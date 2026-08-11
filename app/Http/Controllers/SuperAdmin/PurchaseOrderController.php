<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\CompanyContact;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Design;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Models\Craftman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PurchaseOrderController extends Controller
{
    /**
     * Display all Purchase Orders with Tab Filtering
     */
    public function index(Request $request)
    {
        // Handle search and filtering
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $categoryFilter = $request->get('category_filter');
        $filterCraftsman = $request->get('filter_craftsman');
        $designCodeFilter = $request->get('design_code_filter');

        // Validate sort parameters
        $allowedSortColumns = ['id', 'purchase_order_code', 'due_date', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'id';
        }

        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        // Export functionality
        if ($request->has('export')) {
            return $this->exportPurchaseOrders($request);
        }

        $createdOrdersQuery = PurchaseOrder::where('status', 'created')->whereNull('allocated_craftsman_code');
        $allocatedOrdersQuery = PurchaseOrder::where('craftsman_status', 'allocated')->where('status', 'created');
        $inProcessOrdersQuery = PurchaseOrder::where('status', 'in_process');
        $forApprovalOrdersQuery = PurchaseOrder::where('status', 'for_approval');
        $completedOrdersQuery = PurchaseOrder::where('status', 'completed');
        $completed_filter = $request->get('completed_filter');
        if ($completed_filter == 'day') {
            $completedOrdersQuery->whereDate('updated_at', now()->toDateString());
        } elseif ($completed_filter == 'week') {
            $completedOrdersQuery->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($completed_filter == 'month') {
            $completedOrdersQuery->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year);
        }
        $completed_filter = $request->get('completed_filter');
        if ($completed_filter == 'day') {
            $completedOrdersQuery->whereDate('updated_at', now()->toDateString());
        } elseif ($completed_filter == 'week') {
            $completedOrdersQuery->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($completed_filter == 'month') {
            $completedOrdersQuery->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year);
        }
        $rejectedOrdersQuery = PurchaseOrder::where(function ($q) {
            $q->where('craftsman_status', 'rejected')
                ->orWhereRaw('JSON_LENGTH(rejected_items) > 0');
        });

        // Apply Overdue filter
        if ($request->get('overdue') == 1) {
            $createdOrdersQuery->where('due_date', '<', now());
            $allocatedOrdersQuery->where('due_date', '<', now());
            $inProcessOrdersQuery->where('due_date', '<', now());
            $forApprovalOrdersQuery->where('due_date', '<', now());
        }

        // Apply filters to all queries
        $queries = [
            $createdOrdersQuery,
            $allocatedOrdersQuery,
            $inProcessOrdersQuery,
            $forApprovalOrdersQuery,
            $completedOrdersQuery,
            $rejectedOrdersQuery
        ];

        foreach ($queries as $query) {
            // Apply search if present
            if ($search) {
                $searchTerm = '%' . $search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('purchase_order_code', 'LIKE', $searchTerm)
                        ->orWhere('notes', 'LIKE', $searchTerm)
                        ->orWhereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", [$searchTerm]);
                });
            }

            // Apply Category filter
            if ($categoryFilter) {
                $query->whereRaw("JSON_CONTAINS(items, ?, '$')", [json_encode(['category' => (int)$categoryFilter])]);
            }

            // Apply Craftsman filter
            if ($filterCraftsman) {
                $query->where('allocated_craftsman_code', 'LIKE', '%' . $filterCraftsman . '%');
            }

            // Apply Design Code filter
            if ($designCodeFilter) {
                $query->whereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", ["%{$designCodeFilter}%"]);
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);
        }

        $createdOrders = $createdOrdersQuery->get();
        $allocatedOrders = $allocatedOrdersQuery->get();
        $inProcessOrders = $inProcessOrdersQuery->get();
        $forApprovalOrders = $forApprovalOrdersQuery->get();
        $completedOrders = $completedOrdersQuery->get();
        $rejectedOrders = $rejectedOrdersQuery->get();

        // Get unique categories for filters
        $categories = ProductCategory::orderBy('name')->get();

        $craftsmen = Craftman::all();

        return view('super-admin.purchase-order.index', compact(
            'createdOrders',
            'allocatedOrders',
            'inProcessOrders',
            'forApprovalOrders',
            'completedOrders',
            'rejectedOrders',
            'craftsmen',
            'search',
            'sortBy',
            'sortOrder',
            'categories',
            'categoryFilter',
            'filterCraftsman'
        ));
    }

    /**
     * Show Create Form
     */
    public function create()
    {
        $categories = ProductCategory::orderBy('name')->get();
        $products = Product::with(['subcategory', 'category', 'images'])->get();
        $designs = Design::select('design_code')->get()
            ->concat(
                Product::where('design_status', 'Accepted')
                    ->whereNotNull('design_code')
                    ->select('design_code')
                    ->get()
                    ->map(fn($p) => (object)['design_code' => $p->design_code])
            )
            ->unique('design_code')
            ->values();
        return view('super-admin.purchase-order.create', compact('categories', 'products', 'designs'));
    }

    /**
     * Store a New Purchase Order with Image and Multi-Row Calculation
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'due_date' => 'nullable|date',
            'items' => 'required|array',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.grams' => 'required|array',
            'items.*.quantity' => 'required|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $purchaseOrderCode = PurchaseOrder::generatePurchaseOrderCode();
        $items = $request->items;

        // Process only the items that are not marked for deletion
        $processedItems = [];
        foreach ($items as $index => $item) {
            // Skip if item was marked for deletion
            if (isset($item['_deleted']) && $item['_deleted'] == '1') {
                continue;
            }

            // Handle Manual Image Upload
            if (isset($request->file('items')[$index]['image'])) {
                $image = $request->file('items')[$index]['image'];
                $imageName = time() . "_{$index}_" . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/purchase-orders'), $imageName);
                $item['image'] = 'images/purchase-orders/' . $imageName;
            } else {
                $item['image'] = null;
            }

            // Multi-row Calculation for weight
            $total = 0;
            if (isset($item['grams']) && is_array($item['grams'])) {
                foreach ($item['grams'] as $i => $gram) {
                    $total += floatval($gram) * intval($item['quantity'][$i]);
                }
            }
            $item['total'] = $total;

            $processedItems[] = $item;
        }

        PurchaseOrder::create([
            'purchase_order_code' => $purchaseOrderCode,
            'due_date' => $request->due_date,
            'notes' => $request->notes,
            'items' => $processedItems,
            'status' => 'created',
            'created_by' => \Auth::guard('super_admin')->id(),
            'creator_type' => 'super_admin',
            'created_by' => \Auth::guard('super_admin')->id(),
            'creator_type' => 'super_admin',
        ]);

        return redirect()->route('super-admin.purchase-order.index')->with('success', 'Order created successfully.');
    }

    /**
     * Show Detailed Purchase Order
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $itemsWithDetails = [];
        if ($purchaseOrder->items) {
            foreach ($purchaseOrder->items as $item) {
                $productId = $item['product_id'] ?? null;
                $product = $productId ? Product::with(['subcategory', 'category', 'images'])->find($productId) : null;
                $total = 0;
                $individual_totals = [];
                if (isset($item['grams']) && is_array($item['grams'])) {
                    foreach ($item['grams'] as $i => $gram) {
                        $individual_total = floatval($gram) * intval($item['quantity'][$i]);
                        $individual_totals[] = $individual_total;
                        $total += $individual_total;
                    }
                }

                // Robust Category Name Resolution
                $categoryName = 'N/A';
                if (!empty($item['category_name']) && $item['category_name'] !== 'N/A') {
                    $categoryName = $item['category_name'];
                } elseif (!empty($item['produts_category']) && $item['produts_category'] !== 'N/A') {
                    $categoryName = $item['produts_category'];
                } elseif (!empty($item['category'])) {
                    if (is_numeric($item['category'])) {
                        $cat = ProductCategory::find($item['category']);
                        $categoryName = $cat ? $cat->name : 'N/A';
                    } else {
                        $categoryName = $item['category'];
                    }
                }

                if (($categoryName === 'N/A' || empty($categoryName)) && $product && $product->category) {
                    $categoryName = $product->category->name;
                }

                // Robust Subcategory Name Resolution
                $subcategoryName = 'N/A';
                if (!empty($item['subcategory_name']) && $item['subcategory_name'] !== 'N/A') {
                    $subcategoryName = $item['subcategory_name'];
                } elseif (!empty($item['sub_category_name']) && $item['sub_category_name'] !== 'N/A') {
                    $subcategoryName = $item['sub_category_name'];
                } elseif ($product && $product->subcategory) {
                    $subcategoryName = $product->subcategory->name;
                } elseif (!empty($item['subcategory'])) {
                    if (is_numeric($item['subcategory'])) {
                        $sub = ProductSubcategory::find($item['subcategory']);
                        $subcategoryName = $sub ? $sub->name : 'N/A';
                    } else {
                        $subcategoryName = $item['subcategory'];
                    }
                }

                // Get design images - broadened lookup
                $design = Design::where('product_id', $product?->id)->first();

                $itemsWithDetails[] = array_merge($item, [
                    'product' => $product,
                    'total' => $total,
                    'individual_totals' => $individual_totals,
                    'design' => $design,
                    'category_name' => $categoryName,
                    'subcategory_name' => $subcategoryName
                ]);
            }
        }

        // Load rejected items details
        $rejectedItemsWithDetails = [];
        if (!empty($purchaseOrder->rejected_items) && is_array($purchaseOrder->rejected_items)) {
            foreach ($purchaseOrder->rejected_items as $item) {
                $productId = $item['product_id'] ?? null;
                $product = $productId ? Product::with(['subcategory', 'category', 'images'])->find($productId) : null;
                $total = 0;
                $individual_totals = [];
                if (isset($item['grams']) && is_array($item['grams'])) {
                    foreach ($item['grams'] as $i => $gram) {
                        $individual_total = floatval($gram) * intval($item['quantity'][$i]);
                        $individual_totals[] = $individual_total;
                        $total += $individual_total;
                    }
                }

                // Robust Category Name Resolution
                $categoryName = 'N/A';
                if (!empty($item['category_name']) && $item['category_name'] !== 'N/A') {
                    $categoryName = $item['category_name'];
                } elseif (!empty($item['produts_category']) && $item['produts_category'] !== 'N/A') {
                    $categoryName = $item['produts_category'];
                } elseif (!empty($item['category'])) {
                    if (is_numeric($item['category'])) {
                        $cat = ProductCategory::find($item['category']);
                        $categoryName = $cat ? $cat->name : 'N/A';
                    } else {
                        $categoryName = $item['category'];
                    }
                }

                if (($categoryName === 'N/A' || empty($categoryName)) && $product && $product->category) {
                    $categoryName = $product->category->name;
                }

                // Robust Subcategory Name Resolution
                $subcategoryName = 'N/A';
                if (!empty($item['subcategory_name']) && $item['subcategory_name'] !== 'N/A') {
                    $subcategoryName = $item['subcategory_name'];
                } elseif (!empty($item['sub_category_name']) && $item['sub_category_name'] !== 'N/A') {
                    $subcategoryName = $item['sub_category_name'];
                } elseif ($product && $product->subcategory) {
                    $subcategoryName = $product->subcategory->name;
                } elseif (!empty($item['subcategory'])) {
                    if (is_numeric($item['subcategory'])) {
                        $sub = ProductSubcategory::find($item['subcategory']);
                        $subcategoryName = $sub ? $sub->name : 'N/A';
                    } else {
                        $subcategoryName = $item['subcategory'];
                    }
                }

                // Get design images - broadened lookup
                $design = Design::where('product_id', $product?->id)->first();

                $rejectedItemsWithDetails[] = array_merge($item, [
                    'product' => $product,
                    'total' => $total,
                    'individual_totals' => $individual_totals,
                    'design' => $design,
                    'category_name' => $categoryName,
                    'subcategory_name' => $subcategoryName
                ]);
            }
        }

        return view('super-admin.purchase-order.show', compact('purchaseOrder', 'itemsWithDetails', 'rejectedItemsWithDetails'));
    }

    /**
     * Load Edit Form with Detail Lookup
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        $categories = ProductCategory::orderBy('name')->get();
        $products = Product::with(['subcategory', 'category', 'images'])->get();
        $designs = Design::select('design_code')->get()
            ->concat(
                Product::where('design_status', 'Accepted')
                    ->whereNotNull('design_code')
                    ->select('design_code')
                    ->get()
                    ->map(fn($p) => (object)['design_code' => $p->design_code])
            )
            ->unique('design_code')
            ->values();

        $itemsWithDetails = [];
        if ($purchaseOrder->items) {
            foreach ($purchaseOrder->items as $item) {
                $productId = $item['product_id'] ?? null;
                $product = $productId ? Product::with(['subcategory', 'category', 'images'])->find($productId) : null;

                // Calculate individual totals for display
                $individual_totals = [];
                if (isset($item['grams']) && is_array($item['grams'])) {
                    foreach ($item['grams'] as $i => $gram) {
                        $individual_total = floatval($gram) * intval($item['quantity'][$i]);
                        $individual_totals[] = $individual_total;
                    }
                }

                // Robust Category Name Resolution
                $categoryName = 'N/A';
                if (!empty($item['category_name']) && $item['category_name'] !== 'N/A') {
                    $categoryName = $item['category_name'];
                } elseif (!empty($item['produts_category']) && $item['produts_category'] !== 'N/A') {
                    $categoryName = $item['produts_category'];
                } elseif (!empty($item['category'])) {
                    if (is_numeric($item['category'])) {
                        $cat = ProductCategory::find($item['category']);
                        $categoryName = $cat ? $cat->name : 'N/A';
                    } else {
                        $categoryName = $item['category'];
                    }
                }

                if (($categoryName === 'N/A' || empty($categoryName)) && $product && $product->category) {
                    $categoryName = $product->category->name;
                }

                // Robust Subcategory Name Resolution
                $subcategoryName = 'N/A';
                if (!empty($item['subcategory_name']) && $item['subcategory_name'] !== 'N/A') {
                    $subcategoryName = $item['subcategory_name'];
                } elseif (!empty($item['sub_category_name']) && $item['sub_category_name'] !== 'N/A') {
                    $subcategoryName = $item['sub_category_name'];
                } elseif ($product && $product->subcategory) {
                    $subcategoryName = $product->subcategory->name;
                } elseif (!empty($item['subcategory'])) {
                    if (is_numeric($item['subcategory'])) {
                        $sub = ProductSubcategory::find($item['subcategory']);
                        $subcategoryName = $sub ? $sub->name : 'N/A';
                    } else {
                        $subcategoryName = $item['subcategory'];
                    }
                }

                // Get design images - broadened lookup
                $design = Design::where('product_id', $product?->id)->first();

                // Match category name to ID if needed for dropdown pre-selection
                $categoryId = is_numeric($item['category'] ?? null) ? (int)$item['category'] : ($product->product_category_id ?? null);
                if (!$categoryId && !empty($categoryName) && $categoryName !== 'N/A') {
                    $catMatch = ProductCategory::where('name', $categoryName)->first();
                    $categoryId = $catMatch ? $catMatch->id : null;
                }

                $itemsWithDetails[] = array_merge([
                    'product_id' => null,
                    'category' => null,
                    'image' => null,
                    'item_notes' => '',
                    'grams' => [],
                    'quantity' => [],
                    'individual_totals' => $individual_totals,
                ], $item, [
                    'product' => $product,
                    'design' => $design,
                    'category_name' => $categoryName,
                    'subcategory_name' => $subcategoryName,
                    'sub_category_name' => $subcategoryName,
                    'produts_category' => $categoryName,
                    'category' => $categoryId,
                    'subcategory' => is_numeric($item['subcategory'] ?? null) ? (int)$item['subcategory'] : ($product->product_subcategory_id ?? null)
                ]);
            }
        }

        return view('super-admin.purchase-order.edit', compact('purchaseOrder', 'categories', 'products', 'itemsWithDetails', 'designs'));
    }

    /**
     * Update Purchase Order with Persistence Logic
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $items = $request->items ?? [];

        // Process only the items that are still present in the form (handle client-side deletions)
        $processedItems = [];
        foreach ($items as $index => $item) {
            // Skip if item was marked for deletion (we'll handle this by checking if it has a deletion marker)
            if (isset($item['_deleted']) && $item['_deleted'] == '1') {
                continue;
            }

            // Handle Image Persistence
            if ($request->hasFile("items.$index.image")) {
                $imageFile = $request->file("items")[$index]["image"] ?? null;
                if ($imageFile) {
                    $imageName = time() . "_{$index}_" . uniqid() . '.' . $imageFile->getClientOriginalExtension();
                    $imageFile->move(public_path('images/purchase-orders'), $imageName);
                    $item['image'] = 'images/purchase-orders/' . $imageName;
                }
            } else {
                // Keep the old image path from hidden field if no new file provided
                $item['image'] = $item['old_image'] ?? null;
            }

            // Multi-row Calculation for weight
            $total = 0;
            if (isset($item['grams']) && is_array($item['grams'])) {
                foreach ($item['grams'] as $i => $gram) {
                    $total += floatval($gram) * intval($item['quantity'][$i]);
                }
            }
            $item['total'] = $total;

            $processedItems[] = $item;
        }

        $purchaseOrder->update([
            'due_date' => $request->due_date,
            'notes' => $request->notes,
            'items' => $processedItems
        ]);

        $returnUrl = $request->input('return_url');
        if ($returnUrl) {
            return redirect($returnUrl)->with('success', 'Order updated successfully.');
        }

        $tab = $request->input('tab');
        if (!$tab || $tab === 'created') {
            if ($purchaseOrder->craftsman_status === 'rejected' || $purchaseOrder->status === 'rejected') {
                $tab = 'rejected';
            } elseif ($purchaseOrder->status === 'completed') {
                $tab = 'completed';
            } elseif ($purchaseOrder->status === 'for_approval') {
                $tab = 'for_approval';
            } elseif ($purchaseOrder->status === 'in_process') {
                $tab = 'in_process';
            } elseif ($purchaseOrder->craftsman_status === 'allocated' || $purchaseOrder->allocated_craftsman_code) {
                $tab = 'allocated';
            } else {
                $tab = 'created';
            }
        }

        return redirect()->route('super-admin.purchase-order.index', ['tab' => $tab])->with('success', 'Order updated successfully.');
    }

    /**
     * Print PO Logic
     */
    public function print(PurchaseOrder $purchaseOrder)
    {
        $itemsWithDetails = [];
        if ($purchaseOrder->items) {
            foreach ($purchaseOrder->items as $item) {
                $productId = $item['product_id'] ?? null;
                $product = $productId ? Product::with(['subcategory', 'category', 'images'])->find($productId) : null;
                $total = 0;
                $individual_totals = [];
                if (isset($item['grams']) && is_array($item['grams'])) {
                    foreach ($item['grams'] as $i => $gram) {
                        $individual_total = floatval($gram) * intval($item['quantity'][$i]);
                        $individual_totals[] = $individual_total;
                        $total += $individual_total;
                    }
                }

                // Robust Category Name Resolution
                $categoryName = 'N/A';
                if (!empty($item['category_name']) && $item['category_name'] !== 'N/A') {
                    $categoryName = $item['category_name'];
                } elseif (!empty($item['produts_category']) && $item['produts_category'] !== 'N/A') {
                    $categoryName = $item['produts_category'];
                } elseif (!empty($item['category'])) {
                    if (is_numeric($item['category'])) {
                        $cat = \App\Models\ProductCategory::find($item['category']);
                        $categoryName = $cat ? $cat->name : 'N/A';
                    } else {
                        $categoryName = $item['category'];
                    }
                }

                if (($categoryName === 'N/A' || empty($categoryName)) && $product && $product->category) {
                    $categoryName = $product->category->name;
                }

                // Robust Subcategory Name Resolution
                $subcategoryName = 'N/A';
                if (!empty($item['subcategory_name']) && $item['subcategory_name'] !== 'N/A') {
                    $subcategoryName = $item['subcategory_name'];
                } elseif (!empty($item['sub_category_name']) && $item['sub_category_name'] !== 'N/A') {
                    $subcategoryName = $item['sub_category_name'];
                } elseif ($product && $product->subcategory) {
                    $subcategoryName = $product->subcategory->name;
                } elseif (!empty($item['subcategory'])) {
                    if (is_numeric($item['subcategory'])) {
                        $sub = \App\Models\ProductSubcategory::find($item['subcategory']);
                        $subcategoryName = $sub ? $sub->name : 'N/A';
                    } else {
                        $subcategoryName = $item['subcategory'];
                    }
                }

                // Get design images - broadened lookup
                $design = \App\Models\Design::where('product_id', $product?->id)->first();

                $itemsWithDetails[] = array_merge($item, [
                    'product' => $product,
                    'total' => $total,
                    'individual_totals' => $individual_totals,
                    'design' => $design,
                    'category_name' => $categoryName,
                    'subcategory_name' => $subcategoryName
                ]);
            }
        }
        return view('super-admin.purchase-order.print', compact('purchaseOrder', 'itemsWithDetails'));
    }

    /**
     * Destroy PO and associated images
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->items) {
            foreach ($purchaseOrder->items as $item) {
                if (isset($item['image']) && file_exists(public_path($item['image']))) {
                    unlink(public_path($item['image']));
                }
            }
        }
        $purchaseOrder->delete();
        return redirect()->route('super-admin.purchase-order.index')->with('success', 'Deleted.');
    }

    /**
     * Single Order Allocation
     */
    public function allocate(PurchaseOrder $purchaseOrder)
    {
        $itemsWithDetails = [];
        foreach ($purchaseOrder->items as $item) {
            $productId = $item['product_id'] ?? null;
            $product = $productId ? Product::find($productId) : null;
            $total = 0;
            $individual_totals = [];
            if (isset($item['grams']) && is_array($item['grams'])) {
                foreach ($item['grams'] as $i => $gram) {
                    $individual_total = floatval($gram) * intval($item['quantity'][$i]);
                    $individual_totals[] = $individual_total;
                    $total += $individual_total;
                }
            }
            // Get design for allocate view
            $design = null;
            if ($product && $product->design_code) {
                $design = Design::where('product_id', $product->id)->first();
            }
            // Get category name for display
            $categoryName = $item['category_name'] ?? 'N/A';
            if (isset($item['category'])) {
                $category = \App\Models\ProductCategory::find($item['category']);
                $categoryName = $category ? $category->name : $categoryName;
            }
            if ($categoryName === 'N/A' && $product && $product->category) {
                $categoryName = $product->category->name;
            }

            // Get subcategory name
            $subcategoryName = $item['subcategory_name'] ?? 'N/A';
            if ($product && $product->subcategory) {
                $subcategoryName = $product->subcategory->name;
            } elseif (isset($item['subcategory'])) {
                $sub = ProductSubcategory::find($item['subcategory']);
                $subcategoryName = $sub ? $sub->name : $subcategoryName;
            }

            $itemsWithDetails[] = array_merge([
                'product_id' => null,
                'category' => null,
                'subcategory' => null,
                'image' => null,
                'item_notes' => '',
                'grams' => [],
                'quantity' => [],
            ], $item, [
                'product' => $product,
                'total' => $total,
                'individual_totals' => $individual_totals,
                'design' => $design,
                'category_name' => $categoryName,
                'subcategory_name' => $subcategoryName,
                'category' => $item['category'] ?? ($product->product_category_id ?? null),
                'subcategory' => $item['subcategory'] ?? ($product->product_subcategory_id ?? null)
            ]);
        }
        $craftsmen = Craftman::all();
        return view('super-admin.purchase-order.allocate', compact('purchaseOrder', 'itemsWithDetails', 'craftsmen'));
    }

    public function allocateStore(Request $request, PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update([
            'allocated_craftsman_code' => $request->allocated_craftsman_code,
            'craftsman_status' => 'allocated',
            'allocated_by' => \Auth::guard("super_admin")->id(),
            'allocated_at' => now(),
        ]);

        // Send Notification
        try {
            $craftsman = \App\Models\Craftman::where('craftman_code', $request->allocated_craftsman_code)->first();
            if ($craftsman && $craftsman->fcm_token) {
                $craftsman->notify(new \App\Notifications\PurchaseOrderAllocated($purchaseOrder));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send notification: ' . $e->getMessage());
        }

        return redirect()->route('super-admin.purchase-order.index')->with('success', 'Allocated.');
    }

    /**
     * Bulk Allocation for multiple orders
     */
    public function bulkAllocate(Request $request)
    {
        PurchaseOrder::whereIn('id', $request->order_ids)->update([
            'allocated_craftsman_code' => $request->craftsman_code,
            'craftsman_status' => 'allocated',
            'allocated_by' => \Auth::guard("super_admin")->id(),
            'allocated_at' => now(),
        ]);

        // Send Notification
        try {
            $craftsman = \App\Models\Craftman::where('craftman_code', $request->craftsman_code)->first();
            if ($craftsman && $craftsman->fcm_token) {
                $count = count($request->order_ids);
                $message = "You have been allocated {$count} new Purchase Orders.";
                $firstOrder = PurchaseOrder::find($request->order_ids[0]);
                $craftsman->notify(new \App\Notifications\PurchaseOrderAllocated($firstOrder, $message));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send notification: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Bulk allocation complete.');
    }

    /**
     * Final Approval Logic
     */
    public function approve(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update(['status' => 'completed',
                'craftsman_status' => 'completed', 'approved_by' => \Auth::guard("super_admin")->id()]);

        // Send Notification
        try {
            $adminUsers = \App\Models\ProcessOwner::where('role', 'super_admin')->get();
            foreach ($adminUsers as $admin) {
                if ($admin->fcm_token) {
                    $admin->notify(new \App\Notifications\PurchaseOrderCompleted($purchaseOrder));
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send PO completion notification: ' . $e->getMessage());
        }

        return redirect()->route('super-admin.purchase-order.index', ['tab' => 'completed'])->with('success', 'Approved.');
    }

    /**
     * Reallocate Logic for Rejected Orders
     */
    public function reallocate(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update([
            'status' => 'created',
            'created_by' => \Auth::guard('super_admin')->id(),
            'creator_type' => 'super_admin',
            'created_by' => \Auth::guard('super_admin')->id(),
            'creator_type' => 'super_admin',
            'craftsman_status' => null,
            'allocated_craftsman_code' => null,
            'rejected_items' => null
        ]);

        return redirect()->route('super-admin.purchase-order.index', ['tab' => 'created'])
            ->with('success', 'Order moved back to Created tab.');
    }

    /**
     * AJAX: Get Products by Category
     */
    /**
     * AJAX: Get Products by Category
     */
    public function getProductsByCategory(Request $request)
    {
        // Filter products that have a linked BP or were created by a user
        // AND match the selected category
        $products = Product::with(['subcategory', 'category', 'images', 'designs'])
            ->where('product_category_id', $request->category_id)
            ->where(function($q) {
                $q->where(function($sub) {
                    $sub->whereNotNull('bp_code')
                        ->orWhereNotNull('created_by');
                })
                ->orWhere('design_status', 'Accepted'); // Always include Accepted designs
            })
            ->where(function($q) {
                $q->whereNull('product_image') // Exclude Work Order Imports
                  ->orWhere('design_status', 'Accepted'); // UNLESS they are Accepted designs
            })
            ->get()
            ->map(function ($product) {
                // Prioritize Design Image -> Product Image -> Default
                $imageUrl = '';
                $designCode = '';

                // Check for associated design
                $design = $product->designs->first(); // Assuming one design per product or taking the first one
                if ($design) {
                    $designCode = $design->design_code;
                    if ($design->image) {
                        $path = $design->image;
                        if (!str_starts_with($path, 'storage/') && !str_starts_with($path, 'images/')) {
                            $path = 'storage/' . $path;
                        }
                        $imageUrl = asset($path);
                    }
                }

                // Fallback to product design code if not found in relation
                if (empty($designCode)) {
                    $designCode = $product->design_code;
                }

                if (empty($imageUrl) && $product->images->count() > 0) {
                    $path = $product->images->first()->path;
                    if (!str_starts_with($path, 'storage/') && !str_starts_with($path, 'images/')) {
                        $path = 'storage/' . $path;
                    }
                    $imageUrl = asset($path);
                }

                // Add these custom fields to the JSON response
                $product->design_code_display = $designCode;
                $product->image_url_display = $imageUrl;

                return $product;
            });

        return response()->json($products);
    }

    /**
     * AJAX: Get Product details by Design Code
     */
    public function getProductByDesignCode(Request $request)
    {
        $designCode = $request->design_code;
        
        // 1. Try finding in Designs table first
        $design = Design::with(['product.category', 'product.subcategory'])
            ->where('design_code', $designCode)
            ->first();

        $product = null;
        $imageUrl = '';

        if ($design && $design->product) {
            $product = $design->product;
            
            // Handle image URL from Design
            if ($design->image) {
                $path = $design->image;
                if (!str_starts_with($path, 'storage/') && !str_starts_with($path, 'images/')) {
                    $path = 'storage/' . $path;
                }
                $imageUrl = asset($path);
            }
        } else {
            // 2. Fallback: Search directly in Products table for Accepted designs
            $product = Product::with(['category', 'subcategory', 'images'])
                ->where('design_code', $designCode)
                ->where('design_status', 'Accepted')
                ->first();
        }

        if (!$product) {
            return response()->json(['error' => 'Design not found'], 404);
        }

        // Fallback image logic if no image found yet
        if (empty($imageUrl) && $product->images->count() > 0) {
            $path = $product->images->first()->path;
            if (!str_starts_with($path, 'storage/') && !str_starts_with($path, 'images/')) {
                $path = 'storage/' . $path;
            }
            $imageUrl = asset($path);
        }

        return response()->json([
            'product_id' => $product->id,
            'category_id' => $product->product_category_id,
            'subcategory_id' => $product->product_subcategory_id,
            'image_url' => $imageUrl,
            'product_name' => $product->product_name,
            'design_code' => $designCode
        ]);
    }

    /**
     * AJAX: Get Designs by Product
     */
    public function getDesignsByProduct(Request $request)
    {
        try {
            $designs = Design::where('product_id', $request->product_id)
                ->get(['id', 'design_code', 'design_name', 'image']);

            return response()->json($designs);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch designs'], 500);
        }
    }

    /**
     * Export Purchase Orders to CSV
     */
    private function exportPurchaseOrders(Request $request)
    {
        $tab = $request->get('tab', 'created');
        $search = $request->get('search');
        $categoryFilter = $request->get('category_filter');
        $filterCraftsman = $request->get('filter_craftsman');
        $designCodeFilter = $request->get('design_code_filter');

        $query = null;

        switch ($tab) {
            case 'created':
                $query = PurchaseOrder::where('status', 'created')->whereNull('allocated_craftsman_code');
                break;
            case 'allocated':
                $query = PurchaseOrder::where('craftsman_status', 'allocated')->where('status', 'created');
                break;
            case 'in_process':
                $query = PurchaseOrder::where('status', 'in_process');
                break;
            case 'for_approval':
                $query = PurchaseOrder::where('status', 'for_approval');
                break;
            case 'completed':
                $query = PurchaseOrder::where('status', 'completed');
                break;
            case 'rejected':
                $query = PurchaseOrder::where(function ($q) {
                    $q->where('craftsman_status', 'rejected')
                        ->orWhereRaw('JSON_LENGTH(rejected_items) > 0');
                });
                break;
            default:
                $query = PurchaseOrder::where('status', 'created')->whereNull('allocated_craftsman_code');
        }

        // Apply search if present
        if ($search) {
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('purchase_order_code', 'LIKE', $searchTerm)
                    ->orWhere('notes', 'LIKE', $searchTerm)
                    ->orWhereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", [$searchTerm]);
            });
        }

        // Apply Category filter
        if ($categoryFilter) {
            $query->whereRaw("JSON_CONTAINS(items, ?, '$')", [json_encode(['category' => (int)$categoryFilter])]);
        }

        // Apply Craftsman filter
        if ($filterCraftsman) {
            $query->where('allocated_craftsman_code', 'LIKE', '%' . $filterCraftsman . '%');
        }

        // Apply Design Code filter
        if ($designCodeFilter) {
            $query->whereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", ["%{$designCodeFilter}%"]);
        }

        $purchaseOrders = $query->get();

        // Create CSV content
        $headers = [
            'PO Code',
            'Due Date',
            'Items Count',
            'Total Weight (g)',
            'Notes',
            'Status',
            'Craftsman Status',
            'Allocated Craftsman',
            'Created At'
        ];

        $csvData = [];
        $csvData[] = $headers;

        foreach ($purchaseOrders as $po) {
            $csvData[] = [
                $po->purchase_order_code,
                $po->due_date ? $po->due_date->format('Y-m-d') : 'N/A',
                count($po->items ?? []),
                number_format(collect($po->items)->sum('total'), 2),
                $po->notes ?? 'N/A',
                $po->status,
                $po->craftsman_status ?? 'N/A',
                $po->allocated_craftsman_code ?? 'N/A',
                $po->created_at->format('Y-m-d H:i:s')
            ];
        }

        // Generate CSV
        $filename = 'purchase_orders_' . $tab . '_' . date('Y-m-d_H-i-s') . '.csv';
        $handle = fopen('php://output', 'w');

        // Set headers for download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Output CSV data
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
        exit;
    }

    public function bulkPrint(Request $request)
    {
        $orderIds = $request->input('order_ids', []);

        if (empty($orderIds)) {
            return redirect()->back()->with('error', 'Please select at least one purchase order.');
        }

        $orders = PurchaseOrder::whereIn('id', $orderIds)->get();
        $ordersWithDetails = [];

        foreach ($orders as $po) {
            $items = is_array($po->items) ? $po->items : [];
            $enrichedItems = [];

            foreach ($items as $item) {
                $productId = $item['product_id'] ?? null;
                $product = $productId ? Product::with(['subcategory', 'category', 'images'])->find($productId) : null;

                $total = 0;
                $individual_totals = [];
                if (isset($item['grams']) && is_array($item['grams'])) {
                    foreach ($item['grams'] as $i => $gram) {
                        $qty = isset($item['quantity'][$i]) ? intval($item['quantity'][$i]) : 0;
                        $individual_total = floatval($gram) * $qty;
                        $individual_totals[] = $individual_total;
                        $total += $individual_total;
                    }
                }

                // Robust Category Name Resolution
                $categoryName = 'N/A';
                if (!empty($item['category_name']) && $item['category_name'] !== 'N/A') {
                    $categoryName = $item['category_name'];
                } elseif (!empty($item['produts_category']) && $item['produts_category'] !== 'N/A') {
                    $categoryName = $item['produts_category'];
                } elseif (!empty($item['category'])) {
                    if (is_numeric($item['category'])) {
                        $cat = \App\Models\ProductCategory::find($item['category']);
                        $categoryName = $cat ? $cat->name : 'N/A';
                    } else {
                        $categoryName = $item['category'];
                    }
                }

                if (($categoryName === 'N/A' || empty($categoryName)) && $product && $product->category) {
                    $categoryName = $product->category->name;
                }

                // Robust Subcategory Name Resolution
                $subcategoryName = 'N/A';
                if (!empty($item['subcategory_name']) && $item['subcategory_name'] !== 'N/A') {
                    $subcategoryName = $item['subcategory_name'];
                } elseif (!empty($item['sub_category_name']) && $item['sub_category_name'] !== 'N/A') {
                    $subcategoryName = $item['sub_category_name'];
                } elseif ($product && $product->subcategory) {
                    $subcategoryName = $product->subcategory->name;
                } elseif (!empty($item['subcategory'])) {
                    if (is_numeric($item['subcategory'])) {
                        $sub = \App\Models\ProductSubcategory::find($item['subcategory']);
                        $subcategoryName = $sub ? $sub->name : 'N/A';
                    } else {
                        $subcategoryName = $item['subcategory'];
                    }
                }

                // Get design images - broadened lookup
                $design = \App\Models\Design::where('product_id', $product?->id)->first();

                $enrichedItems[] = array_merge($item, [
                    'product' => $product,
                    'total' => $total,
                    'individual_totals' => $individual_totals,
                    'design' => $design,
                    'category_name' => $categoryName,
                    'subcategory_name' => $subcategoryName
                ]);
            }

            // Load craftsman details for this order
            $craftsman = null;
            if (!empty($po->allocated_craftsman_code)) {
                $craftsman = \App\Models\Craftman::where('craftman_code', $po->allocated_craftsman_code)->first();
            }

            $ordersWithDetails[] = [
                'order'     => $po,
                'items'     => $enrichedItems,
                'craftsman' => $craftsman,
            ];
        }

        // CompanyContact stores contact info in the 'data' column (cast to array)
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

        return view('super-admin.purchase-order.bulk-print', compact('ordersWithDetails', 'company'));
    }
    public function bulkApprove(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:purchase_orders,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Get the selected purchase orders
        $orderIds = $request->input('order_ids');

        // Update all selected purchase orders
        PurchaseOrder::whereIn('id', $orderIds)
            ->where('status', 'for_approval') // Only approve orders waiting for approval
            ->update([
                'status' => 'completed',
                'approved_by' => \Auth::guard("super_admin")->id(),
            ]);

        // Send Bulk Notification
        try {
            $adminUsers = \App\Models\ProcessOwner::where('role', 'super_admin')->get();
            foreach ($adminUsers as $admin) {
                if ($admin->fcm_token) {
                    $count = count($orderIds);
                    // Use the first PO as reference
                    $admin->notify(new \App\Notifications\PurchaseOrderCompleted(PurchaseOrder::find($orderIds[0])));
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send bulk PO completion notification: ' . $e->getMessage());
        }

        return redirect()->route('super-admin.purchase-order.index', ['tab' => 'completed'])
            ->with('success', count($orderIds) . ' Purchase Orders approved successfully!');
    }

    public function bulkComplete(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:purchase_orders,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $orderIds = $request->input('order_ids');

        // Update selected purchase orders to completed
        PurchaseOrder::whereIn('id', $orderIds)
            ->whereIn('status', ['created', 'in_process', 'for_approval'])
            ->update([
                'status' => 'completed',
                'approved_by' => \Auth::guard("super_admin")->id(),
                'craftsman_status' => 'completed'
            ]);

        // Send Bulk Notification
        try {
            $adminUsers = \App\Models\ProcessOwner::where('role', 'super_admin')->get();
            foreach ($adminUsers as $admin) {
                if ($admin->fcm_token) {
                    $admin->notify(new \App\Notifications\PurchaseOrderCompleted(PurchaseOrder::find($orderIds[0])));
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send bulk PO completion notification: ' . $e->getMessage());
        }

        return redirect()->route('super-admin.purchase-order.index', ['tab' => 'completed'])
            ->with('success', count($orderIds) . ' Purchase Orders marked as completed successfully!');
    }

    /**
     * Copy a completed purchase order to create a new one.
     */
    public function copy(PurchaseOrder $purchaseOrder)
    {
        // Create a new purchase order based on the existing one
        $newPurchaseOrder = $purchaseOrder->replicate();

        // Reset status and other fields that shouldn't be copied
        $newPurchaseOrder->status = 'created'; // Reset to created status
        $newPurchaseOrder->allocated_craftsman_code = null;
        $newPurchaseOrder->craftsman_status = null;
        $newPurchaseOrder->rejected_items = null;
        $newPurchaseOrder->due_date = today()->addDays(7); // Set a new due date (7 days from now)

        // Generate a new purchase order code
        $newPurchaseOrder->purchase_order_code = PurchaseOrder::generatePurchaseOrderCode();

        // Save the new purchase order
        $newPurchaseOrder->save();

        return redirect()->route('super-admin.purchase-order.edit', $newPurchaseOrder)
            ->with('success', 'Purchase Order copied successfully! Please review and update as needed.');
    }

    /**
     * Mark specific items of a purchase order as completed.
     */
    public function completeItems(Request $request, PurchaseOrder $purchaseOrder)
    {
        $selectedItemIndexes = $request->input('selected_items', []);
        if (empty($selectedItemIndexes)) {
            return redirect()->back()->with('error', 'No items selected.');
        }

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

        if (empty($remainingItems)) {
            // All items completed, just update the existing PO
            $purchaseOrder->update([
                'craftsman_status' => 'completed',
                'status' => 'for_approval',
            ]);
            return redirect()->route('super-admin.purchase-order.index', ['tab' => 'completed'])
                ->with('success', 'All items completed and order sent for approval.');
        } else {
            // Partial completion: Split the PO
            // 1. Create a new PO for completed items
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

            // 2. Update existing PO with remaining items
            $purchaseOrder->update([
                'items' => $remainingItems,
            ]);

            return redirect()->route('super-admin.purchase-order.show', $purchaseOrder)
                ->with('success', count($completedItems) . ' items completed and sent for approval. Remaining items are still in process.');
        }
    }
}
