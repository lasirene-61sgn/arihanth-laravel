<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\WorkOrder;
use App\Models\WorkOrderImage;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Services\ImageWatermarkService;
use App\Models\Craftman;
use App\Models\ProcessOwner;
use App\Models\Buyer;
use App\Models\KeyUser;
use App\Models\User;
use App\Notifications\WorkOrderAllocated;
use App\Notifications\WorkOrderCompleted;
use Dompdf\Dompdf;
use Dompdf\Options;

class WorkOrderController extends Controller
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

    private function isBuyerSide($user): bool
    {
        return $user instanceof \App\Models\Buyer
            || $user instanceof \App\Models\KeyUser
            || $user instanceof \App\Models\User
            || ($user->role ?? '') === 'buyer';
    }

    /**
     * Transform work order data to ensure category/subcategory are simple strings
     */
    private function transformWorkOrderResponse($workOrder): array
    {
        $workOrderData = $workOrder->toArray();
        $user = Auth::user();

        // ── Calculate Color Coding for Frontend ──
        $isEligibleForColor = $user && ($this->isAdmin($user) || $this->isCraftsman($user));
        $colorKey = null;
        $colorHex = null;

        if ($isEligibleForColor) {
            if ($workOrder->isOverdue()) {
                // 1. Overdue -> Light Red
                $colorKey = 'light-red';
                $colorHex = '#FFCDD2';
            } elseif ($workOrder->craftsman_status === 'in_process') {
                // 2. Accepted/In-Process -> Light Orange
                $colorKey = 'light-orange';
                $colorHex = '#FFE0B2';
            } elseif ($workOrder->status === 'allocated' && $workOrder->craftsman_status === 'allocated') {
                // 3. Allocated (first 12 hours light-blue, after 12 hours light-yellow)
                $allocationTime = $workOrder->updated_at;
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

        $workOrderData['color_key'] = $colorKey;
        $workOrderData['color_hex'] = $colorHex;

        // ── Ensure product_category is a string, not an object ──
        if (isset($workOrderData['product_category']) && is_array($workOrderData['product_category'])) {
            $workOrderData['product_category'] = $workOrder->productCategory ? $workOrder->productCategory->name : $workOrder->product_category;
        } elseif (!isset($workOrderData['product_category'])) {
            $workOrderData['product_category'] = $workOrder->productCategory ? $workOrder->productCategory->name : null;
        }

        // ── Ensure subcategory is a string, not an object ──
        if (isset($workOrderData['subcategory']) && is_array($workOrderData['subcategory'])) {
            $workOrderData['subcategory'] = $workOrder->subcategoryRelation ? $workOrder->subcategoryRelation->name : $workOrder->subcategory;
        } elseif (!isset($workOrderData['subcategory'])) {
            $workOrderData['subcategory'] = $workOrder->subcategoryRelation ? $workOrder->subcategoryRelation->name : null;
        }

        // ── Apply Data Masking if it's a PDF Work Order or if User is a Craftsman ──
        $isPdf = false;
        if (method_exists($workOrder, 'getFileTypeAttribute') || isset($workOrder->file_type)) {
            $isPdf = $workOrder->file_type === 'pdf';
        }

        // Robust fallback check
        if (!$isPdf && $workOrder->product_image) {
            $isPdf = strtolower(pathinfo($workOrder->product_image, PATHINFO_EXTENSION)) === 'pdf';
        }

        if ($isPdf || ($user && $this->isCraftsman($user))) {
            $this->maskSensitiveFields($workOrderData);
        }

        // Additional PDF information for UI
        $workOrderData['is_pdf_work_order'] = $isPdf;
        if ($isPdf && !extension_loaded('imagick')) {
            $workOrderData['pdf_conversion_status'] = 'unavailable'; // Help UI know why no images are generated
        }

        // ── Consolidate Gallery Images ──
        $allImages = [];
        if ($workOrder->product_image) {
            if (filter_var($workOrder->product_image, FILTER_VALIDATE_URL)) {
                $allImages[] = $workOrder->product_image;
            } elseif (str_starts_with($workOrder->product_image, 'images/') || str_starts_with($workOrder->product_image, 'uploads/')) {
                $allImages[] = asset($workOrder->product_image);
            } else {
                $allImages[] = asset('storage/' . $workOrder->product_image);
            }
        }
        if ($workOrder->relationLoaded('images')) {
            foreach ($workOrder->images as $img) {
                if ($img->image_url) {
                    $allImages[] = $img->image_url;
                }
            }
        }
        if ($workOrder->relationLoaded('product') && $workOrder->product && $workOrder->product->relationLoaded('images')) {
            foreach ($workOrder->product->images as $pImg) {
                if ($pImg->image_url) {
                    $allImages[] = $pImg->image_url;
                }
            }
        }
        $workOrderData['gallery_images'] = array_values(array_unique($allImages));
        $workOrderData['images'] = array_values(array_unique($allImages));

        return $workOrderData;
    }

    /**
     * Mask sensitive fields for PDF-based work orders or craftsman view.
     */
    private function maskSensitiveFields(array &$data): void
    {
        $fieldsToMask = [
            // 'work_order_number',
            // 'quantity',
            // 'product_name',
            // 'product_code',
            // 'design_code',
            // 'weight_from',
            // 'weight_to',
            // 'size',
            // 'customer_name',
            // 'reference_no',
            // 'narration_admin',
            // 'hallmark',
            // 'rodium',
            // 'hook',
            // 'stone',
            // 'enamel',
            // 'length',
            // 'tolerance',
            // 'tolerance_from',
            // 'tolerance_to',
            // 'customer_details',
            // 'price',
            // 'labor_charge',
            // 'other_charges'
        ];

        foreach ($fieldsToMask as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***';
            }
        }
    }

    /**
     * Resolve creator_type + creator_user_code from authenticated user.
     * Buyer  → 'buyer'     + bp_code
     * KeyUser→ 'key_user'  + user_code
     * User   → 'user'      + user_code
     * Craftsman → 'craftsman' + craftman_code
     * Admin  → 'super_admin'+ id
     */
    private function getCreatorInfo($user): array
    {
        if ($user instanceof \App\Models\Buyer) {
            return ['creator_type' => 'buyer', 'creator_user_code' => $user->bp_code];
        }
        if ($user instanceof \App\Models\KeyUser) {
            return ['creator_type' => 'key_user', 'creator_user_code' => $user->user_code];
        }
        if ($user instanceof \App\Models\User) {
            return ['creator_type' => 'user', 'creator_user_code' => $user->user_code];
        }
        if ($user instanceof \App\Models\Craftman) {
            return ['creator_type' => 'craftsman', 'creator_user_code' => $user->craftman_code];
        }
        return ['creator_type' => 'super_admin', 'creator_user_code' => null];
    }

    /**
     * Check work_order permission for roles that have permission system.
     * Returns true if allowed, false if denied.
     */
    private function checkPermission($user): bool
    {
        if ($this->isAdmin($user)) return true;
        if ($user instanceof \App\Models\Buyer) return true; // Buyers always have WO access
        // KeyUser, User, Craftsman have granular permissions
        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission('work_order');
        }
        return true;
    }

    // =========================================================================
    // INDEX — with tabs + counts (full SuperAdmin logic)
    // =========================================================================

    public function index(Request $request)
    {
        $user  = $request->user();
        if (!$this->checkPermission($user)) {
            return response()->json(['success' => false, 'message' => 'Forbidden – no work_order permission'], 403);
        }
        $admin = $this->isAdmin($user);
        $search = $request->get('search');

        // ── 1. Helper Definitions ──
        $scopeFilter = function ($query) use ($user, $admin) {
            if ($admin) return $query;
            if ($this->isCraftsman($user)) {
                return $query->where('allocated_craftsman_bp_code', $user->craftman_code);
            }
            return $query->where('bp_code', $user->bp_code);
        };

        $applyFilters = function ($query) use ($request, $search) {
            if ($request->filled('category_id'))    $query->where('product_category_id', $request->category_id);
            if ($request->filled('bp_code_filter') || $request->filled('bp_code')) {
                $query->where('bp_code', $request->get('bp_code_filter') ?: $request->get('bp_code'));
            }
            if ($request->filled('craftsman_code_filter') || $request->filled('craftsman_code')) {
                $query->where('allocated_craftsman_bp_code', $request->get('craftsman_code_filter') ?: $request->get('craftsman_code'));
            }
            if ($request->filled('subcategory_id'))   $query->where('subcategory_id', $request->subcategory_id);
            if ($request->filled('category_name'))    $query->where('product_category', $request->category_name);
            if ($request->filled('subcategory'))      $query->where('subcategory', $request->subcategory);
            if ($request->filled('subcategory_name')) $query->where('subcategory', $request->subcategory_name);
            if ($request->filled('design_code'))      $query->where('design_code', $request->design_code);
            if ($request->filled('craftsman_status')) $query->where('craftsman_status', $request->craftsman_status);
            if ($request->filled('product_code')) $query->where('product_code', $request->product_code);
            if ($request->filled('customer_name')) $query->where('customer_name', $request->customer_name);

            if ($request->filled('from_date')) $query->whereDate('due_date', '>=', $request->from_date);
            if ($request->filled('to_date'))   $query->whereDate('due_date', '<=', $request->to_date);

            if ($request->filled('type'))        $query->where('type', $request->type);
            if ($request->filled('size'))        $query->where('size', $request->size);
            if ($request->filled('weight_from')) $query->where('weight_from', '>=', $request->weight_from);
            if ($request->filled('weight_to'))   $query->where('weight_to', '<=', $request->weight_to);
            if ($request->filled('hallmark'))    $query->where('hallmark', $request->hallmark);
            if ($request->filled('rodium'))      $query->where('rodium', $request->rodium);
            if ($request->filled('hook'))        $query->where('hook', $request->hook);
            if ($request->filled('stone'))       $query->where('stone', $request->stone);
            if ($request->filled('enamel'))      $query->where('enamel', $request->enamel);
            if ($request->filled('length'))      $query->where('length', $request->length);

            if ($request->filled('work_order_ids')) {
                $ids = $request->work_order_ids;
                if (is_string($ids)) $ids = explode(',', $ids);
                if (is_array($ids)) $query->whereIn('id', $ids);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('work_order_number', 'LIKE', "%$search%")
                        ->orWhere('product_code', 'LIKE', "%$search%")
                        ->orWhere('design_code', 'LIKE', "%$search%")
                        ->orWhere('product_name', 'LIKE', "%$search%")
                        ->orWhere('customer_name', 'LIKE', "%$search%")
                        ->orWhere('bp_code', 'LIKE', "%$search%")
                        ->orWhere('allocated_craftsman_bp_code', 'LIKE', "%$search%")
                        ->orWhere('size', 'LIKE', "%$search%")
                        ->orWhere('length', 'LIKE', "%$search%")
                        ->orWhere('hallmark', 'LIKE', "%$search%")
                        ->orWhere('weight_from', 'LIKE', "%$search%")
                        ->orWhere('reference_no', 'LIKE', "%$search%");
                });
            }
            return $query;
        };

        // ── 2. Tab Detection ──
        $tab = $request->get('tab');
        if (!$tab) {
            $tabsLookup = [
                'all-orders' => 'all-orders', 'all' => 'all-orders',
                'new-orders' => 'new-orders', 'new' => 'new-orders',
                'allocated-orders' => 'allocated-orders', 'allocated' => 'allocated-orders',
                'in-process-orders' => 'in-process-orders', 'in_process' => 'in-process-orders',
                'for-approval-orders' => 'for-approval-orders', 'for_approval' => 'for-approval-orders',
                'completed-orders' => 'completed-orders', 'completed' => 'completed-orders',
                'rejected-orders' => 'rejected-orders', 'rejected' => 'rejected-orders',
                'overdue-orders' => 'overdue-orders', 'overdue' => 'overdue-orders',
            ];
            foreach ($tabsLookup as $flag => $target) {
                if ($request->has($flag)) { $tab = $target; break; }
            }
            if (!$tab) $tab = 'new-orders';
        }

        // ── Normalize tab name if passed via ?tab=... ──
        $tab = match ($tab) {
            'new'          => 'new-orders',
            'allocated'    => 'allocated-orders',
            'in_process'   => 'in-process-orders',
            'for-approval' => 'for-approval-orders',
            'for_approval' => 'for-approval-orders',
            'completed'    => 'completed-orders',
            'rejected'     => 'rejected-orders',
            'overdue'      => 'overdue-orders',
            'all'          => 'all-orders',
            default        => $tab
        };

        // ── 3. Tab Counts Calculation ──
        $counts = [
            'all'          => $applyFilters($scopeFilter(WorkOrder::query()))->count(),
            'new'          => $applyFilters($scopeFilter(WorkOrder::where('status', 'new')))->count(),
            'allocated'    => $applyFilters($scopeFilter(WorkOrder::where('status', 'allocated')->whereNotIn('craftsman_status', ['in_process', 'rejected', 'completed'])))->count(),
            'in_process'   => $applyFilters($scopeFilter(WorkOrder::where('craftsman_status', 'in_process')))->count(),
            'for_approval' => $applyFilters($scopeFilter(WorkOrder::where('status', 'for_approval')))->count(),
            'completed'    => $applyFilters($scopeFilter(WorkOrder::where('status', 'completed')))->count(),
            'rejected'     => $applyFilters($scopeFilter(WorkOrder::where('status', '!=', 'new')->where('craftsman_status', 'rejected')))->count(),
            'overdue'      => $applyFilters($scopeFilter(
                WorkOrder::where('status', '!=', 'completed')
                    ->where('craftsman_status', '!=', 'rejected')
                    ->where(function ($q) {
                        $q->whereDate('craftsman_due_date', '<', now()->toDateString())
                            ->orWhere(function ($sq) {
                                $sq->whereDate('craftsman_due_date', now()->toDateString())
                                    ->whereRaw('HOUR(NOW()) >= 12');
                            });
                    })
            ))->count(),
        ];

        // ── 4. Main Query Construction ──
        $query = WorkOrder::with(['productCategory', 'subcategoryRelation', 'buyer', 'craftsman', 'images', 'product.images']);
        $scopeFilter($query);
        $applyFilters($query);

        switch ($tab) {
            case 'new-orders':          $query->where('status', 'new'); break;
            case 'allocated-orders':    $query->where('status', 'allocated')->whereNotIn('craftsman_status', ['in_process', 'rejected', 'completed']); break;
            case 'in-process-orders':   $query->where('craftsman_status', 'in_process'); break;
            case 'for-approval-orders': $query->where('status', 'for_approval'); break;
            case 'completed-orders':    $query->where('status', 'completed'); break;
            case 'rejected-orders':     $query->where('status', '!=', 'new')->where('craftsman_status', 'rejected'); break;
            case 'overdue-orders':
                $query->where('status', '!=', 'completed')
                    ->where('craftsman_status', '!=', 'rejected')
                    ->where(function ($q) {
                        $q->whereDate('craftsman_due_date', '<', now()->toDateString())
                            ->orWhere(function ($sq) {
                                $sq->whereDate('craftsman_due_date', now()->toDateString())
                                    ->whereRaw('HOUR(NOW()) >= 12');
                            });
                    });
                break;
            case 'all-orders': break;
            default:
                if ($request->filled('status')) $query->where('status', $request->status);
                else $query->where('status', 'new');
                break;
        }

        // ── 5. Sorting ──
        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', $request->get('sort', 'desc'));
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) $sortOrder = 'desc';

        $allowedColumns = ['id', 'work_order_number', 'customer_name', 'product_name', 'quantity', 'due_date', 'status', 'bp_code', 'product_category', 'reference_no', 'type', 'size', 'length', 'weight_from', 'weight_to', 'hallmark', 'rodium', 'hook', 'stone', 'enamel', 'craftsman_due_date', 'created_at'];
        if (!in_array($sortBy, $allowedColumns)) $sortBy = 'created_at';
        $query->orderBy($sortBy, $sortOrder);

        // ── 6. Select Specific IDs (Moved to applyFilters) ──


        // ── 7. Handle Exports/PDF/Print Early ──
        if ($request->has('export')) {
            $workOrders = $query->get();
            $exportData = $workOrders->map(fn($o) => [
                'Work Order Number' => $o->work_order_number, 'Customer Name' => $o->customer_name, 'Product Name' => $o->product_name, 'Product Code' => $o->product_code, 'Quantity' => $o->quantity, 'Due Date' => $o->due_date ? $o->due_date : 'N/A', 'Status' => $o->status, 'Craftsman Status' => $o->craftsman_status,
            ]);
            $filename = 'work_orders_' . now()->format('Y-m-d_H-i-s') . '.csv';
            return response()->stream(function() use ($exportData) {
                $file = fopen('php://output', 'w');
                if ($exportData->isNotEmpty()) {
                    fputcsv($file, array_keys($exportData->first()));
                    foreach ($exportData as $row) fputcsv($file, $row);
                }
                fclose($file);
            }, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""]);
        }

        if ($request->has('print')) {
            $workOrders = $query->get();
            return response()->json(['success' => true, 'data' => $workOrders->map(fn($wo) => $this->transformWorkOrderResponse($wo))]);
        }

        if ($request->has('pdf')) {
            $workOrders = $query->with(['product.images', 'productCategory', 'subcategoryRelation', 'buyer', 'craftsman'])->get();
            return view('admin.work-order.bulk-print', compact('workOrders'));
        }

        // ── 8. Final Pagination ──
        $perPage  = $request->get('per_page', 10);
        $pageName = 'page';

        $workOrders = $query->paginate($perPage, ['*'], 'page')->withQueryString();
        $workOrders->getCollection()->transform(fn($wo) => $this->transformWorkOrderResponse($wo));

        return response()->json([
            'success' => true,
            'counts'  => $counts,
            'data'    => $workOrders
        ]);
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->checkPermission($user)) {
            return response()->json(['success' => false, 'message' => 'Forbidden – no work_order permission'], 403);
        }
        $workOrder = WorkOrder::with(['productCategory', 'subcategoryRelation', 'buyer', 'craftsman', 'images', 'product.images'])->find($id);

        if (!$workOrder) {
            return response()->json(['success' => false, 'message' => 'Work Order not found'], 404);
        }

        if (!$this->isAdmin($user)) {
            if ($this->isCraftsman($user) && $workOrder->allocated_craftsman_bp_code !== $user->craftman_code) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            if ($this->isBuyerSide($user) && $workOrder->bp_code !== $user->bp_code) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        }

        $subcategoryOptions = [];
        if ($workOrder->product_category_id) {
            $subcategoryOptions = ProductSubcategory::where('product_category_id', $workOrder->product_category_id)->get();
        }

        // Get product details if product_code exists
        $productCode = $workOrder->product_code;
        $designCode = $workOrder->design_code ?? null;
        $finalProductCode = $productCode ?: $designCode;

        $categoryId = $workOrder->product_category_id;
        $categoryName = $workOrder->productCategory ? $workOrder->productCategory->name : $workOrder->product_category;
        $subcategoryId = $workOrder->subcategory_id;
        $subcategoryName = $workOrder->subcategoryRelation ? $workOrder->subcategoryRelation->name : $workOrder->subcategory;
        $productImageUrl = $workOrder->product_image_url;

        $productImages = [];

        if ($finalProductCode) {
            $product = Product::with(['images', 'category', 'subcategory'])
                ->where('product_code', $finalProductCode)
                ->orWhere('design_code', $finalProductCode)
                ->first();

            if ($product) {
                if (!$designCode) {
                    $designCode = $product->design_code;
                }
                if (!$productCode) {
                    $productCode = $product->product_code;
                }
                // Use product's category/subcategory if work order doesn't have them
                if (!$categoryId) {
                    $categoryId = $product->product_category_id;
                    $categoryName = $product->category ? $product->category->name : $categoryName;
                }
                if (!$subcategoryId) {
                    $subcategoryId = $product->product_subcategory_id ?? $product->subcategory_id;
                    $subcategoryName = $product->subcategory ? $product->subcategory->name : $subcategoryName;
                }
                // Use product image if work order doesn't have one
                if (!$workOrder->product_image && $product->images->count() > 0) {
                    $productImageUrl = asset('storage/' . $product->images->first()->path);
                }

                foreach ($product->images as $pImg) {
                    $productImages[] = asset('storage/' . $pImg->path);
                }
            }
        }

        // Consolidate all images
        $allImages = [];
        if ($productImageUrl) $allImages[] = $productImageUrl;
        foreach ($workOrder->images as $img) {
            if ($img->image_url) $allImages[] = $img->image_url;
        }
        foreach ($productImages as $pUrl) {
            $allImages[] = $pUrl;
        }

        // Consolidate response using the helper
        $responseData = $this->transformWorkOrderResponse($workOrder);

        // Add additional details needed for show view
        $responseData['product_code'] = $productCode;
        $responseData['design_code'] = $designCode;
        $responseData['product_category_id'] = $categoryId;
        $responseData['category_id'] = $categoryId;
        $responseData['category_name'] = $categoryName;
        $responseData['subcategory_id'] = $subcategoryId;
        $responseData['subcategory_name'] = $subcategoryName;
        $responseData['product_image_url'] = $productImageUrl;
        $responseData['images'] = array_values(array_unique($allImages));

        // Re-mask if anything added above is sensitive (double check)
        $isPdf = $workOrder->file_type === 'pdf' || strtolower(pathinfo($workOrder->product_image ?? '', PATHINFO_EXTENSION)) === 'pdf';
        if ($isPdf || ($user && $this->isCraftsman($user))) {
            $this->maskSensitiveFields($responseData);
        }

        // Help the UI for "Show all images" if it's a PDF and conversion failed
        if ($isPdf && count($responseData['images']) === 1 && str_ends_with($responseData['images'][0], '.pdf')) {
            $responseData['pdf_view_required'] = true;
        }

        return response()->json([
            'success'             => true,
            'data'                => array_merge($responseData, [
                'product_code'        => $productCode,
                'design_code'         => $designCode,
                'product_category_id' => $categoryId,
                'category_id'         => $categoryId,
                'product_category'    => $categoryName,
                'category_name'       => $categoryName,
                'subcategory_id'      => $subcategoryId,
                'subcategory'         => $subcategoryName,
                'subcategory_name'    => $subcategoryName,
                'product_image_url'   => $productImageUrl,
                'images'              => array_values(array_unique($allImages))
            ]),
            'subcategory_options' => $subcategoryOptions
        ]);
    }

    /**
     * Generate PDF for selected work orders.
     */
    public function generatePdf(Request $request, $id = null)
    {
        $user = $request->user();
        if (!$this->checkPermission($user)) {
            return response()->json(['success' => false, 'message' => 'Forbidden – no work_order permission'], 403);
        }

        $ids = [];
        if ($id) {
            $ids = [$id];
        } elseif ($request->filled('work_order_ids')) {
            $ids = $request->work_order_ids;
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }
        }

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No work order IDs provided'], 400);
        }

        $query = WorkOrder::with(['productCategory', 'subcategoryRelation', 'buyer', 'craftsman', 'images', 'product.images'])
            ->whereIn('id', $ids);

        // Role-based scope (similar to index)
        if (!$this->isAdmin($user)) {
            if ($this->isCraftsman($user)) {
                $query->where('allocated_craftsman_bp_code', $user->craftman_code);
            } else {
                $query->where('bp_code', $user->bp_code);
            }
        }

        $workOrders = $query->get();

        if ($workOrders->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No matching work orders found'], 404);
        }

        // Transform data using existing helper
        $transformedWorkOrders = $workOrders->map(function ($wo) {
            return $this->transformWorkOrderResponse($wo);
        });

        $data = [
            'workOrders' => $transformedWorkOrders,
            'userRole'   => $user->role ?? null,
            'isAdmin'    => $this->isAdmin($user),
            'isCraftsman' => $this->isCraftsman($user),
            'isBuyer'    => $this->isBuyerSide($user),
        ];

        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'sans-serif');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(view('api.common.work-orders.generate-pdf', $data)->render());
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = count($ids) === 1
                ? "WorkOrder_" . $workOrders->first()->work_order_number . ".pdf"
                : "Bulk_WorkOrders_" . now()->format('Ymd_His') . ".pdf";

            return response($dompdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Access-Control-Expose-Headers', 'Content-Disposition');
        } catch (\Exception $e) {
            Log::error('Work Order PDF Generation Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate PDF. ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // STORE — full SuperAdmin create logic with product-code auto-fill
    // =========================================================================

    public function store(Request $request)
    {
        $user = $request->user();

        // Permission check
        if (!$this->checkPermission($user)) {
            return response()->json(['success' => false, 'message' => 'Forbidden – no work_order permission'], 403);
        }

        // Craftsmen cannot create work orders
        if ($this->isCraftsman($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to create work orders'], 403);
        }

        $validator = Validator::make($request->all(), [
            'bp_code'             => $this->isAdmin($user) ? 'required|string|exists:buyers,bp_code' : 'nullable',
            'customer_name'       => 'required|string|max:255',
            'quantity'            => 'required|integer|min:1',
            'product_name'        => 'nullable|string|max:255',
            'due_date'            => 'required|date',
            'product_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'product_images.*'    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'product_year'        => 'nullable|string',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'subcategory_id'      => 'nullable|exists:product_subcategories,id',
            'type'                => 'nullable|in:Piece,Pair',
            'open_close'          => 'nullable|in:Open,Close',
            'size'                => 'nullable|string|max:255',
            'length'              => 'nullable|string|max:255',
            'weight_from'         => 'nullable|numeric|min:0',
            'weight_to'           => 'nullable|numeric|gte:weight_from',
            'hallmark'            => 'nullable|string|max:255',
            'rodium'              => 'nullable|string|max:255',
            'hook'                => 'nullable|string|max:255',
            'stone'               => 'nullable|string|max:255',
            'enamel'              => 'nullable|string|max:255',
            'category_name'       => 'nullable|string|max:255',
            'product_category'    => 'nullable|string|max:255', // Added - can send category name directly
            'subcategory_name'    => 'nullable|string|max:255',
            'subcategory'         => 'nullable|string|max:255', // Added - can send subcategory name directly
            'product_code'        => 'nullable|string|max:255',
            'design_code'         => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $bpCode = $this->isAdmin($user) ? $request->bp_code : $user->bp_code;

        // ── Product code lookup & image auto-fill ──
        $finalProductCode = $request->product_code ?: $request->design_code;
        $productImage     = null;
        $existingProduct  = null;
        $designCode       = $request->design_code; // Default to input
        $storedProductCode = null; // Will store the actual product_code from database

        // Variables for auto-fill from existing product
        $productName = null;
        $type = null;
        $openClose = null;
        $weightFrom = null;
        $weightTo = null;
        $hallmark = null;
        $rodium = null;
        $hook = null;
        $size = null;
        $stone = null;
        $enamel = null;
        $length = null;

        if (!empty($finalProductCode)) {
            $existingProduct = Product::with(['images', 'category', 'subcategory'])
                ->where('product_code', $finalProductCode)
                ->orWhere('design_code', $finalProductCode)
                ->first();

            if ($existingProduct) {
                // Store the actual design_code and product_code from product
                $designCode = $existingProduct->design_code;
                $storedProductCode = $existingProduct->product_code;

                // If user entered design_code, use product_code for work order
                if ($existingProduct->design_code && $existingProduct->design_code === $finalProductCode) {
                    $finalProductCode = $existingProduct->product_code;
                }

                $productName = $existingProduct->product_name;
                $type        = $existingProduct->type;
                $openClose   = $existingProduct->open_close;
                $weightFrom  = $existingProduct->weight_from;
                $weightTo    = $existingProduct->weight_to;
                $hallmark    = $existingProduct->hallmark;
                $rodium      = $existingProduct->rodium;
                $hook        = $existingProduct->hook;
                $size        = $existingProduct->size;
                $stone       = $existingProduct->stone;
                $enamel      = $existingProduct->enamel;
                $length      = $existingProduct->length;

                // Auto-fill image from product if no manual upload
                if (!$request->hasFile('product_image') && $existingProduct->images->count() > 0) {
                    try {
                        $existingImage   = $existingProduct->images->first();
                        $sourceImagePath = storage_path('app/public/' . $existingImage->path);
                        if (file_exists($sourceImagePath)) {
                            $imageName       = time() . '_copied_from_product_' . basename($existingImage->path);
                            $destinationPath = public_path('images/work-orders/' . $imageName);
                            if (!file_exists(dirname($destinationPath))) mkdir(dirname($destinationPath), 0755, true);
                            copy($sourceImagePath, $destinationPath);
                            $productImage = 'images/work-orders/' . $imageName;
                            (new ImageWatermarkService())->addWatermark($productImage, true);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error copying product image for work order: ' . $e->getMessage());
                    }
                }
            }
        }
        // REMOVED: Auto-generation of product codes - only generate if explicitly requested by frontend

        // Direct image upload overrides
        if ($request->hasFile('product_image')) {
            try {
                $image     = $request->file('product_image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images/work-orders'), $imageName);
                $productImage = 'images/work-orders/' . $imageName;
                (new ImageWatermarkService())->addWatermark($productImage, true);
            } catch (\Exception $e) {
                Log::error('Error uploading product image for work order: ' . $e->getMessage());
            }
        }

        // ── Category / Subcategory resolution ──
        $categoryId      = $request->product_category_id;
        $categoryName    = null;
        $subcategoryId   = $request->subcategory_id;
        $subcategoryName = null;

        if ($existingProduct) {
            // Use product's category and subcategory IDs directly
            $categoryId      = $existingProduct->product_category_id;
            $categoryName    = $existingProduct->category ? $existingProduct->category->name : null;

            // Note: Product model uses product_subcategory_id whereas WorkOrder uses subcategory_id
            $subcategoryId   = $existingProduct->product_subcategory_id ?? $existingProduct->subcategory_id;
            $subcategoryName = $existingProduct->subcategory ? $existingProduct->subcategory->name : null;

            // Log for debugging
            Log::info('Auto-filling from product:', [
                'category_id' => $categoryId,
                'category_name' => $categoryName,
                'subcategory_id' => $subcategoryId,
                'subcategory_name' => $subcategoryName
            ]);
        } else {
            // Priority order: ID > name field > legacy name field
            // Resolve Category FIRST
            if ($request->filled('product_category_id')) {
                // If ID is provided, use it directly
                $cat          = ProductCategory::find($request->product_category_id);
                $categoryId   = $cat ? $cat->id : null;
                $categoryName = $cat ? $cat->name : null;
            } elseif ($request->filled('product_category')) {
                // If category name is provided directly
                $category     = ProductCategory::firstOrCreate(['name' => $request->product_category]);
                $categoryId   = $category->id;
                $categoryName = $category->name;
            } elseif ($request->filled('category_name')) {
                // Legacy support for category_name field
                $category     = ProductCategory::firstOrCreate(['name' => $request->category_name]);
                $categoryId   = $category->id;
                $categoryName = $category->name;
            }

            // Resolve Subcategory SECOND (independently, not requiring categoryId)
            if ($request->filled('subcategory_id')) {
                // If subcategory ID is provided, use it directly
                $sub             = ProductSubcategory::find($request->subcategory_id);
                $subcategoryId   = $sub ? $sub->id : null;
                $subcategoryName = $sub ? $sub->name : null;
            } elseif ($request->filled('subcategory')) {
                // If subcategory name is provided directly
                // Try to find existing subcategory with any category, or create new one
                $subcategory = ProductSubcategory::where('name', $request->subcategory)->first();

                if (!$subcategory && $categoryId) {
                    // Create new subcategory linked to the resolved category
                    $subcategory = ProductSubcategory::firstOrCreate([
                        'product_category_id' => $categoryId,
                        'name'                => $request->subcategory
                    ]);
                } elseif (!$subcategory && !$categoryId) {
                    // Create subcategory without category link if no category available
                    $subcategory = ProductSubcategory::create([
                        'name' => $request->subcategory
                    ]);
                }

                if ($subcategory) {
                    $subcategoryId   = $subcategory->id;
                    $subcategoryName = $subcategory->name;
                }
            } elseif ($request->filled('subcategory_name')) {
                // Legacy support for subcategory_name field
                // Try to find existing subcategory with any category, or create new one
                $subcategory = ProductSubcategory::where('name', $request->subcategory_name)->first();

                if (!$subcategory && $categoryId) {
                    // Create new subcategory linked to the resolved category
                    $subcategory = ProductSubcategory::firstOrCreate([
                        'product_category_id' => $categoryId,
                        'name'                => $request->subcategory_name
                    ]);
                } elseif (!$subcategory && !$categoryId) {
                    // Create subcategory without category link if no category available
                    $subcategory = ProductSubcategory::create([
                        'name' => $request->subcategory_name
                    ]);
                }

                if ($subcategory) {
                    $subcategoryId   = $subcategory->id;
                    $subcategoryName = $subcategory->name;
                }
            }

            // Log for debugging
            Log::info('Using provided category/subcategory:', [
                'category_id' => $categoryId,
                'category_name' => $categoryName,
                'subcategory_id' => $subcategoryId,
                'subcategory_name' => $subcategoryName
            ]);
        }

        // ── Determine creator_type & creator_user_code ──
        $creatorInfo = $this->getCreatorInfo($user);

        $workOrder = WorkOrder::create([
            'work_order_number'   => WorkOrder::generateWorkOrderNumber(),
            'product_image'       => $productImage,
            'bp_code'             => $bpCode,
            'customer_name'       => $request->customer_name,
            'reference_no'        => $request->reference_no,
            'due_date'            => $request->due_date,
            'product_category'    => $categoryName ?? $request->category_name,
            'product_category_id' => $categoryId,
            'subcategory'         => $subcategoryName ?? $request->subcategory_name,
            'subcategory_id'      => $subcategoryId,
            'quantity'            => $request->quantity,
            'type'                => $type ?? $request->type,
            'open_close'          => $openClose ?? $request->open_close,
            'weight_from'         => $weightFrom ?? $request->weight_from,
            'weight_to'           => $weightTo ?? $request->weight_to,
            'hallmark'            => $hallmark ?? $request->hallmark,
            'rodium'              => $rodium ?? $request->rodium,
            'hook'                => $hook ?? $request->hook,
            'size'                => $size ?? $request->size,
            'stone'               => $stone ?? $request->stone,
            'enamel'              => $enamel ?? $request->enamel,
            'length'              => $length ?? $request->length,
            'screw_name'          => $request->screw_name,
            'product_code'        => $storedProductCode ?: $finalProductCode,
            'relabel_code'        => $request->relabel_code,
            'design_code'         => $designCode,
            'product_name'        => $productName ?? $request->product_name,
            'craftsman_due_date'  => $request->craftsman_due_date,
            'narration_craftsman' => $request->narration_craftsman,
            'narration_admin'     => $request->narration_admin,
            'status'              => 'new',
            'creator_type'        => $creatorInfo['creator_type'],
            'creator_user_code'   => $creatorInfo['creator_user_code'],
            'created_by'          => $user->id,
        ]);

        // Multiple image uploads
        if ($request->hasFile('product_images')) {
            $watermarkService = new ImageWatermarkService();
            foreach ($request->file('product_images') as $index => $file) {
                $imageName = time() . '_multi_' . $index . '_' . $file->getClientOriginalName();
                $file->move(public_path('images/work-orders'), $imageName);
                $imagePath = 'images/work-orders/' . $imageName;
                $watermarkService->addWatermark($imagePath, true);

                WorkOrderImage::create([
                    'work_order_id' => $workOrder->id,
                    'image_path'    => $imagePath,
                ]);

                if (!$workOrder->product_image) {
                    $workOrder->update(['product_image' => $imagePath]);
                }
            }
        }

        $workOrder->load(['productCategory', 'subcategoryRelation', 'buyer', 'craftsman']);

        $productImages = [];
        if ($existingProduct && $existingProduct->images) {
            foreach ($existingProduct->images as $pImg) {
                $productImages[] = asset('storage/' . $pImg->path);
            }
        } elseif ($workOrder->product_code) {
            $product = Product::with(['images'])->where('product_code', $workOrder->product_code)->orWhere('design_code', $workOrder->product_code)->first();
            if ($product && $product->images) {
                foreach ($product->images as $pImg) {
                    $productImages[] = asset('storage/' . $pImg->path);
                }
            }
        }

        // Consolidate all images
        $allImages = [];
        if ($workOrder->product_image_url) $allImages[] = $workOrder->product_image_url;
        foreach ($workOrder->images as $img) {
            if ($img->image_url) $allImages[] = $img->image_url;
        }
        foreach ($productImages as $pUrl) {
            $allImages[] = $pUrl;
        }

        return response()->json([
            'success' => true,
            'message' => 'Work Order created successfully',
            'data'    => array_merge($workOrder->toArray(), [
                'product_code'        => $workOrder->product_code,
                'design_code'         => $workOrder->design_code,
                'product_category_id' => $categoryId,
                'category_id'         => $categoryId,
                'product_category'    => $categoryName,
                'category_name'       => $categoryName,
                'subcategory_id'      => $subcategoryId,
                'subcategory'         => $subcategoryName,
                'subcategory_name'    => $subcategoryName,
                'product_image_url'   => $workOrder->product_image_url,
                'images'              => array_values(array_unique($allImages))
            ])
        ], 201);
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->checkPermission($user)) {
            return response()->json(['success' => false, 'message' => 'Forbidden – no work_order permission'], 403);
        }
        $workOrder = WorkOrder::find($id);

        if (!$workOrder) {
            return response()->json(['success' => false, 'message' => 'Work Order not found'], 404);
        }

        if (!$this->isAdmin($user) && !($this->isBuyerSide($user) && $workOrder->bp_code === $user->bp_code)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'customer_name'       => 'sometimes|required|string|max:255',
            'quantity'            => 'sometimes|required|integer|min:1',
            'product_name'        => 'nullable|string|max:255',
            'due_date'            => 'sometimes|required|date',
            'product_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'product_images.*'    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'product_code'        => 'nullable|string|max:255',
            'design_code'         => 'nullable|string|max:255',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'subcategory_id'      => 'nullable|exists:product_subcategories,id',
            'product_category'    => 'nullable|string|max:255',
            'subcategory'         => 'nullable|string|max:255',
            'category_name'       => 'nullable|string|max:255',
            'subcategory_name'    => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $productImage = $workOrder->product_image;

        if ($request->hasFile('product_image')) {
            $image     = $request->file('product_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/work-orders'), $imageName);
            $productImage = 'images/work-orders/' . $imageName;
            (new ImageWatermarkService())->addWatermark($productImage, true);
        }

        // ── Product code lookup & image auto-fill ──
        $finalProductCode = $request->product_code ?: $request->design_code;
        $existingProduct  = null;
        $productImage     = $workOrder->product_image; // Keep existing image by default

        if (!empty($finalProductCode) && $finalProductCode !== $workOrder->product_code && $finalProductCode !== $workOrder->design_code) {
            // Only fetch new product if code changed
            $existingProduct = Product::with(['images', 'category', 'subcategory'])
                ->where('product_code', $finalProductCode)
                ->orWhere('design_code', $finalProductCode)
                ->first();

            if ($existingProduct && !$request->hasFile('product_image')) {
                // Auto-fill image from product only if no manual upload and product has images
                if ($existingProduct->images->count() > 0) {
                    try {
                        $existingImage   = $existingProduct->images->first();
                        $sourceImagePath = storage_path('app/public/' . $existingImage->path);
                        if (file_exists($sourceImagePath)) {
                            $imageName       = time() . '_copied_from_product_' . basename($existingImage->path);
                            $destinationPath = public_path('images/work-orders/' . $imageName);
                            if (!file_exists(dirname($destinationPath))) mkdir(dirname($destinationPath), 0755, true);
                            copy($sourceImagePath, $destinationPath);
                            $productImage = 'images/work-orders/' . $imageName;
                            (new ImageWatermarkService())->addWatermark($productImage, true);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error copying product image for work order update: ' . $e->getMessage());
                    }
                }
            }
        }

        if ($request->hasFile('product_image')) {
            try {
                $image     = $request->file('product_image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images/work-orders'), $imageName);
                $productImage = 'images/work-orders/' . $imageName;
                (new ImageWatermarkService())->addWatermark($productImage, true);
            } catch (\Exception $e) {
                Log::error('Error uploading product image for work order update: ' . $e->getMessage());
            }
        }

        // ── Category / Subcategory resolution ──
        $categoryId      = $request->product_category_id ?? $workOrder->product_category_id;
        $categoryName    = null;
        $subcategoryId   = $request->subcategory_id ?? $workOrder->subcategory_id;
        $subcategoryName = null;

        if ($existingProduct) {
            // Use product's category and subcategory IDs directly
            $categoryId      = $existingProduct->product_category_id;
            $categoryName    = $existingProduct->category ? $existingProduct->category->name : null;

            // Note: Product model uses product_subcategory_id whereas WorkOrder uses subcategory_id
            $subcategoryId   = $existingProduct->product_subcategory_id ?? $existingProduct->subcategory_id;
            $subcategoryName = $existingProduct->subcategory ? $existingProduct->subcategory->name : null;

            // Log for debugging
            Log::info('Update: Auto-filling from product:', [
                'category_id' => $categoryId,
                'category_name' => $categoryName,
                'subcategory_id' => $subcategoryId,
                'subcategory_name' => $subcategoryName
            ]);
        } else {
            // Priority order: ID > name field > legacy name field
            // Resolve Category FIRST
            if ($request->filled('product_category_id')) {
                // If ID is provided, use it directly
                $cat          = ProductCategory::find($request->product_category_id);
                $categoryId   = $cat ? $cat->id : null;
                $categoryName = $cat ? $cat->name : null;
            } elseif ($request->filled('product_category')) {
                // If category name is provided directly
                $category     = ProductCategory::firstOrCreate(['name' => $request->product_category]);
                $categoryId   = $category->id;
                $categoryName = $category->name;
            } elseif ($request->filled('category_name')) {
                // Legacy support for category_name field
                $category     = ProductCategory::firstOrCreate(['name' => $request->category_name]);
                $categoryId   = $category->id;
                $categoryName = $category->name;
            } elseif ($categoryId) {
                // Fallback to existing category ID from work order
                $cat          = ProductCategory::find($categoryId);
                $categoryName = $cat ? $cat->name : null;
            }

            // Resolve Subcategory SECOND (independently, not requiring categoryId)
            if ($request->filled('subcategory_id')) {
                // If subcategory ID is provided, use it directly
                $sub             = ProductSubcategory::find($request->subcategory_id);
                $subcategoryId   = $sub ? $sub->id : null;
                $subcategoryName = $sub ? $sub->name : null;
            } elseif ($request->filled('subcategory')) {
                // If subcategory name is provided directly
                // Try to find existing subcategory with any category, or create new one
                $subcategory = ProductSubcategory::where('name', $request->subcategory)->first();

                if (!$subcategory && $categoryId) {
                    // Create new subcategory linked to the resolved category
                    $subcategory = ProductSubcategory::firstOrCreate([
                        'product_category_id' => $categoryId,
                        'name'                => $request->subcategory
                    ]);
                } elseif (!$subcategory && !$categoryId) {
                    // Create subcategory without category link if no category available
                    $subcategory = ProductSubcategory::create([
                        'name' => $request->subcategory
                    ]);
                }

                if ($subcategory) {
                    $subcategoryId   = $subcategory->id;
                    $subcategoryName = $subcategory->name;
                }
            } elseif ($request->filled('subcategory_name')) {
                // Legacy support for subcategory_name field
                // Try to find existing subcategory with any category, or create new one
                $subcategory = ProductSubcategory::where('name', $request->subcategory_name)->first();

                if (!$subcategory && $categoryId) {
                    // Create new subcategory linked to the resolved category
                    $subcategory = ProductSubcategory::firstOrCreate([
                        'product_category_id' => $categoryId,
                        'name'                => $request->subcategory_name
                    ]);
                } elseif (!$subcategory && !$categoryId) {
                    // Create subcategory without category link if no category available
                    $subcategory = ProductSubcategory::create([
                        'name' => $request->subcategory_name
                    ]);
                }

                if ($subcategory) {
                    $subcategoryId   = $subcategory->id;
                    $subcategoryName = $subcategory->name;
                }
            } elseif ($subcategoryId) {
                // Fallback to existing subcategory ID from work order
                $sub             = ProductSubcategory::find($subcategoryId);
                $subcategoryName = $sub ? $sub->name : null;
            }

            // Log for debugging
            Log::info('Update: Using provided category/subcategory:', [
                'category_id' => $categoryId,
                'category_name' => $categoryName,
                'subcategory_id' => $subcategoryId,
                'subcategory_name' => $subcategoryName
            ]);
        }

        $status = $request->status ?? $workOrder->status;
        $updateData = [
            'product_image'       => $productImage,
            'customer_name'       => $request->customer_name,
            'reference_no'        => $request->reference_no,
            'due_date'            => $request->due_date,
            'product_category'    => $categoryName ?? $request->category_name,
            'product_category_id' => $categoryId,
            'subcategory'         => $subcategoryName ?? $request->subcategory_name,
            'subcategory_id'      => $subcategoryId,
            'quantity'            => $request->quantity,
            'type'                => $request->type ?? $workOrder->type,
            'open_close'          => $request->open_close ?? $workOrder->open_close,
            'weight_from'         => $request->weight_from ?? $workOrder->weight_from,
            'weight_to'           => $request->weight_to ?? $workOrder->weight_to,
            'hallmark'            => $request->hallmark ?? $workOrder->hallmark,
            'rodium'              => $request->rodium ?? $workOrder->rodium,
            'hook'                => $request->hook ?? $workOrder->hook,
            'size'                => $request->size ?? $workOrder->size,
            'stone'               => $request->stone ?? $workOrder->stone,
            'enamel'              => $request->enamel ?? $workOrder->enamel,
            'length'              => $request->length ?? $workOrder->length,
            'screw_name'          => $request->screw_name ?? $workOrder->screw_name,
            'product_name'        => $request->product_name,
            'narration_admin'     => $request->narration_admin ?? $workOrder->narration_admin,
            'narration_craftsman' => $request->narration_craftsman ?? $workOrder->narration_craftsman,
            'craftsman_due_date'  => $request->craftsman_due_date ?? $workOrder->craftsman_due_date,
            'status'              => $status,
        ];

        // If resetting to "new", clear craftsman-specific fields
        if ($status === 'new') {
            $updateData['craftsman_status'] = null;
            $updateData['allocated_craftsman_bp_code'] = null;
        }

        $workOrder->update($updateData);

        // Handle additional images
        if ($request->hasFile('product_images')) {
            $watermarkService = new ImageWatermarkService();
            foreach ($request->file('product_images') as $index => $file) {
                $imageName = time() . '_multi_' . $index . '_' . $file->getClientOriginalName();
                $file->move(public_path('images/work-orders'), $imageName);
                $imagePath = 'images/work-orders/' . $imageName;
                $watermarkService->addWatermark($imagePath, true);
                WorkOrderImage::create([
                    'work_order_id' => $workOrder->id,
                    'image_path'    => $imagePath,
                ]);
            }
        }

        $workOrder->load(['productCategory', 'subcategoryRelation', 'buyer', 'craftsman']);

        $productImages = [];
        $productCode = $workOrder->product_code;
        $designCode = $workOrder->design_code;
        $finalProductCode = $productCode ?: $designCode;
        if ($finalProductCode) {
            $product = Product::with(['images'])->where('product_code', $finalProductCode)
                ->orWhere('design_code', $finalProductCode)->first();
            if ($product && $product->images) {
                foreach ($product->images as $pImg) {
                    $productImages[] = asset('storage/' . $pImg->path);
                }
            }
        }

        $categoryName = $workOrder->productCategory ? $workOrder->productCategory->name : $workOrder->product_category;
        $subcategoryName = $workOrder->subcategoryRelation ? $workOrder->subcategoryRelation->name : $workOrder->subcategory;

        // Consolidate all images
        $allImages = [];
        if ($workOrder->product_image_url) $allImages[] = $workOrder->product_image_url;
        foreach ($workOrder->images as $img) {
            if ($img->image_url) $allImages[] = $img->image_url;
        }
        foreach ($productImages as $pUrl) {
            $allImages[] = $pUrl;
        }

        return response()->json([
            'success' => true,
            'message' => 'Work Order updated successfully',
            'data'    => array_merge($workOrder->toArray(), [
                'category_id'       => $workOrder->product_category_id,
                'category_name'     => $categoryName,
                'subcategory_id'    => $workOrder->subcategory_id,
                'subcategory_name'  => $subcategoryName,
                'product_image_url' => $workOrder->product_image_url,
                'images'            => array_values(array_unique($allImages))
            ])
        ]);
    }

    // =========================================================================
    // DELETE
    // =========================================================================

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->checkPermission($user)) {
            return response()->json(['success' => false, 'message' => 'Forbidden – no work_order permission'], 403);
        }
        $workOrder = WorkOrder::find($id);

        if (!$workOrder) {
            return response()->json(['success' => false, 'message' => 'Work Order not found'], 404);
        }

        if (!$this->isAdmin($user) && !($this->isBuyerSide($user) && $workOrder->bp_code === $user->bp_code)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $workOrder->delete();

        return response()->json(['success' => true, 'message' => 'Work Order deleted successfully']);
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

        $workOrder = WorkOrder::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'allocated_craftsman_bp_code' => 'required|exists:craftmen,craftman_code',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $workOrder->update([
            'allocated_craftsman_bp_code' => $request->allocated_craftsman_bp_code,
            'status'                      => 'allocated',
            'craftsman_status'            => 'allocated',
        ]);

        // Send Notification
        try {
            $craftsman = Craftman::where('craftman_code', $request->allocated_craftsman_bp_code)->first();
            if ($craftsman && $craftsman->fcm_token) {
                $craftsman->notify(new WorkOrderAllocated($workOrder));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Work Order allocated successfully!',
            'data' => $this->transformWorkOrderResponse($workOrder)
        ]);
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

        $validator = Validator::make($request->all(), [
            'work_order_ids'              => 'required|array',
            'work_order_ids.*'            => 'exists:work_orders,id',
            'allocated_craftsman_bp_code' => 'required|exists:craftmen,craftman_code',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $workOrderIds    = $request->input('work_order_ids');
        $craftsmanBpCode = $request->input('allocated_craftsman_bp_code');

        WorkOrder::whereIn('id', $workOrderIds)->update([
            'allocated_craftsman_bp_code' => $craftsmanBpCode,
            'status'                      => 'allocated',
            'craftsman_status'            => 'allocated',
        ]);

        try {
            $craftsman = Craftman::where('craftman_code', $craftsmanBpCode)->first();
            if ($craftsman && $craftsman->fcm_token) {
                $count      = count($workOrderIds);
                $message    = "You have been allocated {$count} new Work Orders.";
                $firstOrder = WorkOrder::find($workOrderIds[0]);
                $craftsman->notify(new WorkOrderAllocated($firstOrder, $message));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notification: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => count($workOrderIds) . ' Work Orders allocated successfully!']);
    }

    // =========================================================================
    // RE-ALLOCATE — Admin only
    // =========================================================================

    public function reallocate(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $workOrder = WorkOrder::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'allocated_craftsman_bp_code' => 'required|exists:craftmen,craftman_code',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $workOrder->update([
            'allocated_craftsman_bp_code' => $request->allocated_craftsman_bp_code,
            'status'                      => 'allocated',
            'craftsman_status'            => 'allocated',
        ]);

        try {
            $craftsman = Craftman::where('craftman_code', $request->allocated_craftsman_bp_code)->first();
            if ($craftsman && $craftsman->fcm_token) {
                $craftsman->notify(new WorkOrderAllocated($workOrder, "Work Order #{$workOrder->work_order_number} has been reallocated to you."));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Work Order reallocated successfully!',
            'data' => $this->transformWorkOrderResponse($workOrder)
        ]);
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
            'work_order_ids'   => 'required|array',
            'work_order_ids.*' => 'exists:work_orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $workOrderIds = $request->input('work_order_ids');

        WorkOrder::whereIn('id', $workOrderIds)
            ->where('status', 'for_approval')
            ->update([
                'status'      => 'completed',
                'approved_by' => $user->id,
            ]);

        // Notify Original Requester (Grouped)
        $recipients = [];
        foreach ($workOrderIds as $id) {
            try {
                $workOrder = WorkOrder::find($id);
                if ($workOrder) {
                    $recipientKey = "{$workOrder->creator_type}_{$workOrder->creator_user_code}";
                    if (!isset($recipients[$recipientKey])) {
                        $recipient = null;
                        if ($workOrder->creator_type === 'buyer') {
                            $recipient = Buyer::where('bp_code', $workOrder->creator_user_code)->first();
                        } elseif ($workOrder->creator_type === 'key_user') {
                            $recipient = KeyUser::where('user_code', $workOrder->creator_user_code)->first();
                        } elseif ($workOrder->creator_type === 'user') {
                            $recipient = User::where('user_code', $workOrder->creator_user_code)->first();
                        }

                        if ($recipient) {
                            $recipients[$recipientKey] = [
                                'model' => $recipient,
                                'count' => 0,
                                'lastOrder' => $workOrder
                            ];
                        }
                    }

                    if (isset($recipients[$recipientKey])) {
                        $recipients[$recipientKey]['count']++;
                        $recipients[$recipientKey]['lastOrder'] = $workOrder;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to resolve recipient for work order: ' . $e->getMessage());
            }
        }

        foreach ($recipients as $data) {
            try {
                $recipient = $data['model'];
                if ($recipient && $recipient->fcm_token) {
                    $count = $data['count'];
                    $lastOrder = $data['lastOrder'];
                    $message = $count > 1
                        ? "{$count} of your Work Orders have been completed and approved by Admin."
                        : "Your Work Order #{$lastOrder->work_order_number} has been completed and approved by Admin.";

                    $recipient->notify(new WorkOrderCompleted($lastOrder, $message));
                }
            } catch (\Exception $e) {
                Log::error('Failed to notify requester in bulk: ' . $e->getMessage());
            }
        }

        return response()->json(['success' => true, 'message' => count($workOrderIds) . ' Work Orders approved successfully!']);
    }

    // =========================================================================
    // CRAFTSMAN ACTIONS — Accept, Reject, Complete
    // =========================================================================

    public function acceptWorkOrder(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->isCraftsman($user)) {
            return response()->json(['success' => false, 'message' => 'Only craftsmen can perform this action'], 403);
        }

        $workOrder = WorkOrder::where('allocated_craftsman_bp_code', $user->craftman_code)->find($id);
        if (!$workOrder) return response()->json(['success' => false, 'message' => 'Work Order not found or not allocated'], 404);

        if ($workOrder->craftsman_status !== 'allocated') {
            return response()->json(['success' => false, 'message' => 'Work order cannot be accepted in current status'], 400);
        }

        $workOrder->update(['craftsman_status' => 'in_process']);

        return response()->json([
            'success' => true,
            'message' => 'Work order accepted',
            'data' => $this->transformWorkOrderResponse($workOrder)
        ]);
    }

    public function rejectWorkOrder(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->isCraftsman($user)) {
            return response()->json(['success' => false, 'message' => 'Only craftsmen can perform this action'], 403);
        }

        $workOrder = WorkOrder::where('allocated_craftsman_bp_code', $user->craftman_code)->find($id);
        if (!$workOrder) return response()->json(['success' => false, 'message' => 'Work Order not found'], 404);

        if ($workOrder->craftsman_status !== 'allocated') {
            return response()->json(['success' => false, 'message' => 'Work order cannot be rejected in current status'], 400);
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $workOrder->update([
            'craftsman_status'  => 'rejected',
            'rejection_reason'  => $request->rejection_reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Work order rejected',
            'data' => $this->transformWorkOrderResponse($workOrder)
        ]);
    }

    public function completeWorkOrder(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->isCraftsman($user)) {
            return response()->json(['success' => false, 'message' => 'Only craftsmen can perform this action'], 403);
        }

        $workOrder = WorkOrder::where('allocated_craftsman_bp_code', $user->craftman_code)->find($id);
        if (!$workOrder) return response()->json(['success' => false, 'message' => 'Work Order not found'], 404);

        if ($workOrder->craftsman_status !== 'in_process') {
            return response()->json(['success' => false, 'message' => 'Work order cannot be completed in current status'], 400);
        }

        $validator = Validator::make($request->all(), [
            'weight' => 'nullable|numeric|min:0'
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $data = ['craftsman_status' => 'completed', 'status' => 'for_approval'];
        if ($request->has('weight')) $data['weight'] = $request->weight;

        $workOrder->update($data);

        // Notify Admins
        try {
            $admins = ProcessOwner::whereNotNull('fcm_token')->get();
            foreach ($admins as $admin) {
                $admin->notify(new WorkOrderCompleted($workOrder));
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify admins: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Work order completed',
            'data' => $this->transformWorkOrderResponse($workOrder)
        ]);
    }

    // =========================================================================
    // BULK CRAFTSMAN ACTIONS
    // =========================================================================

    public function bulkAcceptWorkOrders(Request $request)
    {
        $user = $request->user();
        if (!$this->isCraftsman($user)) {
            return response()->json(['success' => false, 'message' => 'Only craftsmen can perform this action'], 403);
        }

        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:work_orders,id']);

        $count = WorkOrder::whereIn('id', $request->ids)
            ->where('allocated_craftsman_bp_code', $user->craftman_code)
            ->where('craftsman_status', 'allocated')
            ->update(['craftsman_status' => 'in_process']);

        return response()->json(['success' => true, 'message' => "$count work orders accepted"]);
    }

    public function bulkRejectWorkOrders(Request $request)
    {
        $user = $request->user();
        if (!$this->isCraftsman($user)) {
            return response()->json(['success' => false, 'message' => 'Only craftsmen can perform this action'], 403);
        }

        $request->validate([
            'ids'              => 'required|array',
            'ids.*'            => 'exists:work_orders,id',
            'rejection_reason' => 'required|string'
        ]);

        $count = WorkOrder::whereIn('id', $request->ids)
            ->where('allocated_craftsman_bp_code', $user->craftman_code)
            ->where('craftsman_status', 'allocated')
            ->update([
                'craftsman_status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
            ]);

        return response()->json(['success' => true, 'message' => "$count work orders rejected"]);
    }

    public function bulkCompleteWorkOrders(Request $request)
    {
        $user = $request->user();
        if (!$this->isCraftsman($user)) {
            return response()->json(['success' => false, 'message' => 'Only craftsmen can perform this action'], 403);
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:work_orders,id',
        ]);

        // Optional: You can send a common weight for all, or individual weights per work order
        // If sending individual weights, format should be: { "ids": [1,2], "weights": {"1": 10.5, "2": 12.3} }
        $weights = $request->input('weights', []);

        $count = 0;
        foreach ($request->ids as $id) {
            $workOrder = WorkOrder::where('allocated_craftsman_bp_code', $user->craftman_code)
                ->where('id', $id)
                ->where('craftsman_status', 'in_process')
                ->first();

            if ($workOrder) {
                $data = [
                    'craftsman_status' => 'completed',
                    'status' => 'for_approval'
                ];

                // Add weight if provided (either common or specific to this work order)
                if (isset($weights[$id])) {
                    $data['weight'] = $weights[$id];
                } elseif ($request->has('weight')) {
                    $data['weight'] = $request->weight;
                }

                $workOrder->update($data);
                $count++;
                $lastOrder = $workOrder;
            }
        }

        // Notify Admins once after bulk completion
        if ($count > 0 && isset($lastOrder)) {
            try {
                $admins = ProcessOwner::whereNotNull('fcm_token')->get();
                $message = "{$count} Work Orders have been completed by craftsman {$user->craftman_code}.";
                foreach ($admins as $admin) {
                    $admin->notify(new WorkOrderCompleted($lastOrder, $message));
                }
            } catch (\Exception $e) {
                Log::error('Failed to notify admins in bulk: ' . $e->getMessage());
            }
        }

        return response()->json(['success' => true, 'message' => "$count work orders completed and sent for approval"]);
    }

    // =========================================================================
    // HELPER - Generate unique product code
    // =========================================================================

    private function generateUniqueProductCode()
    {
        $latestOrder = WorkOrder::where('product_code', 'LIKE', 'OO%')
            ->orderBy('product_code', 'desc')
            ->first();

        if (!$latestOrder) return 'OO001';

        $numericPart = preg_replace('/[^0-9]/', '', $latestOrder->product_code);
        $nextNumber  = intval($numericPart) + 1;
        return 'OO' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    // =========================================================================
    // HELPER - Get BP Codes for Dropdown Filtering
    // =========================================================================

    public function getBpCodes(Request $request)
    {
        $user = $request->user();

        if ($this->isAdmin($user)) {
            $buyers = \App\Models\Buyer::select('bp_code', 'business_name', 'name')->get();
            return response()->json(['success' => true, 'data' => $buyers]);
        }

        // For Buyer/KeyUser/User, return their own bp_code
        if ($this->isBuyerSide($user)) {
            $buyer = \App\Models\Buyer::where('bp_code', $user->bp_code)
                ->select('bp_code', 'business_name', 'name')
                ->first();
            return response()->json(['success' => true, 'data' => $buyer ? [$buyer] : []]);
        }

        return response()->json(['success' => true, 'data' => []]);
    }

    public function getCraftmanCodes(Request $request)
    {
        $user = $request->user();

        if ($this->isAdmin($user)) {
            $craftsmen = \App\Models\Craftman::select('craftman_code', 'business_name', 'name')->get();
            return response()->json(['success' => true, 'data' => $craftsmen]);
        }

        if ($this->isCraftsman($user)) {
            $craftsman = \App\Models\Craftman::where('craftman_code', $user->craftman_code)
                ->select('craftman_code', 'business_name', 'name')
                ->first();
            return response()->json(['success' => true, 'data' => $craftsman ? [$craftsman] : []]);
        }

        return response()->json(['success' => true, 'data' => []]);
    }

    // =========================================================================
    // DASHBOARD STATS - Role-based statistics for all panels
    // =========================================================================

    public function getDashboardStats(Request $request)
    {
        $user = $request->user();

        // Check if user has dashboard permission(except Admin/SuperAdmin who have it by default)
        if (!$this->isAdmin($user)) {
            if (method_exists($user, 'hasPermission') && !$user->hasPermission('dashboard')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden – no dashboard permission'
                ], 403);
            }
        }

        if ($this->isAdmin($user)) {
            // Admin/SuperAdmin sees global statistics
            return $this->getAdminDashboardStats();
        }

        if ($this->isCraftsman($user)) {
            // Craftsman sees their own statistics
            return $this->getCraftsmanDashboardStats($user);
        }

        if ($this->isBuyerSide($user)) {
            // Buyer/KeyUser/User sees their own statistics
            return $this->getBuyerDashboardStats($user);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 403);
    }

    /**
     * Dashboard stats for Admin/SuperAdmin
     */
    private function getAdminDashboardStats()
    {
        $buyersCount = \App\Models\Buyer::count();
        $craftsmenCount = \App\Models\Craftman::count();
        $productsCount = \App\Models\Product::count();
        $designsCount = \App\Models\Product::where('design_status', 'Accepted')->count();
        $workOrdersCount = \App\Models\WorkOrder::count();
        $purchaseOrdersCount = \App\Models\PurchaseOrder::count();
        $usersCount = \App\Models\User::count();
        $keyUsersCount = \App\Models\KeyUser::count();
        $cataloguesCount = \App\Models\Product::where('design_status', 'Accepted')->whereNotNull('design_code')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'totalBusinessPartners' => $buyersCount + $craftsmenCount,
                'totalBuyers' => $buyersCount,
                'totalCraftsmen' => $craftsmenCount,
                'totalWorkOrders' => $workOrdersCount,
                'totalProducts' => $productsCount,
                'totalDesigns' => $designsCount,
                'totalCatalogues' => $cataloguesCount,
                'totalPurchaseOrders' => $purchaseOrdersCount,
                'totalUsers' => $usersCount,
                'totalKeyUsers' => $keyUsersCount,
                // Status breakdown for work orders
                'newOrders' => \App\Models\WorkOrder::where('status', 'new')->count(),
                'allocatedOrders' => \App\Models\WorkOrder::where('status', 'allocated')->whereNotIn('craftsman_status', ['in_process', 'rejected', 'completed'])->count(),
                'inProcessOrders' => \App\Models\WorkOrder::where('craftsman_status', 'in_process')->count(),
                'forApprovalOrders' => \App\Models\WorkOrder::where('status', 'for_approval')->count(),
                'completedOrders' => \App\Models\WorkOrder::where('status', 'completed')->count(),
                'rejectedOrders' => \App\Models\WorkOrder::where('status', '!=', 'new')->where('craftsman_status', 'rejected')->count(),
            ]
        ]);
    }

    /**
     * Dashboard stats for Craftsman
     */
    private function getCraftsmanDashboardStats($user)
    {
        $craftsmanCode = $user->craftman_code;

        // Scope queries to craftsman's allocated work orders
        $scopeFilter = function ($query) use ($craftsmanCode) {
            return $query->where('allocated_craftsman_bp_code', $craftsmanCode);
        };

        return response()->json([
            'success' => true,
            'data' => [
                'totalWorkOrders' => $scopeFilter(\App\Models\WorkOrder::query())->count(),
                'newOrders' => $scopeFilter(\App\Models\WorkOrder::where('status', 'new'))->count(),
                'allocatedOrders' => $scopeFilter(\App\Models\WorkOrder::where('status', 'allocated')->whereNotIn('craftsman_status', ['in_process', 'rejected', 'completed']))->count(),
                'inProcessOrders' => $scopeFilter(\App\Models\WorkOrder::where('craftsman_status', 'in_process'))->count(),
                'forApprovalOrders' => $scopeFilter(\App\Models\WorkOrder::where('status', 'for_approval'))->count(),
                'completedOrders' => $scopeFilter(\App\Models\WorkOrder::where('status', 'completed'))->count(),
                'rejectedOrders' => $scopeFilter(\App\Models\WorkOrder::where('status', '!=', 'new')->where('craftsman_status', 'rejected'))->count(),
            ]
        ]);
    }

    /**
     * Dashboard stats for Buyer/KeyUser/User
     */
    private function getBuyerDashboardStats($user)
    {
        $bpCode = $user->bp_code;

        // Scope queries to buyer's work orders
        $scopeFilter = function ($query) use ($bpCode) {
            return $query->where('bp_code', $bpCode);
        };

        return response()->json([
            'success' => true,
            'data' => [
                'totalWorkOrders' => $scopeFilter(\App\Models\WorkOrder::query())->count(),
                'newOrders' => $scopeFilter(\App\Models\WorkOrder::where('status', 'new'))->count(),
                'allocatedOrders' => $scopeFilter(\App\Models\WorkOrder::where('status', 'allocated')->whereNotIn('craftsman_status', ['in_process', 'rejected', 'completed']))->count(),
                'inProcessOrders' => $scopeFilter(\App\Models\WorkOrder::where('craftsman_status', 'in_process'))->count(),
                'forApprovalOrders' => $scopeFilter(\App\Models\WorkOrder::where('status', 'for_approval'))->count(),
                'completedOrders' => $scopeFilter(\App\Models\WorkOrder::where('status', 'completed'))->count(),
                'rejectedOrders' => $scopeFilter(\App\Models\WorkOrder::where('status', '!=', 'new')->where('craftsman_status', 'rejected'))->count(),
                'totalProducts' => $scopeFilter(\App\Models\Product::query())->count(),
                'totalPurchaseOrders' => $scopeFilter(\App\Models\PurchaseOrder::query())->count(),
            ]
        ]);
    }
}
