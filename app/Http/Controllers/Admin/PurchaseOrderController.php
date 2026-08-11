<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Design;
use App\Models\Craftman;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Models\ProductImage;
use App\Services\ImageWatermarkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PurchaseOrderController extends Controller
{
    /**
     * Display all Purchase Orders with Tab Filtering
     */
    public function index(Request $request)
    {
        // Handle search and filtering
        $search = $request->get('search');
        
        // Handle new filter parameters
        $filterPoCode = $request->get('filter_po_code');
        $filterCraftsman = $request->get('filter_craftsman');
        $filterDesignCode = $request->get('filter_design_code');
        $filterStatus = $request->get('filter_status');
        $filterDateFrom = $request->get('filter_date_from');
        $filterDateTo = $request->get('filter_date_to');
        
        // Handle sorting from dropdown
        $sort = $request->get('sort', 'latest');
        
        // Base queries for each tab
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
        $rejectedOrdersQuery = PurchaseOrder::where(function($q) {
            $q->where('craftsman_status', 'rejected')
              ->orWhereRaw('JSON_LENGTH(rejected_items) > 0');
        });
        
        // Apply search filter
        if ($search) {
            $searchTerm = '%' . $search . '%';
            $createdOrdersQuery->where(function($q) use ($searchTerm) {
                $q->where('purchase_order_code', 'LIKE', $searchTerm)
                  ->orWhere('notes', 'LIKE', $searchTerm)
                  ->orWhereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", [$searchTerm]);
            });
            $allocatedOrdersQuery->where(function($q) use ($searchTerm) {
                $q->where('purchase_order_code', 'LIKE', $searchTerm)
                  ->orWhere('notes', 'LIKE', $searchTerm)
                  ->orWhereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", [$searchTerm]);
            });
            $inProcessOrdersQuery->where(function($q) use ($searchTerm) {
                $q->where('purchase_order_code', 'LIKE', $searchTerm)
                  ->orWhere('notes', 'LIKE', $searchTerm)
                  ->orWhereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", [$searchTerm]);
            });
            $forApprovalOrdersQuery->where(function($q) use ($searchTerm) {
                $q->where('purchase_order_code', 'LIKE', $searchTerm)
                  ->orWhere('notes', 'LIKE', $searchTerm)
                  ->orWhereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", [$searchTerm]);
            });
            $completedOrdersQuery->where(function($q) use ($searchTerm) {
                $q->where('purchase_order_code', 'LIKE', $searchTerm)
                  ->orWhere('notes', 'LIKE', $searchTerm)
                  ->orWhereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", [$searchTerm]);
            });
            $rejectedOrdersQuery->where(function($q) use ($searchTerm) {
                $q->where('purchase_order_code', 'LIKE', $searchTerm)
                  ->orWhere('notes', 'LIKE', $searchTerm)
                  ->orWhereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", [$searchTerm]);
            });
        }
        
        // Apply new filters
        if ($filterPoCode) {
            $filterTerm = '%' . $filterPoCode . '%';
            $createdOrdersQuery->where('purchase_order_code', 'LIKE', $filterTerm);
            $allocatedOrdersQuery->where('purchase_order_code', 'LIKE', $filterTerm);
            $inProcessOrdersQuery->where('purchase_order_code', 'LIKE', $filterTerm);
            $forApprovalOrdersQuery->where('purchase_order_code', 'LIKE', $filterTerm);
            $completedOrdersQuery->where('purchase_order_code', 'LIKE', $filterTerm);
            $rejectedOrdersQuery->where('purchase_order_code', 'LIKE', $filterTerm);
        }
        
        if ($filterCraftsman) {
            $filterTerm = '%' . $filterCraftsman . '%';
            $allocatedOrdersQuery->where('allocated_craftsman_code', 'LIKE', $filterTerm);
            $inProcessOrdersQuery->where('allocated_craftsman_code', 'LIKE', $filterTerm);
            $forApprovalOrdersQuery->where('allocated_craftsman_code', 'LIKE', $filterTerm);
            $completedOrdersQuery->where('allocated_craftsman_code', 'LIKE', $filterTerm);
            $rejectedOrdersQuery->where('allocated_craftsman_code', 'LIKE', $filterTerm);
        }

        if ($filterDesignCode) {
            $filterTerm = '%' . $filterDesignCode . '%';
            $createdOrdersQuery->whereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", [$filterTerm]);
            $allocatedOrdersQuery->whereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", [$filterTerm]);
            $inProcessOrdersQuery->whereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", [$filterTerm]);
            $forApprovalOrdersQuery->whereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", [$filterTerm]);
            $completedOrdersQuery->whereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", [$filterTerm]);
            $rejectedOrdersQuery->whereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", [$filterTerm]);
        }
        
        if ($filterStatus) {
            // Apply status filter based on current tab context
            switch($filterStatus) {
                case 'created':
                    // Only affects created orders tab
                    break;
                case 'allocated':
                    // Only affects allocated orders tab
                    break;
                case 'in_process':
                    // Only affects in-process orders tab
                    break;
                case 'for_approval':
                    // Only affects for-approval orders tab
                    break;
                case 'completed':
                    // Only affects completed orders tab
                    break;
                case 'rejected':
                    // Only affects rejected orders tab
                    break;
            }
        }
        
        if ($filterDateFrom) {
            $createdOrdersQuery->where('due_date', '>=', $filterDateFrom);
            $allocatedOrdersQuery->where('due_date', '>=', $filterDateFrom);
            $inProcessOrdersQuery->where('due_date', '>=', $filterDateFrom);
            $forApprovalOrdersQuery->where('due_date', '>=', $filterDateFrom);
            $completedOrdersQuery->where('due_date', '>=', $filterDateFrom);
            $rejectedOrdersQuery->where('due_date', '>=', $filterDateFrom);
        }
        
        if ($filterDateTo) {
            $createdOrdersQuery->where('due_date', '<=', $filterDateTo);
            $allocatedOrdersQuery->where('due_date', '<=', $filterDateTo);
            $inProcessOrdersQuery->where('due_date', '<=', $filterDateTo);
            $forApprovalOrdersQuery->where('due_date', '<=', $filterDateTo);
            $completedOrdersQuery->where('due_date', '<=', $filterDateTo);
            $rejectedOrdersQuery->where('due_date', '<=', $filterDateTo);
        }
        
        // Apply Overdue filter
        if ($request->get('overdue') == 1) {
            $createdOrdersQuery->where('due_date', '<', now());
            $allocatedOrdersQuery->where('due_date', '<', now());
            $inProcessOrdersQuery->where('due_date', '<', now());
            $forApprovalOrdersQuery->where('due_date', '<', now());
        }
        
        // Apply sorting from dropdown
        switch($sort) {
            case 'po_asc':
                $createdOrdersQuery->orderBy('purchase_order_code', 'asc');
                $allocatedOrdersQuery->orderBy('purchase_order_code', 'asc');
                $inProcessOrdersQuery->orderBy('purchase_order_code', 'asc');
                $forApprovalOrdersQuery->orderBy('purchase_order_code', 'asc');
                $completedOrdersQuery->orderBy('purchase_order_code', 'asc');
                $rejectedOrdersQuery->orderBy('purchase_order_code', 'asc');
                break;
            case 'po_desc':
                $createdOrdersQuery->orderBy('purchase_order_code', 'desc');
                $allocatedOrdersQuery->orderBy('purchase_order_code', 'desc');
                $inProcessOrdersQuery->orderBy('purchase_order_code', 'desc');
                $forApprovalOrdersQuery->orderBy('purchase_order_code', 'desc');
                $completedOrdersQuery->orderBy('purchase_order_code', 'desc');
                $rejectedOrdersQuery->orderBy('purchase_order_code', 'desc');
                break;
            case 'due_date_asc':
                $createdOrdersQuery->orderBy('due_date', 'asc');
                $allocatedOrdersQuery->orderBy('due_date', 'asc');
                $inProcessOrdersQuery->orderBy('due_date', 'asc');
                $forApprovalOrdersQuery->orderBy('due_date', 'asc');
                $completedOrdersQuery->orderBy('due_date', 'asc');
                $rejectedOrdersQuery->orderBy('due_date', 'asc');
                break;
            case 'due_date_desc':
                $createdOrdersQuery->orderBy('due_date', 'desc');
                $allocatedOrdersQuery->orderBy('due_date', 'desc');
                $inProcessOrdersQuery->orderBy('due_date', 'desc');
                $forApprovalOrdersQuery->orderBy('due_date', 'desc');
                $completedOrdersQuery->orderBy('due_date', 'desc');
                $rejectedOrdersQuery->orderBy('due_date', 'desc');
                break;
            case 'weight_asc':
                // Sort by total weight (sum of all items)
                $createdOrdersQuery->orderByRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(items, "$[*].total")) AS DECIMAL) ASC');
                $allocatedOrdersQuery->orderByRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(items, "$[*].total")) AS DECIMAL) ASC');
                $inProcessOrdersQuery->orderByRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(items, "$[*].total")) AS DECIMAL) ASC');
                $forApprovalOrdersQuery->orderByRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(items, "$[*].total")) AS DECIMAL) ASC');
                $completedOrdersQuery->orderByRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(items, "$[*].total")) AS DECIMAL) ASC');
                $rejectedOrdersQuery->orderByRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(items, "$[*].total")) AS DECIMAL) ASC');
                break;
            case 'weight_desc':
                $createdOrdersQuery->orderByRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(items, "$[*].total")) AS DECIMAL) DESC');
                $allocatedOrdersQuery->orderByRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(items, "$[*].total")) AS DECIMAL) DESC');
                $inProcessOrdersQuery->orderByRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(items, "$[*].total")) AS DECIMAL) DESC');
                $forApprovalOrdersQuery->orderByRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(items, "$[*].total")) AS DECIMAL) DESC');
                $completedOrdersQuery->orderByRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(items, "$[*].total")) AS DECIMAL) DESC');
                $rejectedOrdersQuery->orderByRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(items, "$[*].total")) AS DECIMAL) DESC');
                break;
            case 'latest':
            default:
                $createdOrdersQuery->latest();
                $allocatedOrdersQuery->latest();
                $inProcessOrdersQuery->latest();
                $forApprovalOrdersQuery->latest();
                $completedOrdersQuery->latest();
                $rejectedOrdersQuery->latest();
                break;
        }
        
        $createdOrders = $createdOrdersQuery->get();
        $allocatedOrders = $allocatedOrdersQuery->get();
        $inProcessOrders = $inProcessOrdersQuery->get();
        $forApprovalOrders = $forApprovalOrdersQuery->get();
        $completedOrders = $completedOrdersQuery->get();
        $rejectedOrders = $rejectedOrdersQuery->get();
        
        $craftsmen = Craftman::all();
        
        return view('admin.purchase-order.index', compact(
            'createdOrders', 'allocatedOrders', 'inProcessOrders', 
            'forApprovalOrders', 'completedOrders', 'rejectedOrders', 'craftsmen',
            'search', 'filterPoCode', 'filterCraftsman', 'filterStatus', 'filterDateFrom', 'filterDateTo', 'sort'
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
        return view('admin.purchase-order.create', compact('categories', 'products', 'designs'));
    }

    public function store(Request $request)
{
    // Log the data to see if multiple products are arriving
    Log::info('Incoming PO Data: ' . json_encode($request->all()));

    $request->validate([
        'due_date' => 'nullable|date',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'nullable|exists:products,id',
        'items.*.category' => 'required',
        'items.*.grams' => 'required|array',
        'items.*.quantity' => 'required|array',
    ]);

    $purchaseOrderCode = PurchaseOrder::generatePurchaseOrderCode();
    $processedItems = [];

    foreach ($request->items as $index => $item) {
        $totalWeight = 0;
        
        // Calculation for this specific item
        if (isset($item['grams'])) {
            foreach ($item['grams'] as $gIndex => $gram) {
                $qty = $item['quantity'][$gIndex] ?? 0;
                $totalWeight += (floatval($gram) * intval($qty));
            }
        }

        // Handle Image for multiple products
        $imagePath = null;
        if ($request->hasFile("items.{$index}.image")) {
            $image = $request->file("items.{$index}.image");
            $imageName = time() . "_{$index}_" . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/purchase-orders'), $imageName);
            $imagePath = 'images/purchase-orders/' . $imageName;
        }

        $processedItems[] = [
            'product_id'   => $item['product_id'],
            'category'     => $item['category'],
            'subcategory'  => $item['subcategory'] ?? null,
            'item_notes'   => $item['item_notes'] ?? '',
            'item_size'    => $item['item_size'] ?? '',
            'grams'        => $item['grams'],
            'quantity'     => $item['quantity'],
            'total'        => number_format($totalWeight, 2, '.', ''),
            'image'        => $imagePath,
        ];
    }

    PurchaseOrder::create([
        'purchase_order_code' => $purchaseOrderCode,
        'due_date' => $request->due_date,
        'notes'    => $request->notes,
        'items'    => $processedItems,
        'status'   => 'created',
            'created_by' => \Auth::id(),
            'creator_type' => 'admin',
    ]);

    return redirect()->route('admin.purchase-order.index')->with('success', 'Order created with multiple products!');
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
                    'subcategory_name' => $subcategoryName,
                    // 'item_size' => $itemsize
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

        return view('admin.purchase-order.show', compact('purchaseOrder', 'itemsWithDetails', 'rejectedItemsWithDetails'));
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
                $design = Design::where('product_id', $product?->id)->first();
                
                // Match category name to ID if needed for dropdown pre-selection
                $categoryId = is_numeric($item['category'] ?? null) ? (int)$item['category'] : ($product->product_category_id ?? null);
                if (!$categoryId && !empty($categoryName) && $categoryName !== 'N/A') {
                    $catMatch = \App\Models\ProductCategory::where('name', $categoryName)->first();
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
                    'subcategory' => is_numeric($item['subcategory'] ?? null) ? (int)$item['subcategory'] : ($product->product_subcategory_id ?? null),
                ]);
            }
        }

        return view('admin.purchase-order.edit', compact('purchaseOrder', 'categories', 'products', 'itemsWithDetails', 'designs'));
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

        return redirect()->route('admin.purchase-order.index', ['tab' => $tab])->with('success', 'Order updated successfully.');
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
        return view('admin.purchase-order.print', compact('purchaseOrder', 'itemsWithDetails'));
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
        return redirect()->route('admin.purchase-order.index')->with('success', 'Deleted.');
    }

    /**
     * Single Order Allocation
     */
    public function allocate(PurchaseOrder $purchaseOrder)
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
        $craftsmen = Craftman::all();
        return view('admin.purchase-order.allocate', compact('purchaseOrder', 'itemsWithDetails', 'craftsmen'));
    }

    public function allocateStore(Request $request, PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update([
            'allocated_craftsman_code' => $request->allocated_craftsman_code,
            'craftsman_status' => 'allocated',
            'allocated_by' => \Auth::id(),
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

        return redirect()->route('admin.purchase-order.index')->with('success', 'Allocated.');
    }

    /**
     * Bulk Allocation for multiple orders
     */
    public function bulkAllocate(Request $request)
    {
        PurchaseOrder::whereIn('id', $request->order_ids)->update([
            'allocated_craftsman_code' => $request->craftsman_code,
            'craftsman_status' => 'allocated',
            'allocated_by' => \Auth::id(),
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
                'craftsman_status' => 'completed', 'approved_by' => \Auth::id()]);

        // Send Notification (Optional if requester can be resolved, otherwise notify admins)
        try {
            // PurchaseOrder doesn't have creator_type/creator_user_code yet, 
            // but we can notify admins that it's completed (if needed)
            // Or if it's for a buyer, resolution is needed.
            // For now, we follow the WorkOrder pattern if possible.
            $adminUsers = \App\Models\ProcessOwner::where('role', 'super_admin')->get();
            foreach ($adminUsers as $admin) {
                if ($admin->fcm_token) {
                    $admin->notify(new \App\Notifications\PurchaseOrderCompleted($purchaseOrder));
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send PO completion notification: ' . $e->getMessage());
        }

        return redirect()->route('admin.purchase-order.index', ['tab' => 'completed'])->with('success', 'Approved.');
    }

    /**
     * Reallocate Logic for Rejected Orders
     */
    public function reallocate(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update([
            'status' => 'created',
            'craftsman_status' => null,
            'allocated_craftsman_code' => null,
            'rejected_items' => null 
        ]);

        return redirect()->route('admin.purchase-order.index', ['tab' => 'created'])
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
            ->map(function($product) {
                // Prioritize Design Image -> Product Image -> Default
                $imageUrl = '';
                $designCode = '';
                
                // Check for associated design
                $design = $product->designs->first();
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
     * Export purchase orders to CSV based on filters
     */
    public function export(Request $request)
    {
        $tab = $request->get('tab', 'created');
        $search = $request->get('search');
        $filterPoCode = $request->get('filter_po_code');
        $filterCraftsman = $request->get('filter_craftsman');
        $filterDesignCode = $request->get('filter_design_code');
        $filterStatus = $request->get('filter_status');
        $filterDateFrom = $request->get('filter_date_from');
        $filterDateTo = $request->get('filter_date_to');
        $sort = $request->get('sort', 'latest');
        
        // Get the appropriate query based on tab
        switch ($tab) {
            case 'created':
                $query = PurchaseOrder::where('status', 'created')->whereNull('allocated_craftsman_code');
                $filename = 'created_purchase_orders';
                break;
            case 'allocated':
                $query = PurchaseOrder::where('craftsman_status', 'allocated')->where('status', 'created');
                $filename = 'allocated_purchase_orders';
                break;
            case 'in_process':
                $query = PurchaseOrder::where('status', 'in_process');
                $filename = 'in_process_purchase_orders';
                break;
            case 'for_approval':
                $query = PurchaseOrder::where('status', 'for_approval');
                $filename = 'purchase_orders_for_approval';
                break;
            case 'completed':
                $query = PurchaseOrder::where('status', 'completed');
                $filename = 'completed_purchase_orders';
                break;
            case 'rejected':
                $query = PurchaseOrder::where(function($q) {
                    $q->where('craftsman_status', 'rejected')
                      ->orWhereRaw('JSON_LENGTH(rejected_items) > 0');
                });
                $filename = 'rejected_purchase_orders';
                break;
            default:
                $query = PurchaseOrder::where('status', 'created')->whereNull('allocated_craftsman_code');
                $filename = 'purchase_orders';
        }
        
        // Apply search filter
        if ($search) {
            $searchTerm = '%' . $search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('purchase_order_code', 'LIKE', $searchTerm)
                  ->orWhere('notes', 'LIKE', $searchTerm)
                  ->orWhereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", [$searchTerm]);
            });
        }
        
        // Apply filters
        if ($filterPoCode) {
            $filterTerm = '%' . $filterPoCode . '%';
            $query->where('purchase_order_code', 'LIKE', $filterTerm);
        }
        
        if ($filterCraftsman) {
            $filterTerm = '%' . $filterCraftsman . '%';
            $query->where('allocated_craftsman_code', 'LIKE', $filterTerm);
        }

        if ($filterDesignCode) {
            $filterTerm = '%' . $filterDesignCode . '%';
            $query->whereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", [$filterTerm]);
        }
        
        if ($filterStatus) {
            // Apply status filter based on current tab context
            switch($filterStatus) {
                case 'created':
                    // Only affects created orders tab
                    break;
                case 'allocated':
                    // Only affects allocated orders tab
                    break;
                case 'in_process':
                    // Only affects in-process orders tab
                    break;
                case 'for_approval':
                    // Only affects for-approval orders tab
                    break;
                case 'completed':
                    // Only affects completed orders tab
                    break;
                case 'rejected':
                    // Only affects rejected orders tab
                    break;
            }
        }
        
        if ($filterDateFrom) {
            $query->where('due_date', '>=', $filterDateFrom);
        }
        
        if ($filterDateTo) {
            $query->where('due_date', '<=', $filterDateTo);
        }
        
        // Apply sorting
        switch($sort) {
            case 'po_asc':
                $query->orderBy('purchase_order_code', 'asc');
                break;
            case 'po_desc':
                $query->orderBy('purchase_order_code', 'desc');
                break;
            case 'due_date_asc':
                $query->orderBy('due_date', 'asc');
                break;
            case 'due_date_desc':
                $query->orderBy('due_date', 'desc');
                break;
            case 'weight_asc':
                $query->orderByRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(items, "$[*].total")) AS DECIMAL) ASC');
                break;
            case 'weight_desc':
                $query->orderByRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(items, "$[*].total")) AS DECIMAL) DESC');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }
        
        // Get all results
        $purchaseOrders = $query->get();
        
        // Format data for export
        $exportData = $purchaseOrders->map(function ($po) {
            $totalWeight = 0;
            $itemsCount = 0;
            
            if ($po->items && is_array($po->items)) {
                foreach ($po->items as $item) {
                    if (isset($item['total'])) {
                        $totalWeight += floatval($item['total']);
                    }
                    $itemsCount++;
                }
            }
            
            return [
                'ID' => $po->id,
                'PO Code' => $po->purchase_order_code,
                'Due Date' => $po->due_date ? $po->due_date->format('Y-m-d') : 'N/A',
                'Items Count' => $itemsCount,
                'Total Weight (g)' => number_format($totalWeight, 2),
                'Status' => $po->status,
                'Craftsman Status' => $po->craftsman_status ?? 'N/A',
                'Allocated Craftsman' => $po->allocated_craftsman_code ?? 'N/A',
                'Notes' => $po->notes ?? 'N/A',
                'Created At' => $po->created_at->format('Y-m-d H:i:s'),
                'Updated At' => $po->updated_at->format('Y-m-d H:i:s'),
            ];
        });
        
        // Create CSV content
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];
        
        $callback = function() use ($exportData) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            if ($exportData->isNotEmpty()) {
                fputcsv($file, array_keys($exportData->first()));
            }
            
            // Add data rows
            foreach ($exportData as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
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
                'approved_by' => \Auth::id(),
            ]);

        // Send Bulk Notification
        try {
            $adminUsers = \App\Models\ProcessOwner::where('role', 'super_admin')->get();
            foreach ($adminUsers as $admin) {
                if ($admin->fcm_token) {
                    $count = count($orderIds);
                    // Use a simple notification for now
                    $admin->notify(new \App\Notifications\PurchaseOrderCompleted(PurchaseOrder::find($orderIds[0])));
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send bulk PO completion notification: ' . $e->getMessage());
        }

        return redirect()->route('admin.purchase-order.index', ['tab' => 'completed'])
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
                'approved_by' => \Auth::id(),
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

        return redirect()->route('admin.purchase-order.index', ['tab' => 'completed'])
            ->with('success', count($orderIds) . ' Purchase Orders marked as completed successfully!');
    }

    public function bulkPrint(Request $request)
    {
        $orderIds = $request->input('order_ids', []);
        $printDate = $request->input('print_date');
        
        if (empty($orderIds) && empty($printDate)) {
            return redirect()->back()->with('error', 'Please select at least one order to print.');
        }

        if ($printDate) {
            $orders = PurchaseOrder::whereDate('created_at', $printDate)->get();
        } else {
            $orders = PurchaseOrder::whereIn('id', $orderIds)->get();
        }

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
                $craftsman = Craftman::where('craftman_code', $po->allocated_craftsman_code)->first();
            }

            $ordersWithDetails[] = [
                'order'     => $po,
                'items'     => $enrichedItems,
                'craftsman' => $craftsman,
            ];
        }

        // CompanyContact stores contact info in the 'data' column (cast to array)
        $companyDetails = \App\Models\CompanyContact::where('is_active', 1)->get();
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
        return view('admin.purchase-order.bulk-print', compact('ordersWithDetails', 'company'));
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
        
        return redirect()->route('admin.purchase-order.edit', $newPurchaseOrder)
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
            return redirect()->route('admin.purchase-order.index', ['tab' => 'completed'])
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

            return redirect()->route('admin.purchase-order.show', $purchaseOrder)
                ->with('success', count($completedItems) . ' items completed and sent for approval. Remaining items are still in process.');
        }
    }
}
