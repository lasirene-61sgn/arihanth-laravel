<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Models\Design;
use App\Models\Craftman;
use App\Models\CompanyContact;
use App\Models\ProcessOwner;
use App\Notifications\PurchaseOrderAllocated;
use App\Notifications\PurchaseOrderCompleted;
use Dompdf\Dompdf;
use Dompdf\Options;



class PurchaseOrderController extends Controller
{
    // =========================================================================
    // HELPERS — role detection
    // =========================================================================

    private function isAdmin($user): bool
    {
        return in_array($user->role ?? '', ['super_admin', 'admin']);
    }

    private function isCraftsman($user): bool
    {
        return ($user->role ?? '') === 'craftsman' || $user instanceof \App\Models\Craftman;
    }

    // =========================================================================
    // INDEX — full SuperAdmin tab-based listing with role scoping
    // =========================================================================

    public function index(Request $request)
    {
        $user    = $request->user();
        $admin   = $this->isAdmin($user);
        $tab     = $request->get('tab', 'created');
        $perPage = $request->get('per_page', 10);
        $search  = $request->get('search');

        // ── Sort ──
        $sortBy = $request->get('sort_by', 'id');

        // 2. Check for 'sort' parameter (?sort=asc), then 'sort_order', then default to 'asc'
        $sortOrder = strtolower($request->get('sort') ?: $request->get('sort_order', 'asc'));

        // 3. Validate Order
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        // 4. Validate Columns
        $allowedSortColumns = ['id', 'purchase_order_code', 'due_date', 'status', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'id';
        }

        // ── Scope helper: applies role-based filter ──
        $scopeFilter = function ($query) use ($user, $admin) {
            if ($admin) return $query;
            if ($this->isCraftsman($user)) {
                return $query->where('allocated_craftsman_code', $user->craftman_code);
            }
            return $query->where('id', 0); // No access for others (like Buyers)
        };

        // ── Filter Application Helper ──
        $applyFilters = function ($query) use ($request, $search) {
            // Search
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('purchase_order_code', 'LIKE', "%$search%")
                        ->orWhere('notes', 'LIKE', "%$search%")
                        ->orWhere('allocated_craftsman_code', 'LIKE', "%$search%")
                        ->orWhereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", ["%$search%"]);
                });
            }

            // Global Filter Parameter (craftsman, PO code, category, subcategory)
            if ($request->filled('filter')) {
                $f = $request->filter;
                $query->where(function ($q) use ($f) {
                    $q->where('allocated_craftsman_code', 'LIKE', "%$f%")
                        ->orWhere('purchase_order_code', 'LIKE', "%$f%")
                        ->orWhereRaw("JSON_CONTAINS(JSON_EXTRACT(items, '$[*].category'), ?)", [json_encode($f)])
                        ->orWhereRaw("JSON_CONTAINS(JSON_EXTRACT(items, '$[*].subcategory'), ?)", [json_encode($f)]);

                    if (is_numeric($f)) {
                        $cat = ProductCategory::find($f);
                        if ($cat) {
                            $q->orWhereRaw("JSON_CONTAINS(JSON_EXTRACT(items, '$[*].category'), ?)", [json_encode($cat->name)]);
                        }
                        $sub = ProductSubcategory::find($f);
                        if ($sub) {
                            $q->orWhereRaw("JSON_CONTAINS(JSON_EXTRACT(items, '$[*].subcategory'), ?)", [json_encode($sub->name)]);
                        }
                    }
                });
            }

            // Exact Matches JSON Filters — Using JSON_EXTRACT for reliable array element matching
            if ($request->filled('category_id') || $request->filled('category_filter')) {
                $catId = $request->category_id ?? $request->category_filter;
                $catName = is_numeric($catId) ? (ProductCategory::find($catId)->name ?? $catId) : $catId;
                $query->whereRaw("JSON_CONTAINS(JSON_EXTRACT(items, '$[*].category'), ?)", [json_encode($catName)]);
            }
            if ($request->filled('category_name')) {
                $query->whereRaw("JSON_CONTAINS(JSON_EXTRACT(items, '$[*].category'), ?)", [json_encode($request->category_name)]);
            }

            if ($request->filled('subcategory_id')) {
                $subId = $request->subcategory_id;
                $subName = is_numeric($subId) ? (ProductSubcategory::find($subId)->name ?? $subId) : $subId;
                $query->whereRaw("JSON_CONTAINS(JSON_EXTRACT(items, '$[*].subcategory'), ?)", [json_encode($subName)]);
            }
            if ($request->filled('subcategory_name')) {
                $query->whereRaw("JSON_CONTAINS(JSON_EXTRACT(items, '$[*].subcategory'), ?)", [json_encode($request->subcategory_name)]);
            }

            if ($request->filled('type')) {
                $query->whereRaw("JSON_CONTAINS(JSON_EXTRACT(items, '$[*].type'), ?)", [json_encode($request->type)]);
            }
            if ($request->filled('size')) {
                $query->whereRaw("JSON_CONTAINS(JSON_EXTRACT(items, '$[*].size'), ?)", [json_encode($request->size)]);
            }
            if ($request->filled('hallmark')) {
                $query->whereRaw("JSON_CONTAINS(JSON_EXTRACT(items, '$[*].hallmark'), ?)", [json_encode($request->hallmark)]);
            }
            if ($request->filled('rodium')) {
                $query->whereRaw("JSON_CONTAINS(JSON_EXTRACT(items, '$[*].rodium'), ?)", [json_encode($request->rodium)]);
            }
            if ($request->filled('hook')) {
                $query->whereRaw("JSON_CONTAINS(JSON_EXTRACT(items, '$[*].hook'), ?)", [json_encode($request->hook)]);
            }
            if ($request->filled('stone')) {
                $query->whereRaw("JSON_CONTAINS(JSON_EXTRACT(items, '$[*].stone'), ?)", [json_encode($request->stone)]);
            }
            if ($request->filled('enamel')) {
                $query->whereRaw("JSON_CONTAINS(JSON_EXTRACT(items, '$[*].enamel'), ?)", [json_encode($request->enamel)]);
            }

            // Regular Filters
            if ($request->filled('craftsman_code_filter') || $request->filled('craftsman_code')) {
                $query->where('allocated_craftsman_code', $request->get('craftsman_code_filter') ?: $request->get('craftsman_code'));
            }
            if ($request->filled('purchase_order_code') || $request->filled('po_number')) {
                $query->where('purchase_order_code', $request->get('purchase_order_code') ?: $request->get('po_number'));
            }
            if ($request->filled('craftsman_status')) {
                $query->where('craftsman_status', $request->craftsman_status);
            }

            // Date Range Filters (due_date)
            if ($request->filled('from_date')) {
                $query->whereDate('due_date', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $query->whereDate('due_date', '<=', $request->to_date);
            }

            // Selected IDs
            if ($request->filled('ids') || $request->filled('purchase_order_ids')) {
                $ids = $request->ids ?? $request->purchase_order_ids;
                if (is_string($ids)) {
                    $ids = explode(',', $ids);
                }
                if (is_array($ids)) {
                    $query->whereIn('id', $ids);
                }
            }
            return $query;
        };

        // ── Tab counts ──
        $counts = [
            'all'          => $applyFilters($scopeFilter(PurchaseOrder::query()))->count(),
            'created'      => $applyFilters($scopeFilter(PurchaseOrder::where('status', 'created')->whereNull('allocated_craftsman_code')))->count(),
            'allocated'    => $applyFilters($scopeFilter(PurchaseOrder::where('craftsman_status', 'allocated')->where('status', 'created')))->count(),
            'in_process'   => $applyFilters($scopeFilter(PurchaseOrder::where('status', 'in_process')))->count(),
            'for_approval' => $applyFilters($scopeFilter(PurchaseOrder::where('status', 'for_approval')))->count(),
            'completed'    => $applyFilters($scopeFilter(PurchaseOrder::where('status', 'completed')))->count(),
            'rejected'     => $applyFilters($scopeFilter(PurchaseOrder::where(function ($q) {
                $q->where('craftsman_status', 'rejected')
                    ->orWhereRaw('JSON_LENGTH(rejected_items) > 0');
            })))->count(),
        ];

        $query = $scopeFilter(PurchaseOrder::query());
        $applyFilters($query);

        // ── Consolidate Tab Aliases ──
        if (str_ends_with($tab, '-orders')) {
            $tab = str_replace('-orders', '', $tab);
        }

        // ── Tab logic (mirrors web PurchaseOrderController@index) ──
        switch ($tab) {
            case 'created':
            case 'new':
                $query->where('status', 'created')->whereNull('allocated_craftsman_code');
                break;
            case 'allocated':
                $query->where('craftsman_status', 'allocated')->where('status', 'created');
                break;
            case 'in_process':
                $query->where('status', 'in_process');
                break;
            case 'for_approval':
                $query->where('status', 'for_approval');
                break;
            case 'completed':
                $query->where('status', 'completed');
                break;
            case 'rejected':
                $query->where(function ($q) {
                    $q->where('craftsman_status', 'rejected')
                        ->orWhereRaw('JSON_LENGTH(rejected_items) > 0');
                });
                break;
            case 'all':
                break;
            default:
                if (!$request->has('tab')) {
                    if ($request->filled('status')) $query->where('status', $request->status);
                } else {
                    $query->where('status', 'created')->whereNull('allocated_craftsman_code');
                }
                break;
        }

        $query->orderBy($sortBy, $sortOrder);

        // ── Export (CSV download) ──
        if ($request->has('export')) {
            $purchaseOrders = $query->get();

            $exportData = $purchaseOrders->map(function ($po) {
                // Count items, calculate total weight, etc if needed for export
                $itemCount = is_array($po->items) ? count($po->items) : 0;
                $totalGrams = 0;
                if (is_array($po->items)) {
                    foreach ($po->items as $item) {
                        $totalGrams += floatval($item['total'] ?? 0);
                    }
                }

                return [
                    'PO Code'           => $po->purchase_order_code,
                    'Date'              => $po->created_at ? $po->created_at->format('Y-m-d') : '',
                    'Due Date'          => $po->due_date,
                    'Items Count'       => $itemCount,
                    'Total Grams'       => $totalGrams,
                    'Status'            => ucfirst(str_replace('_', ' ', $po->status)),
                    'Craftsman Code'    => $po->allocated_craftsman_code,
                    'Craftsman Status'  => ucfirst(str_replace('_', ' ', $po->craftsman_status ?? '---')),
                    'Notes'             => $po->notes,
                ];
            });

            $filename = 'purchase_orders_' . now()->format('Y-m-d_H-i-s') . '.csv';
            $headers  = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            return response()->stream(function () use ($exportData) {
                $file = fopen('php://output', 'w');
                if ($exportData->isNotEmpty()) {
                    fputcsv($file, array_keys($exportData->first()));
                    foreach ($exportData as $row) {
                        fputcsv($file, $row);
                    }
                }
            }, 200, $headers);
        }

        // ── Print (full data JSON, no pagination) ──
        if ($request->has('print')) {
            $purchaseOrders = $query->get();

            // Resolve items for full print view
            $purchaseOrders->transform(function ($po) use ($tab) {
                $po->items = $this->resolvePurchaseOrderItems($po->items ?? [], null, $po->notes);
                if (!empty($po->rejected_items)) {
                    $po->rejected_items = $this->resolvePurchaseOrderItems($po->rejected_items, null, $po->notes);
                }

                // If in rejected tab, show rejected items as the primary items
                if (strtolower($tab) === 'rejected') {
                    $po->setAttribute('items', $this->resolvePurchaseOrderItems($po->rejected_items ?? [], 'rejected', $po->notes));
                    $po->setAttribute('rejected_items', []);
                }

                // Add explicit date field for the frontend
                $po->date = $po->created_at ? $po->created_at->format('Y-m-d') : ($po->due_date ?: '');

                // Add category name if missing at the top level
                if (empty($po->category_name) && count($po->items) > 0) {
                    $po->category_name = $po->items[0]['category_name'] ?? 'N/A';
                }

                // Add dynamic status colors for Admin & Craftsman roles
                $colorData = $this->calculatePurchaseOrderColors($po);
                $po->setAttribute('color_key', $colorData['color_key']);
                $po->setAttribute('color_hex', $colorData['color_hex']);

                return $po;
            });

            return response()->json([
                'success' => true,
                'counts'  => $counts,
                'data'    => $purchaseOrders
            ]);
        }

        // ── Standard Paginated Response ──
        $purchaseOrders = $query->paginate($perPage)->withQueryString();

        // Process items to include full image URLs and additional fields for all purchase orders
        $purchaseOrders->getCollection()->transform(function ($po) use ($tab) {
            $po->items = $this->resolvePurchaseOrderItems($po->items ?? [], null, $po->notes);
            if (!empty($po->rejected_items)) {
                $po->rejected_items = $this->resolvePurchaseOrderItems($po->rejected_items, null, $po->notes);
            }

            // If in rejected tab, show rejected items as the primary items
            if (strtolower($tab) === 'rejected') {
                $po->setAttribute('items', $this->resolvePurchaseOrderItems($po->rejected_items ?? [], 'rejected', $po->notes));
                $po->setAttribute('rejected_items', []);
            }

            // Add explicit date field for the frontend
            $po->date = $po->created_at ? $po->created_at->format('Y-m-d') : ($po->due_date ?: '');

            // Add category name if missing at the top level
            if (empty($po->category_name) && count($po->items) > 0) {
                $po->category_name = $po->items[0]['category_name'] ?? 'N/A';
            }

            // Add dynamic status colors for Admin & Craftsman roles
            $colorData = $this->calculatePurchaseOrderColors($po);
            $po->setAttribute('color_key', $colorData['color_key']);
            $po->setAttribute('color_hex', $colorData['color_hex']);

            return $po;
        });

        return response()->json([
            'success' => true,
            'counts'  => $counts,
            'data'    => $purchaseOrders
        ]);
    }

    // =========================================================================
    // SHOW — with resolved item details
    // =========================================================================

    public function show(Request $request, $id)
    {
        $user          = $request->user();
        $purchaseOrder = PurchaseOrder::find($id);

        if (!$purchaseOrder) {
            return response()->json(['success' => false, 'message' => 'Purchase Order not found'], 404);
        }

        // Authorization for non-admins
        if (!$this->isAdmin($user)) {
            if ($this->isCraftsman($user) && $purchaseOrder->allocated_craftsman_code !== $user->craftman_code) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        }

        // ── Tab Detection (Align with index) ──
        $tab = strtolower($request->get('tab') ?: $request->get('status') ?: '');
        
        // Resolve details for BOTH accepted/pending items and rejected items
        $acceptedItems = $this->resolvePurchaseOrderItems($purchaseOrder->items ?? [], null, $purchaseOrder->notes);
        $rejectedItems = $this->resolvePurchaseOrderItems($purchaseOrder->rejected_items ?? [], 'rejected', $purchaseOrder->notes);

        // Merge both into a single unified list as requested (to show all statuses in one list)
        $finalItems = array_merge($acceptedItems, $rejectedItems);

        // Calculate total weight for the combined list
        $totalWeight = collect($finalItems)->sum('total');

        // Prepare the final data array
        $data = $purchaseOrder->toArray();
        
        // Remove the raw items and rejected_items from the response to prevent pollution
        unset($data['items'], $data['rejected_items']);

        // Add dynamic status colors for Admin & Craftsman roles
        $colorData = $this->calculatePurchaseOrderColors($purchaseOrder);

        return response()->json([
            'success' => true,
            'data'    => array_merge($data, [
                'items'          => $finalItems,
                'total_weight'   => $totalWeight,
                'color_key'      => $colorData['color_key'],
                'color_hex'      => $colorData['color_hex'],
            ])
        ]);
    }

    // =========================================================================
    // STORE — full create logic from SuperAdmin
    // =========================================================================

    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'due_date'            => 'required|date',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'nullable|exists:products,id',
            'items.*.design_code' => 'nullable|string',
            'items.*.category'    => 'nullable',
            'items.*.grams'       => 'required|array',
            'items.*.quantity'    => 'required|array',
            'items.*.size' => 'nullable|array',
            'items.*.item_notes' => 'nullable|string',
            'notes'               => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $purchaseOrderCode = PurchaseOrder::generatePurchaseOrderCode();
        $items             = $request->items ?? [];
        $uploadedFiles     = $request->file('items') ?? [];

        $processedItems = [];

        // Handle case when items is empty or not an array
        if (!is_array($items) || empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'At least one item is required'
            ], 422);
        }

        foreach ($items as $index => $item) {
            // Skip items marked for deletion
            if (isset($item['_deleted']) && $item['_deleted'] == '1') continue;

            // ── Resolve design code & image from product OR design_code ──
            if (!empty($item['product_id'])) {
                $product = Product::with(['designs', 'category', 'subcategory'])->find($item['product_id']);
                if ($product) {
                    // Auto-populate category if missing or provided as name
                    if (empty($item['category']) || !is_numeric($item['category'])) {
                        $providedCategory = $item['category'] ?? '';
                        if (!empty($providedCategory)) {
                            $cat = ProductCategory::where('name', $providedCategory)->first();
                            $item['category'] = $cat ? $cat->id : $product->product_category_id;
                        } else {
                            $item['category'] = $product->product_category_id;
                        }
                    }
                    if (empty($item['subcategory']) || !is_numeric($item['subcategory'])) {
                        $providedSubcategory = $item['subcategory'] ?? '';
                        if (!empty($providedSubcategory)) {
                            $sub = ProductSubcategory::where('name', $providedSubcategory)->first();
                            $item['subcategory'] = $sub ? $sub->id : $product->product_subcategory_id;
                        } else {
                            $item['subcategory'] = $product->product_subcategory_id;
                        }
                    }

                    // Get design from product's designs relationship
                    $design = null;

                    // If design_code already exists, try to find matching design
                    if (!empty($item['design_code'])) {
                        $design = $product->designs->where('design_code', $item['design_code'])->first();
                    }

                    // If no specific design found, use first design
                    if (!$design) {
                        $design = $product->designs->first();
                        if ($design && empty($item['design_code'])) {
                            $item['design_code'] = $design->design_code;
                        }
                    }

                    // Fetch image from design if not manually provided
                    if ($design && !empty($design->image)) {
                        // Use design image as fallback if no manual upload or request image
                        if (empty($item['image']) && empty($item['old_image'])) {
                            $item['image'] = $design->image;
                        }
                        // Also store in old_image for update reference
                        if (empty($item['old_image'])) {
                            $item['old_image'] = $design->image;
                        }
                    }
                }
            } elseif (!empty($item['design_code'])) {
                // No product_id but has design_code - fetch from Design table directly
                $design = Design::where('design_code', $item['design_code'])->first();

                if ($design && !empty($design->image)) {
                    // Use design image as fallback if no manual upload or request image
                    if (empty($item['image']) && empty($item['old_image'])) {
                        $item['image'] = $design->image;
                    }
                    // Also store in old_image for update reference
                    if (empty($item['old_image'])) {
                        $item['old_image'] = $design->image;
                    }
                }
            }

            // ── Handle manual image file upload ──
            if (isset($uploadedFiles[$index]['image'])) {
                $file      = $uploadedFiles[$index]['image'];
                $imageName = time() . "_{$index}_" . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/purchase-orders'), $imageName);
                $item['image'] = 'images/purchase-orders/' . $imageName;
                $item['old_image'] = $item['image']; // Set for persistence
                $item['old_image'] = $item['image']; // Set old_image for manual uploads
            } elseif (empty($item['image'])) {
                $item['image'] = null;
            }

            // ── Multi-row weight calculation ──
            $total = 0;
            if (isset($item['grams']) && is_array($item['grams'])) {
                foreach ($item['grams'] as $i => $gram) {
                    $total += floatval($gram) * intval($item['quantity'][$i] ?? 0);
                }
            }
            $item['total'] = $total;

            // ── Map API 'size' key to 'item_size' for web panel compatibility ──
            if (isset($item['size']) && !isset($item['item_size'])) {
                $item['item_size'] = is_array($item['size']) ? implode(', ', $item['size']) : $item['size'];
            }

            // ── Ensure item_notes exists for web panel compatibility ──
            if (!isset($item['item_notes'])) {
                $item['item_notes'] = '';
            }

            $processedItems[] = $item;
        }

        $purchaseOrder = PurchaseOrder::create([
            'purchase_order_code' => $purchaseOrderCode,
            'due_date'            => $request->due_date,
            'notes'               => $request->notes,
            'items'               => $processedItems,
            'status'              => 'created',
        ]);

        // Resolve items with full details including image URLs
        $po = $purchaseOrder->fresh();
        $itemsWithDetails = $this->resolvePurchaseOrderItems($po->items ?? [], null, $po->notes);
        $rejectedWithDetails = $this->resolvePurchaseOrderItems($po->rejected_items ?? [], null, $po->notes);
        $totalWeight = collect($itemsWithDetails)->sum('total');

        // Add dynamic status colors for Admin & Craftsman roles
        $colorData = $this->calculatePurchaseOrderColors($po);

        return response()->json([
            'success' => true,
            'message' => 'Purchase Order created successfully',
            'data'    => [
                'id'                       => $po->id,
                'purchase_order_code'      => $po->purchase_order_code,
                'due_date'                 => $po->due_date,
                'notes'                    => $po->notes,
                'items'                    => $itemsWithDetails,
                'total_weight'             => $totalWeight,
                'status'                   => $po->status,
                'allocated_craftsman_code' => $po->allocated_craftsman_code,
                'craftsman_status'         => $po->craftsman_status,
                'rejected_items'           => $rejectedWithDetails,
                'color_key'                => $colorData['color_key'],
                'color_hex'                => $colorData['color_hex'],
                'created_at'               => $po->created_at,
                'updated_at'               => $po->updated_at,
            ]
        ], 201);
    }

    // =========================================================================
    // UPDATE — full update logic from SuperAdmin
    // =========================================================================

    public function update(Request $request, $id)
    {
        $user          = $request->user();
        $purchaseOrder = PurchaseOrder::find($id);

        if (!$purchaseOrder) {
            return response()->json(['success' => false, 'message' => 'Purchase Order not found'], 404);
        }

        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'due_date'            => 'sometimes|required|date',
            'items'               => 'sometimes|required|array',
            'items.*.product_id'  => 'sometimes|required|exists:products,id',
            'items.*.design_code' => 'nullable|string',
            'items.*.category'    => 'nullable',
            'items.*.grams'       => 'sometimes|required|array',
            'items.*.quantity'    => 'sometimes|required|array',
            'items.*.size' => 'nullable|array',
            'items.*.item_notes' => 'nullable|string',
            'notes'               => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $items         = $request->items ?? [];
        $uploadedFiles = $request->file('items') ?? [];
        $existingItems = $purchaseOrder->items ?? [];

        $processedItems = [];
        foreach ($items as $index => $item) {
            if (isset($item['_deleted']) && $item['_deleted'] == '1') continue;

            // ── Resolve design code from product ──
            if (!empty($item['product_id'])) {
                $product = Product::with(['designs', 'category', 'subcategory'])->find($item['product_id']);
                if ($product) {
                    // Auto-populate category if missing or provided as name
                    if (empty($item['category']) || !is_numeric($item['category'])) {
                        $providedCategory = $item['category'] ?? '';
                        if (!empty($providedCategory)) {
                            $cat = ProductCategory::where('name', $providedCategory)->first();
                            $item['category'] = $cat ? $cat->id : $product->product_category_id;
                        } else {
                            $item['category'] = $product->product_category_id;
                        }
                    }
                    if (empty($item['subcategory']) || !is_numeric($item['subcategory'])) {
                        $providedSubcategory = $item['subcategory'] ?? '';
                        if (!empty($providedSubcategory)) {
                            $sub = ProductSubcategory::where('name', $providedSubcategory)->first();
                            $item['subcategory'] = $sub ? $sub->id : $product->product_subcategory_id;
                        } else {
                            $item['subcategory'] = $product->product_subcategory_id;
                        }
                    }

                    $design = $product->designs->first();
                    if ($design) {
                        $item['design_code'] = $design->design_code;
                        if (empty($item['old_image']) && $design->image) {
                            $item['old_image'] = $design->image;
                        }
                    }
                    if (empty($item['design_code'])) {
                        $item['design_code'] = $product->design_code;
                    }
                }
            }

            // ── Handle image: new upload > request image > DB fallback > null ──
            if (isset($uploadedFiles[$index]['image'])) {
                $file      = $uploadedFiles[$index]['image'];
                $imageName = time() . "_{$index}_" . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/purchase-orders'), $imageName);
                $item['image'] = 'images/purchase-orders/' . $imageName;
                $item['old_image'] = $item['image']; // Set for persistence
            } else {
                $requestImage = $item['image'] ?? $item['image_url'] ?? $item['old_image'] ?? null;
                if ($requestImage) {
                    $path = str_replace(asset(''), '', $requestImage);
                    $item['image'] = ltrim($path, '/');
                } elseif (isset($existingItems[$index]['image'])) {
                    $item['image'] = $existingItems[$index]['image'];
                } else {
                    $item['image'] = null;
                }
            }
            unset($item['image_url']);

            // ── Multi-row weight calculation ──
            $total = 0;
            if (isset($item['grams']) && is_array($item['grams'])) {
                foreach ($item['grams'] as $i => $gram) {
                    $total += floatval($gram) * intval($item['quantity'][$i] ?? 0);
                }
            }
            $item['total'] = $total;

            // ── Map API 'size' key to 'item_size' for web panel compatibility ──
            if (isset($item['size']) && !isset($item['item_size'])) {
                $item['item_size'] = is_array($item['size']) ? implode(', ', $item['size']) : $item['size'];
            }

            // ── Ensure item_notes exists for web panel compatibility ──
            if (!isset($item['item_notes'])) {
                $item['item_notes'] = '';
            }

            $processedItems[] = $item;
        }

        $updateData = [
            'due_date' => $request->due_date ?? $purchaseOrder->due_date,
            'notes'    => $request->has('notes') ? $request->notes : $purchaseOrder->notes,
        ];
        if (!empty($processedItems)) {
            $updateData['items'] = $processedItems;
        }

        $purchaseOrder->update($updateData);

        $po                  = $purchaseOrder->fresh();
        $itemsWithDetails    = $this->resolvePurchaseOrderItems($po->items ?? [], null, $po->notes);
        $rejectedWithDetails = $this->resolvePurchaseOrderItems($po->rejected_items ?? [], null, $po->notes);
        $totalWeight         = collect($itemsWithDetails)->sum('total');

        // Add dynamic status colors for Admin & Craftsman roles
        $colorData = $this->calculatePurchaseOrderColors($po);

        return response()->json([
            'success' => true,
            'message' => 'Purchase Order updated successfully',
            'data'    => [
                'id'                       => $po->id,
                'purchase_order_code'      => $po->purchase_order_code,
                'due_date'                 => $po->due_date,
                'notes'                    => $po->notes,
                'items'                    => $itemsWithDetails,
                'total_weight'             => $totalWeight,
                'status'                   => $po->status,
                'allocated_craftsman_code' => $po->allocated_craftsman_code,
                'craftsman_status'         => $po->craftsman_status,
                'rejected_items'           => $rejectedWithDetails,
                'color_key'                => $colorData['color_key'],
                'color_hex'                => $colorData['color_hex'],
                'created_at'               => $po->created_at,
                'updated_at'               => $po->updated_at,
            ]
        ]);
    }

    // =========================================================================
    // DELETE
    // =========================================================================

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $purchaseOrder = PurchaseOrder::find($id);
        if (!$purchaseOrder) {
            return response()->json(['success' => false, 'message' => 'Purchase Order not found'], 404);
        }

        $purchaseOrder->delete();

        return response()->json(['success' => true, 'message' => 'Purchase Order deleted successfully']);
    }

    // =========================================================================
    // ALLOCATE (Single) — Admin only
    // =========================================================================

    public function allocate(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $purchaseOrder = PurchaseOrder::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'allocated_craftsman_code' => 'required|exists:craftmen,craftman_code',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $purchaseOrder->update([
            'allocated_craftsman_code' => $request->allocated_craftsman_code,
            'craftsman_status'         => 'allocated',
        ]);

        // Notify Craftsman
        try {
            $craftsman = Craftman::where('craftman_code', $request->allocated_craftsman_code)->first();
            if ($craftsman && $craftsman->fcm_token) {
                $craftsman->notify(new PurchaseOrderAllocated($purchaseOrder));
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify craftsman on PO allocation: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Purchase Order allocated successfully!', 'data' => $purchaseOrder]);
    }

    // =========================================================================
    // BULK ALLOCATE — Admin only
    // =========================================================================

    public function bulkAllocate(Request $request)
    {
        $user = $request->user();
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        Log::info('Bulk Allocate Purchase Orders Triggered', $request->all());

        $validator = Validator::make($request->all(), [
            'order_ids'                => 'required|array',
            'order_ids.*'              => 'exists:purchase_orders,id',
            'allocated_craftsman_code' => 'required|exists:craftmen,craftman_code',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $orderIds      = $request->input('order_ids');
        $craftsmanCode = $request->input('allocated_craftsman_code');

        try {
            PurchaseOrder::whereIn('id', $orderIds)->update([
                'allocated_craftsman_code' => $craftsmanCode,
                'craftsman_status'         => 'allocated',
            ]);

            // Notify Craftsman (Summary Message)
            try {
                $craftsman = Craftman::where('craftman_code', $craftsmanCode)->first();
                if ($craftsman && $craftsman->fcm_token) {
                    $count      = count($orderIds);
                    $firstOrder = PurchaseOrder::find($orderIds[0]);
                    $message    = "You have been allocated {$count} new Purchase Orders.";
                    $craftsman->notify(new PurchaseOrderAllocated($firstOrder, $message));
                }
            } catch (\Exception $e) {
                Log::error('Failed to notify craftsman on bulk PO allocation: ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'message' => count($orderIds) . ' Purchase Orders allocated successfully!']);
        } catch (\Exception $e) {
            Log::error('Bulk Allocation Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to allocate purchase orders. ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // RE-ALLOCATE — Admin only (resets to created)
    // =========================================================================

    public function reallocate(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // $validator = Validator::make($request->all(), [
        //     'craftsman_bp_code' => 'required|exists:craftmen,craftman_code',
        // ]);
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        $purchaseOrder->update([
            'status'                   => 'created',
            'craftsman_status'         => null,
            'allocated_craftsman_code' => null,
            'rejected_items'           => null,
        ]);

        return response()->json(['success' => true, 'message' => 'Purchase Order reset/reallocated successfully (Moved to Created)', 'data' => $purchaseOrder]);
    }

    // =========================================================================
    // APPROVE (Single) — Admin only
    // =========================================================================

    public function approve(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $purchaseOrder = PurchaseOrder::findOrFail($id);

        $purchaseOrder->update([
            'status'           => 'completed',
            'craftsman_status' => 'completed',
        ]);

        // Notify craftsman that their work was approved
        try {
            $craftsman = Craftman::where('craftman_code', $purchaseOrder->allocated_craftsman_code)->first();
            if ($craftsman && $craftsman->fcm_token) {
                $craftsman->notify(new PurchaseOrderCompleted($purchaseOrder));
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify craftsman on PO approval: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Purchase Order approved successfully.', 'data' => $purchaseOrder]);
    }

    // =========================================================================
    // BULK APPROVE — Admin only
    // =========================================================================

    public function bulkApprove(Request $request)
    {
        $user = $request->user();
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'order_ids'   => 'required|array',
            'order_ids.*' => 'exists:purchase_orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $orderIds = $request->input('order_ids');

        PurchaseOrder::whereIn('id', $orderIds)
            ->where('status', 'for_approval')
            ->update(['status' => 'completed']);

        // Notify craftsmen that their orders were approved (grouped by craftsman)
        try {
            $approvedOrders = PurchaseOrder::whereIn('id', $orderIds)->get();
            $grouped = $approvedOrders->groupBy('allocated_craftsman_code');

            foreach ($grouped as $craftsmanCode => $orders) {
                if (!$craftsmanCode) continue;
                $craftsman = Craftman::where('craftman_code', $craftsmanCode)->first();
                if ($craftsman && $craftsman->fcm_token) {
                    $count = $orders->count();
                    $message = $count > 1
                        ? "{$count} of your Purchase Orders have been approved by Admin."
                        : "Purchase Order #{$orders->first()->purchase_order_code} has been approved by Admin.";
                    $craftsman->notify(new PurchaseOrderCompleted($orders->first(), $message));
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify craftsmen on bulk PO approval: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => count($orderIds) . ' Purchase Orders approved successfully!']);
    }

    // =========================================================================
    // UPDATE STATUS — Admin only
    // =========================================================================

    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $purchaseOrder   = PurchaseOrder::findOrFail($id);
        $allowedStatuses = ['created', 'in_process', 'for_approval', 'completed'];

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:' . implode(',', $allowedStatuses),
            'notes'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $updateData = ['status' => $request->status];
        if ($request->has('notes')) {
            $updateData['notes'] = $request->notes;
        }

        $purchaseOrder->update($updateData);

        return response()->json(['success' => true, 'message' => 'Purchase Order status updated to ' . $request->status, 'data' => $purchaseOrder->fresh()]);
    }

    // =========================================================================
    // CRAFTSMAN ACTIONS (Process Items, Complete)
    // =========================================================================

    public function processItems(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->isCraftsman($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only Craftsmen can process items.'], 403);
        }

        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if ($purchaseOrder->allocated_craftsman_code !== $user->craftman_code) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Order is not allocated to you.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:accept_all,reject_all,process',
            'accepted_items' => 'required_if:action,process|array',
            'rejected_items' => 'required_if:action,process|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $action = $request->input('action');

        switch ($action) {
            case 'accept_all':
                $purchaseOrder->update([
                    'craftsman_status' => 'in_process',
                    'status' => 'in_process'
                ]);
                return response()->json(['success' => true, 'message' => 'Order is now in process.', 'data' => $purchaseOrder->fresh()]);

            case 'reject_all':
                $purchaseOrder->update([
                    'craftsman_status' => 'rejected',
                    'rejected_items' => $purchaseOrder->items,
                    'items' => [] // Clear items for craftsman
                ]);
                return response()->json(['success' => true, 'message' => 'All items rejected.', 'data' => $purchaseOrder->fresh()]);

            case 'process':
                $acceptedItems = $request->input('accepted_items', []); // Array of indexes
                $rejectedItems = $request->input('rejected_items', []); // Array of indexes

                $allItems = $purchaseOrder->items ?? [];
                $acceptedItemList = [];
                $rejectedItemList = [];

                // Separate accepted and rejected items
                foreach ($allItems as $index => $item) {
                    if (in_array((string)$index, $acceptedItems) || in_array((int)$index, $acceptedItems)) {
                        $acceptedItemList[] = $item;
                    }
                    if (in_array((string)$index, $rejectedItems) || in_array((int)$index, $rejectedItems)) {
                        $rejectedItemList[] = $item;
                    }
                }

                // Validate that each item is either accepted OR rejected (not both)
                $conflictItems = array_intersect($acceptedItems, $rejectedItems);
                if (!empty($conflictItems)) {
                    return response()->json(['success' => false, 'message' => 'Some items are marked as both accepted and rejected. Please review.'], 422);
                }

                // Validate that at least one item is selected
                if (empty($acceptedItems) && empty($rejectedItems)) {
                    return response()->json(['success' => false, 'message' => 'Please select at least one item to accept or reject.'], 422);
                }

                if (count($rejectedItemList) == count($allItems)) {
                    // All items rejected
                    $purchaseOrder->update([
                        'craftsman_status' => 'rejected',
                        'rejected_items' => $rejectedItemList,
                        'items' => [] // Clear items since all are rejected
                    ]);
                    return response()->json(['success' => true, 'message' => 'All items rejected.', 'data' => $purchaseOrder->fresh()]);
                } elseif (count($acceptedItemList) == count($allItems)) {
                    // All items accepted
                    $purchaseOrder->update([
                        'craftsman_status' => 'in_process',
                        'status' => 'in_process',
                        'rejected_items' => [],
                        'items' => $acceptedItemList
                    ]);
                    return response()->json(['success' => true, 'message' => 'All items accepted.', 'data' => $purchaseOrder->fresh()]);
                } else {
                    // Mixed
                    $purchaseOrder->update([
                        'craftsman_status' => 'in_process',
                        'status' => 'in_process', // Main status gets updated to in process
                        'rejected_items' => $rejectedItemList,
                        'items' => $acceptedItemList // Only keep accepted items for craftsman in this PO
                    ]);
                    return response()->json([
                        'success' => true,
                        'message' => count($acceptedItemList) . ' item(s) accepted, ' . count($rejectedItemList) . ' item(s) rejected.',
                        'data' => $purchaseOrder->fresh()
                    ]);
                }
        }

        return response()->json(['success' => false, 'message' => 'Invalid action.'], 400);
    }

    // Single item accept
    public function acceptItem(Request $request, $id, $index)
    {
        $user = $request->user();
        if (!$this->isCraftsman($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only Craftsmen can accept items.'], 403);
        }

        $purchaseOrder = PurchaseOrder::findOrFail($id);
        if ($purchaseOrder->allocated_craftsman_code !== $user->craftman_code) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Order is not allocated to you.'], 403);
        }

        $items = $purchaseOrder->items ?? [];
        $rejectedItems = $purchaseOrder->rejected_items ?? [];

        // Check if it's already in items or in rejected_items
        // If it's in rejected_items, we move it back to items
        if (isset($rejectedItems[$index])) {
            $item = $rejectedItems[$index];
            $items[] = $item;
            unset($rejectedItems[$index]);
            $rejectedItems = array_values($rejectedItems); // Re-index
        } elseif (isset($items[$index])) {
            // Already in items, maybe just confirming?
            $item = $items[$index];
        } else {
            return response()->json(['success' => false, 'message' => 'Item not found at index ' . $index], 404);
        }

        $purchaseOrder->update([
            'items'            => $items,
            'rejected_items'   => $rejectedItems,
            'craftsman_status' => 'in_process',
            'status'           => 'in_process'
        ]);

        return response()->json(['success' => true, 'message' => 'Item accepted.', 'data' => $purchaseOrder->fresh()]);
    }

    // Single item reject
    public function rejectItem(Request $request, $id, $index)
    {
        $user = $request->user();
        if (!$this->isCraftsman($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only Craftsmen can reject items.'], 403);
        }

        $purchaseOrder = PurchaseOrder::findOrFail($id);
        if ($purchaseOrder->allocated_craftsman_code !== $user->craftman_code) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Order is not allocated to you.'], 403);
        }

        $items = $purchaseOrder->items ?? [];
        $rejectedItems = $purchaseOrder->rejected_items ?? [];

        if (!isset($items[$index])) {
            return response()->json(['success' => false, 'message' => 'Item not found in active items at index ' . $index], 404);
        }

        $item = $items[$index];
        if ($request->has('reason')) {
            $item['rejection_reason'] = $request->reason;
        }

        $rejectedItems[] = $item;
        unset($items[$index]);
        $items = array_values($items); // Re-index

        $statusUpdates = [
            'items'          => $items,
            'rejected_items' => $rejectedItems,
        ];

        // If no items left, set PO status to rejected
        if (empty($items)) {
            $statusUpdates['craftsman_status'] = 'rejected';
        } else {
            $statusUpdates['craftsman_status'] = 'in_process';
            $statusUpdates['status'] = 'in_process';
        }

        $purchaseOrder->update($statusUpdates);

        return response()->json(['success' => true, 'message' => 'Item rejected.', 'data' => $purchaseOrder->fresh()]);
    }

    public function bulkAccept(Request $request)
    {
        $user = $request->user();
        if (!$this->isCraftsman($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'purchase_order_ids' => 'required|array',
            'purchase_order_ids.*' => 'exists:purchase_orders,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $count = 0;
        foreach ($request->purchase_order_ids as $id) {
            $purchaseOrder = PurchaseOrder::find($id);

            if ($purchaseOrder && $purchaseOrder->allocated_craftsman_code === $user->craftman_code && $purchaseOrder->craftsman_status === 'allocated') {
                $purchaseOrder->update([
                    'craftsman_status' => 'in_process',
                    'status' => 'in_process'
                ]);
                $count++;
            }
        }

        return response()->json(['success' => true, 'message' => "$count purchase orders accepted!"]);
    }

    public function bulkReject(Request $request)
    {
        $user = $request->user();
        if (!$this->isCraftsman($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'purchase_order_ids' => 'required|array',
            'purchase_order_ids.*' => 'exists:purchase_orders,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $count = 0;
        foreach ($request->purchase_order_ids as $id) {
            $purchaseOrder = PurchaseOrder::find($id);

            if ($purchaseOrder && $purchaseOrder->allocated_craftsman_code === $user->craftman_code && $purchaseOrder->craftsman_status === 'allocated') {
                $purchaseOrder->update([
                    'craftsman_status' => 'rejected',
                    'rejected_items' => $purchaseOrder->items,
                    'items' => []
                ]);
                $count++;
            }
        }

        return response()->json(['success' => true, 'message' => "$count purchase orders rejected!"]);
    }

    public function bulkComplete(Request $request)
    {
        $user = $request->user();
        if (!$this->isCraftsman($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'purchase_order_ids' => 'required|array',
            'purchase_order_ids.*' => 'exists:purchase_orders,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $count = 0;
        foreach ($request->purchase_order_ids as $id) {
            $purchaseOrder = PurchaseOrder::find($id);

            if ($purchaseOrder && $purchaseOrder->allocated_craftsman_code === $user->craftman_code && $purchaseOrder->craftsman_status === 'in_process') {
                $purchaseOrder->update([
                    'craftsman_status' => 'completed',
                    'status' => 'for_approval'
                ]);

                $count++;
                $lastPo = $purchaseOrder;
            }
        }

        // Notify Admins once after bulk completion
        if ($count > 0 && isset($lastPo)) {
            try {
                $admins = ProcessOwner::whereNotNull('fcm_token')->get();
                $message = "{$count} Purchase Orders have been completed by craftsman {$user->craftman_code}.";
                foreach ($admins as $admin) {
                    $admin->notify(new PurchaseOrderCompleted($lastPo, $message));
                }
            } catch (\Exception $e) {
                Log::error('Failed to notify admins on bulk PO completion: ' . $e->getMessage());
            }
        }

        return response()->json(['success' => true, 'message' => "$count purchase orders marked as completed!"]);
    }

    public function complete(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->isCraftsman($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if ($purchaseOrder->allocated_craftsman_code !== $user->craftman_code) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Cannot complete this order.'], 403);
        }

        if ($purchaseOrder->craftsman_status !== 'in_process') {
            return response()->json(['success' => false, 'message' => 'Purchase order is not in process status.'], 400);
        }

        $purchaseOrder->update([
            'craftsman_status' => 'completed',
            'status' => 'for_approval'
        ]);

        // Notify Admins
        try {
            $admins = ProcessOwner::whereNotNull('fcm_token')->get();
            foreach ($admins as $admin) {
                $admin->notify(new PurchaseOrderCompleted($purchaseOrder));
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify admins on PO completion: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Purchase order marked as completed and sent for approval.', 'data' => $purchaseOrder->fresh()]);
    }

    public function completeItems(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->isCraftsman($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if ($purchaseOrder->allocated_craftsman_code !== $user->craftman_code) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Cannot complete items for this order.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'selected_items' => 'required|array',
            'selected_items.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $selectedItemIndexes = $request->input('selected_items', []);
        $allItems = $purchaseOrder->items;
        $completedItems = [];
        $remainingItems = [];

        foreach ($allItems as $index => $item) {
            if (in_array($index, $selectedItemIndexes)) {
                $completedItems[] = $item;
            } else {
                $remainingItems[] = $item;
            }
        }

        if (empty($completedItems)) {
             return response()->json(['success' => false, 'message' => 'No valid items selected for completion.'], 422);
        }

        if (empty($remainingItems)) {
            // All items completed
            $purchaseOrder->update([
                'craftsman_status' => 'completed',
                'status' => 'for_approval',
            ]);
            
            // Notify Admins
            try {
                $admins = ProcessOwner::whereNotNull('fcm_token')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new PurchaseOrderCompleted($purchaseOrder));
                }
            } catch (\Exception $e) {
                Log::error('Failed to notify admins on PO completion: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true, 
                'message' => 'All items completed and order sent for approval.',
                'data' => $purchaseOrder->fresh()
            ]);
        } else {
            // Partial completion: Split the PO
            $newPO = $purchaseOrder->replicate();
            $newPO->purchase_order_code = $purchaseOrder->purchase_order_code . '-C'; 
            
            // Ensure unique PO code
            $baseCode = $newPO->purchase_order_code;
            $counter = 1;
            while (PurchaseOrder::where('purchase_order_code', $newPO->purchase_order_code)->exists()) {
                $newPO->purchase_order_code = $baseCode . $counter++;
            }
            
            $newPO->items = $completedItems;
            $newPO->status = 'for_approval';
            $newPO->craftsman_status = 'completed';
            $newPO->save();

            // Update existing PO with remaining items
            $purchaseOrder->update([
                'items' => $remainingItems,
            ]);

            // Notify Admins about the new PO
            try {
                $admins = ProcessOwner::whereNotNull('fcm_token')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new PurchaseOrderCompleted($newPO));
                }
            } catch (\Exception $e) {
                Log::error('Failed to notify admins on PO split completion: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => count($completedItems) . ' items completed and sent for approval. Remaining items are still in process.',
                'data' => [
                    'completed_order' => $newPO,
                    'remaining_order' => $purchaseOrder->fresh()
                ]
            ]);
        }
    }

    // =========================================================================
    // AJAX HELPERS — Products by Category, Designs by Product, etc.
    // =========================================================================

    /**
     * Get products by category with design code + image URL
     */
    public function getPoProductsByCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer|exists:product_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'category_id is required', 'errors' => $validator->errors()], 422);
        }

        $products = Product::with(['subcategory', 'category', 'images', 'designs'])
            ->where('product_category_id', $request->category_id)
            ->where(function ($q) {
                $q->whereNotNull('bp_code')->orWhereNotNull('created_by');
            })
            ->get()
            ->map(function ($product) {
                $designCode = '';
                $imageUrl   = '';

                $design = $product->designs->first();
                if ($design) {
                    $designCode = $design->design_code;
                    if ($design->image) {
                        $path = $design->image;
                        if (!str_starts_with($path, 'storage/')) $path = 'storage/' . $path;
                        $imageUrl = asset($path);
                    }
                }

                if (empty($designCode)) $designCode = $product->design_code ?? '';

                if (empty($imageUrl) && $product->images->count() > 0) {
                    $path = $product->images->first()->path;
                    if (!str_starts_with($path, 'storage/')) $path = 'storage/' . $path;
                    $imageUrl = asset($path);
                }

                return [
                    'id'             => $product->id,
                    'product_name'   => $product->product_name,
                    'product_code'   => $product->product_code,
                    'design_code'    => $designCode,
                    'image_url'      => $imageUrl,
                    'category_id'    => $product->product_category_id,
                    'subcategory_id' => $product->product_subcategory_id,
                    'subcategory'    => $product->subcategory,
                    'category'       => $product->category,
                ];
            });

        return response()->json(['success' => true, 'data' => $products]);
    }

    /**
     * Get designs for a product
     */
    public function getPoDesignsByProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'product_id is required', 'errors' => $validator->errors()], 422);
        }

        $designs = Design::where('product_id', $request->product_id)
            ->get(['id', 'design_code', 'design_name', 'image'])
            ->map(function ($d) {
                $imageUrl = '';
                if ($d->image) {
                    $path = $d->image;
                    if (!str_starts_with($path, 'storage/')) $path = 'storage/' . $path;
                    $imageUrl = asset($path);
                }
                return [
                    'id'          => $d->id,
                    'design_code' => $d->design_code,
                    'design_name' => $d->design_name,
                    'image'       => $d->image,
                    'image_url'   => $imageUrl,
                ];
            });

        return response()->json(['success' => true, 'data' => $designs]);
    }

    /**
     * Get product details by design code
     */
    public function getPoProductByDesignCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'design_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'design_code is required', 'errors' => $validator->errors()], 422);
        }

        $design = Design::with(['product.category', 'product.subcategory', 'product.images'])
            ->where('design_code', $request->design_code)
            ->first();

        if (!$design || !$design->product) {
            return response()->json(['success' => false, 'message' => 'Design not found'], 404);
        }

        $product  = $design->product;
        $imageUrl = '';

        if ($design->image) {
            $path = $design->image;
            if (!str_starts_with($path, 'storage/')) $path = 'storage/' . $path;
            $imageUrl = asset($path);
        }

        if (empty($imageUrl) && $product->images->count() > 0) {
            $path = $product->images->first()->path;
            if (!str_starts_with($path, 'storage/')) $path = 'storage/' . $path;
            $imageUrl = asset($path);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'product_id'     => $product->id,
                'product_name'   => $product->product_name,
                'product_code'   => $product->product_code,
                'design_code'    => $design->design_code,
                'design_name'    => $design->design_name,
                'image_url'      => $imageUrl,
                'image'          => $design->image,
                'category_id'    => $product->product_category_id,
                'subcategory_id' => $product->product_subcategory_id,
                'category'       => $product->category,
                'subcategory'    => $product->subcategory,
            ]
        ]);
    }

    /**
     * Get accepted designs by subcategory
     */
    public function getPoSubcategoryDesign(Request $request)
    {
        $subcategoryId = $request->get('subcategory_id');
        $designs       = Product::where('product_subcategory_id', $subcategoryId)
            ->whereNotNull('design_code')
            ->where('design_status', 'Accepted')
            ->select('id', 'design_code', 'product_name', 'product_code')
            ->get();

        return response()->json(['success' => true, 'data' => $designs]);
    }

    // =========================================================================
    // PRIVATE — Resolve PO items with full details (exact SuperAdmin logic)
    // =========================================================================

    private function resolvePurchaseOrderItems(array $items, $status = null, $poNotes = null): array
    {
        $resolved = [];

        foreach ($items as $item) {
            $product = Product::with(['subcategory', 'category', 'images', 'designs'])
                ->find($item['product_id'] ?? null);

            // ── Totals ──
            $total            = 0;
            $individualTotals = [];
            if (isset($item['grams']) && is_array($item['grams'])) {
                foreach ($item['grams'] as $i => $gram) {
                    $t                  = floatval($gram) * intval($item['quantity'][$i] ?? 0);
                    $individualTotals[] = $t;
                    $total             += $t;
                }
            }

            // ── Design ──
            $designCode = $item['design_code'] ?? '';
            $designName = '';
            $design     = null;

            if ($product) {
                $design = $product->designs->first();
                if ($design) {
                    $designCode = $designCode ?: ($design->design_code ?? '');
                    $designName = $design->design_name ?? '';
                }
                if (empty($designCode)) {
                    $designCode = $product->design_code ?? '';
                }
            }

            // ── Image URL (full URL) ──
            $imageUrl = '';
            $rawImage = $item['image'] ?? null;

            if ($rawImage) {
                if (str_starts_with($rawImage, 'http://') || str_starts_with($rawImage, 'https://')) {
                    $imageUrl = $rawImage;
                } else {
                    // Ensure proper path format
                    $path = $rawImage;
                    // Remove leading slashes or dots
                    $path = ltrim($path, '/.');

                    // Add storage/ prefix if not already present
                    if (!str_starts_with($path, 'storage/') && !str_starts_with($path, 'images/')) {
                        $path = 'storage/' . $path;
                    }

                    // Generate full URL using APP_URL
                    $imageUrl = asset($path);
                }
            }

            // Fallback 1: Use design image if no item image
            if (empty($imageUrl) && $design && !empty($design->image)) {
                $path = ltrim($design->image, '/.');
                if (!str_starts_with($path, 'storage/')) {
                    $path = 'storage/' . $path;
                }
                $imageUrl = asset($path);
            }

            // Fallback 2: Use product image if still no image
            if (empty($imageUrl) && $product && $product->images->count() > 0) {
                $path = ltrim($product->images->first()->path, '/.');
                if (!str_starts_with($path, 'storage/')) {
                    $path = 'storage/' . $path;
                }
                $imageUrl = asset($path);
            }

            // ── Category / Subcategory names ──
            $categoryName    = 'N/A';
            $subcategoryName = 'N/A';

            if ($product && $product->category) {
                $categoryName = $product->category->name;
            } elseif (!empty($item['category'])) {
                if (is_numeric($item['category'])) {
                    $cat = ProductCategory::find($item['category']);
                    $categoryName = $cat ? $cat->name : 'N/A';
                } else {
                    $categoryName = $item['category'];
                }
            } elseif (!empty($item['category_id'])) {
                $cat = ProductCategory::find($item['category_id']);
                $categoryName = $cat ? $cat->name : 'N/A';
            }

            if ($product && $product->subcategory) {
                $subcategoryName = $product->subcategory->name;
            } elseif ($product && $product->product_subcategory_id) {
                $sub = ProductSubcategory::find($product->product_subcategory_id);
                $subcategoryName = $sub ? $sub->name : 'N/A';
            } elseif (!empty($item['subcategory'])) {
                if (is_numeric($item['subcategory'])) {
                    $sub = ProductSubcategory::find($item['subcategory']);
                    $subcategoryName = $sub ? $sub->name : 'N/A';
                } else {
                    $subcategoryName = $item['subcategory'];
                }
            } elseif (!empty($item['subcategory_id'])) {
                $sub = ProductSubcategory::find($item['subcategory_id']);
                $subcategoryName = $sub ? $sub->name : 'N/A';
            } elseif (!empty($item['sub_category_id'])) {
                $sub = ProductSubcategory::find($item['sub_category_id']);
                $subcategoryName = $sub ? $sub->name : 'N/A';
            } elseif (!empty($item['subcategory_name'])) {
                $subcategoryName = $item['subcategory_name'];
            } elseif (!empty($item['sub_category_name'])) {
                $subcategoryName = $item['sub_category_name'];
            }

            // ── Resolve item_size from either 'item_size' or 'size' key ──
            $itemSize = $item['item_size'] ?? null;
            if (empty($itemSize) && isset($item['size'])) {
                $itemSize = is_array($item['size']) ? implode(', ', $item['size']) : $item['size'];
            }

            $resolved[] = array_merge($item, [
                'status'            => $status ?? ($item['status'] ?? null),
                'image'             => $imageUrl,
                'image_url'         => $imageUrl,
                'category_name'     => $categoryName,
                'subcategory_name'  => $subcategoryName,
                'sub_category_name' => $subcategoryName,
                'produts_category'  => $categoryName,
                'design_code'       => $designCode,
                'design_name'       => $designName,
                'product_name'      => $item['product_name'] ?? ($product ? $product->product_name : 'N/A'),
                'relabel_code'      => $item['relabel_code'] ?? ($product ? $product->relabel_code : 'N/A'),
                'total'             => $total,
                'individual_totals' => $individualTotals,
                'product'           => $product,
                'design'            => $design,
                'type'              => $item['type'] ?? ($product ? $product->type : ($design ? $design->type : 'Piece')),
                'order_type'        => $item['order_type'] ?? ($product ? $product->order_type : 'Ready'),
                'image_path'        => !empty($rawImage) ? public_path(ltrim(str_replace('storage/', '', $rawImage), '/.')) : ($design && !empty($design->image) ? public_path('storage/' . ltrim($design->image, '/.')) : null),
                'po_notes'          => $poNotes,
                'item_notes'        => $item['item_notes'] ?? '',
                'item_size'         => $itemSize,
            ]);
        }


        return $resolved;
    }
    // =========================================================================
    // GENERATE PDF — Admin & Authorized Craftsman (Single or Bulk)
    // =========================================================================
    public function generatePdf(Request $request, $id = null)
    {
        $user = $request->user();

        // ── Handle multiple IDs (query param or array) ──
        $ids = $request->get('ids') ?? $request->get('purchase_order_ids');
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        // Fallback to route ID if no bulk IDs provided
        if (empty($ids) && $id) {
            $ids = [$id];
        }

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No Purchase Order IDs provided'], 400);
        }

        $purchaseOrders = PurchaseOrder::whereIn('id', $ids)->get();
        if ($purchaseOrders->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No orders found'], 404);
        }

        // Fetch company info
        $companyDetails = CompanyContact::where('is_active', 1)->get();
        $getContactValue = function($type) use ($companyDetails) {
            $record = $companyDetails->where('type', $type)->first();
            if (!$record) return '';
            $d = $record->data;
            if (is_array($d)) {
                return $d['value'] ?? (reset($d) ?: '');
            }
            return (string)$d;
        };

        $company = [
            'address' => $getContactValue('location'),
            'mobile'  => $getContactValue('mobile'),
            'gst'     => $getContactValue('gst'),
            'cin'     => $getContactValue('cin'),
            'email'   => $getContactValue('email'),
        ];

        // ── Authorization ──
        if (!$this->isAdmin($user)) {
            $unauthorized = $purchaseOrders->contains(function ($po) use ($user) {
                return $po->allocated_craftsman_code !== $user->craftman_code;
            });
            if ($unauthorized) {
                return response()->json(['success' => false, 'message' => 'Unauthorized for one or more orders'], 403);
            }
        }

        // ── Prepare data for view ──
        $ordersWithDetails = [];
        foreach ($purchaseOrders as $po) {
            $itemsWithDetails = $this->resolvePurchaseOrderItems($po->items ?? [], null, $po->notes);

            // Ensure image_path is correct (check both public and storage)
            foreach ($itemsWithDetails as &$item) {
                if (!empty($item['image_path'])) {
                    if (!file_exists($item['image_path'])) {
                        // Try storage path if public path fails
                        $storagePath = storage_path('app/public/' . ltrim(str_replace(public_path('storage'), '', $item['image_path']), '/\\'));
                        if (file_exists($storagePath)) {
                            $item['image_path'] = $storagePath;
                        }
                    }
                }
            }

            // Fetch craftsman for this PO
            $craftsman = null;
            if (!empty($po->allocated_craftsman_code)) {
                $craftsman = Craftman::where('craftman_code', $po->allocated_craftsman_code)->first();
            }

            $ordersWithDetails[] = [
                'purchaseOrder' => $po,
                'items'         => $itemsWithDetails,
                'craftsman'     => $craftsman,
            ];
        }

        $data = [
            'ordersWithDetails' => $ordersWithDetails,
            'company'           => $company,
        ];

        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'sans-serif');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(view('api.common.purchase-order.generate-pdf', $data)->render());
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = count($ids) === 1
                ? "PurchaseOrder_" . $purchaseOrders->first()->purchase_order_code . ".pdf"
                : "Bulk_PurchaseOrders_" . now()->format('Ymd_His') . ".pdf";

            return response($dompdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Access-Control-Expose-Headers', 'Content-Disposition');
        } catch (\Exception $e) {
            Log::error('PDF Generation Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate PDF. ' . $e->getMessage()], 500);
        }
    }

    /**
     * Calculate color keys and hex colors for purchase orders based on status and due dates.
     */
    private function calculatePurchaseOrderColors($po): array
    {
        $user = Auth::user();
        $isEligibleForColor = $user && ($this->isAdmin($user) || $this->isCraftsman($user));
        $colorKey = null;
        $colorHex = null;

        if ($isEligibleForColor) {
            if ($po->isOverdue()) {
                // 1. Overdue -> Light Red
                $colorKey = 'light-red';
                $colorHex = '#FFCDD2';
            } elseif ($po->craftsman_status === 'in_process') {
                // 2. Accepted/In-Process -> Light Orange
                $colorKey = 'light-orange';
                $colorHex = '#FFE0B2';
            } elseif ($po->craftsman_status === 'allocated') {
                // 3. Allocated (first 12 hours light-blue, after 12 hours light-yellow)
                $allocationTime = $po->updated_at;
                if ($allocationTime) {
                    $hoursElapsed = now()->diffInHours($allocationTime);
                    if ($hoursElapsed <= 12) {
                        $colorKey = 'light-blue';
                        $colorHex = '#E3F2FD';
                    } else {
                        $colorKey = 'light-yellow';
                        $colorHex = '#FFF9C4';
                    }
                } else {
                    $colorKey = 'light-blue';
                    $colorHex = '#E3F2FD';
                }
            }
        }

        return [
            'color_key' => $colorKey,
            'color_hex' => $colorHex,
        ];
    }
}
