<?php

namespace App\Http\Controllers\CraftsmanStaff;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PurchaseOrderController extends Controller
{
    // Define the number of items per page
    private const PER_PAGE = 10;
    
    /**
     * Bulk accept purchase orders.
     */
    public function bulkAccept(Request $request)
    {
        if ($staff = $this->currentStaff()) {
            if (!$staff->hasPermission('po_accept')) abort(403, 'Unauthorized action.');
        }
        $craftsman = $this->currentCraftsman();
        $purchaseOrderIds = $request->input('purchase_order_ids', []);

        if (empty($purchaseOrderIds)) {
            return redirect()->back()->with('error', 'No purchase orders selected for bulk acceptance.');
        }

        $count = 0;
        foreach ($purchaseOrderIds as $id) {
            $purchaseOrder = PurchaseOrder::find($id);

            // Security check: Ensure purchase order exists and belongs to this craftsman
            if ($purchaseOrder && $purchaseOrder->allocated_craftsman_code === $craftsman->craftman_code && $purchaseOrder->craftsman_status === 'allocated') {
                $staffId = null;
                $staffAcceptedAt = null;
                if ($this->currentStaff()) {
                    $staffId = $this->currentStaff()->id;
                    $staffAcceptedAt = now();
                }

                $purchaseOrder->update([
                    'craftsman_status' => 'in_process',
                    'craftsman_accepted_at' => now(),
                    'accepted_by_staff_id' => $staffId,
                    'craftsman_staff_id' => $staffId,
                    'staff_accepted_at' => $staffAcceptedAt,
                    'status' => 'in_process'
                ]);
                $count++;
            }
        }

        return redirect()->route('craftsman_staff.purchase-order.index', ['tab' => 'in-process'])
            ->with('success', $count . ' purchase orders accepted and moved to in process!');
    }

    /**
     * Bulk reject purchase orders.
     */
    public function bulkReject(Request $request)
    {
        if ($staff = $this->currentStaff()) {
            if (!$staff->hasPermission('po_reject')) abort(403, 'Unauthorized action.');
        }
        $craftsman = $this->currentCraftsman();
        $purchaseOrderIds = $request->input('purchase_order_ids', []);

        if (empty($purchaseOrderIds)) {
            return redirect()->back()->with('error', 'No purchase orders selected for bulk rejection.');
        }

        $count = 0;
        foreach ($purchaseOrderIds as $id) {
            $purchaseOrder = PurchaseOrder::find($id);

            // Security check: Ensure purchase order exists and belongs to this craftsman
            if ($purchaseOrder && $purchaseOrder->allocated_craftsman_code === $craftsman->craftman_code && $purchaseOrder->craftsman_status === 'allocated') {
                $purchaseOrder->update([
                    'craftsman_status' => 'rejected',
                    'rejected_items' => $purchaseOrder->items, // Assume all items rejected
                    'items' => [] // Clear items for craftsman
                ]);
                $count++;
            }
        }

        return redirect()->route('craftsman_staff.purchase-order.index', ['tab' => 'rejected'])
            ->with('success', $count . ' purchase orders rejected!');
    }

    /**
     * Bulk complete purchase orders.
     */
    public function bulkComplete(Request $request)
    {
        if ($staff = $this->currentStaff()) {
            if (!$staff->hasPermission('po_accept')) abort(403, 'Unauthorized action.');
        }
        $craftsman = $this->currentCraftsman();
        $purchaseOrderIds = $request->input('purchase_order_ids', []);

        if (empty($purchaseOrderIds)) {
            return redirect()->back()->with('error', 'No purchase orders selected for bulk completion.');
        }

        $count = 0;
        foreach ($purchaseOrderIds as $id) {
            $purchaseOrder = PurchaseOrder::find($id);

            // Security check: Ensure purchase order exists and belongs to this craftsman
            if ($purchaseOrder && $purchaseOrder->allocated_craftsman_code === $craftsman->craftman_code && $purchaseOrder->craftsman_status === 'in_process') {
                $staffId = null;
                $staffCompletedAt = null;
                if ($this->currentStaff()) {
                    $staffId = $this->currentStaff()->id;
                    // If it was already accepted by a staff, preserve it, or we overwrite the completed part
                    if (!$purchaseOrder->accepted_by_staff_id) {
                        $purchaseOrder->accepted_by_staff_id = $staffId;
                    }
                    $staffCompletedAt = now();
                }

                $purchaseOrder->update([
                    'craftsman_status' => 'completed',
                    'craftsman_completed_at' => now(),
                    'staff_completed_at' => $staffCompletedAt,
                    'craftsman_staff_id' => $staffId,
                    'status' => 'for_approval'
                ]);
                $count++;
            }
        }

        return redirect()->route('craftsman_staff.purchase-order.index', ['tab' => 'completed'])
            ->with('success', $count . ' purchase orders marked as completed and sent for approval!');
    }
    
    /**
     * Display a listing of purchase orders allocated to the craftsman.
     */
    public function index(Request $request)
    {
        if ($staff = $this->currentStaff()) {
            if (!$staff->hasPermission('po_view') && !$staff->hasPermission('po_accept') && !$staff->hasPermission('po_reject')) {
                abort(403, 'Unauthorized action.');
            }
        }
        $craftsman = $this->currentCraftsman();
        
        $query = PurchaseOrder::where('allocated_craftsman_code', $craftsman->craftman_code);

        // Apply Category Filter (JSON search)
        if ($request->filled('product_category_filter')) {
            $catId = $request->product_category_filter;
            $query->where(function($q) use ($catId) {
                $q->whereRaw('JSON_CONTAINS(JSON_EXTRACT(items, "$[*].category"), ?)', [ (string)$catId ])
                  ->orWhereRaw('JSON_CONTAINS(JSON_EXTRACT(items, "$[*].category"), ?)', [ '"' . $catId . '"' ]);
            });
        }

        // Apply search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('purchase_order_code', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Get purchase orders with different statuses using cloned queries
        $allocatedOrders = (clone $query)->where('craftsman_status', 'allocated')
            ->paginate(self::PER_PAGE, ['*'], 'allocated_orders_page');
            
        $inProcessOrders = (clone $query)->where('craftsman_status', 'in_process')
            ->paginate(self::PER_PAGE, ['*'], 'in_process_orders_page');
            
        $completedOrders = (clone $query)->where('craftsman_status', 'completed')
            ->paginate(self::PER_PAGE, ['*'], 'completed_orders_page');
            
        $rejectedOrders = (clone $query)->where(function($q) {
                $q->where('craftsman_status', 'rejected')
                  ->orWhereRaw('JSON_LENGTH(rejected_items) > 0');
            })
            ->paginate(self::PER_PAGE, ['*'], 'rejected_orders_page');

        $productCategories = ProductCategory::orderBy('name')->get();

        return view('craftsman_staff.purchase-order.index', compact(
            'allocatedOrders', 
            'inProcessOrders', 
            'completedOrders', 
            'rejectedOrders',
            'productCategories'
        ));
    }

    /**
     * Export purchase orders to Excel.
     */
    public function export(Request $request)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CraftsmanPurchaseOrderExport($request), 
            'purchase-orders-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Print selected purchase orders.
     */
    public function printSelected(Request $request)
    {
        $purchaseOrderIds = $request->input('purchase_order_ids', []);
        
        if (empty($purchaseOrderIds)) {
            return redirect()->back()->with('error', 'No purchase orders selected for printing.');
        }

        $craftsman = $this->currentCraftsman();
        $purchaseOrders = PurchaseOrder::whereIn('id', $purchaseOrderIds)
            ->where('allocated_craftsman_code', $craftsman->craftman_code)
            ->get();
            
        $ordersWithDetails = [];
        foreach ($purchaseOrders as $po) {
            $itemsWithDetails = [];
            if ($po->items) {
                foreach ($po->items as $item) {
                    $productId = $item['product_id'] ?? null;
                    $product = $productId ? \App\Models\Product::with(['subcategory', 'category', 'images'])->find($productId) : null;
                    
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
                        'design' => $design,
                        'category_name' => $categoryName,
                        'subcategory_name' => $subcategoryName
                    ]);
                }
            }
            $ordersWithDetails[] = [
                'order'     => $po,
                'items'     => $itemsWithDetails,
                'craftsman' => $craftsman, // Already have the logged in craftsman
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

        return view('craftsman_staff.purchase-order.print-selected', compact('ordersWithDetails', 'company'));
    }

    /**
     * Display the specified purchase order.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $craftsman = $this->currentCraftsman();
        
        // Ensure the purchase order is allocated to this craftsman
        if ($purchaseOrder->allocated_craftsman_code !== $craftsman->craftman_code) {
            return redirect()->route('craftsman_staff.purchase-order.index')
                ->with('error', 'You are not authorized to view this purchase order.');
        }

        // Load associated products and designs
        $itemsWithDetails = [];
        if ($purchaseOrder->items) {
            foreach ($purchaseOrder->items as $item) {
                $productId = $item['product_id'] ?? null;
                $product = $productId ? \App\Models\Product::with(['subcategory', 'category', 'images'])->find($productId) : null;
                
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
                    'category_name' => $categoryName,
                    'subcategory_name' => $subcategoryName,
                    'product' => $product,
                    'design' => $design,
                    'grams' => isset($item['grams']) && is_array($item['grams']) ? json_encode($item['grams']) : ($item['grams'] ?? ''),
                    'quantity' => isset($item['quantity']) && is_array($item['quantity']) ? json_encode($item['quantity']) : ($item['quantity'] ?? ''),
                    'total' => isset($item['total']) && is_array($item['total']) ? json_encode($item['total']) : ($item['total'] ?? 0),
                ]);
            }
        }

        // Load rejected items details
        $rejectedItemsWithDetails = [];
        if ($purchaseOrder->rejected_items) {
            foreach ($purchaseOrder->rejected_items as $item) {
                $productId = $item['product_id'] ?? null;
                $product = $productId ? \App\Models\Product::with(['subcategory', 'category', 'images'])->find($productId) : null;
                
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
                    'category_name' => $categoryName,
                    'subcategory_name' => $subcategoryName,
                    'product' => $product,
                    'design' => $design,
                    'grams' => isset($item['grams']) && is_array($item['grams']) ? json_encode($item['grams']) : ($item['grams'] ?? ''),
                    'quantity' => isset($item['quantity']) && is_array($item['quantity']) ? json_encode($item['quantity']) : ($item['quantity'] ?? ''),
                    'total' => isset($item['total']) && is_array($item['total']) ? json_encode($item['total']) : ($item['total'] ?? 0),
                ]);
            }
        }

        return view('craftsman_staff.purchase-order.show', compact('purchaseOrder', 'itemsWithDetails', 'rejectedItemsWithDetails'));
    }

    /**
     * Display the print view for the specified purchase order.
     */
    public function print(PurchaseOrder $purchaseOrder)
    {
        $craftsman = $this->currentCraftsman();
        
        // Ensure the purchase order is allocated to this craftsman
        if ($purchaseOrder->allocated_craftsman_code !== $craftsman->craftman_code) {
            return redirect()->route('craftsman_staff.purchase-order.index')
                ->with('error', 'You are not authorized to print this purchase order.');
        }

        // Load associated products and designs
        $itemsWithDetails = [];
        if ($purchaseOrder->items) {
            foreach ($purchaseOrder->items as $item) {
                $productId = $item['product_id'] ?? null;
                $product = $productId ? \App\Models\Product::with(['subcategory', 'category', 'images'])->find($productId) : null;
                
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
                    'category_name' => $categoryName,
                    'subcategory_name' => $subcategoryName,
                    'product' => $product,
                    'design' => $design,
                ]);
            }
        }

        return view('craftsman_staff.purchase-order.print', compact('purchaseOrder', 'itemsWithDetails'));
    }

    public function processItems(Request $request, PurchaseOrder $purchaseOrder)
{
    $craftsman = $this->currentCraftsman();
    
    if ($purchaseOrder->allocated_craftsman_code !== $craftsman->craftman_code) {
        return redirect()->route('craftsman_staff.purchase-order.index')->with('error', 'Unauthorized.');
    }

    $action = $request->input('action');
    
    switch ($action) {
        case 'accept_all':
            $updateData = [
                'craftsman_status' => 'in_process',
                'craftsman_accepted_at' => now(),
                'status' => 'in_process' 
            ];
            if ($staff = $this->currentStaff()) {
                $updateData['craftsman_staff_id'] = $staff->id;
                $updateData['accepted_by_staff_id'] = $staff->id;
                $updateData['staff_accepted_at'] = now();
            }
            $purchaseOrder->update($updateData);
            
            return redirect()->route('craftsman_staff.purchase-order.index', ['tab' => 'in-process'])
                ->with('success', 'Order is now in process.');
            
        case 'reject_all':
            $purchaseOrder->update([
                'craftsman_status' => 'rejected',
                'rejected_items' => $purchaseOrder->items,
                // Status remains 'created' but craftsman_status shows rejected
            ]);
            return redirect()->route('craftsman_staff.purchase-order.index', ['tab' => 'rejected'])
                ->with('success', 'Items rejected.');
            
        case 'process':
            $acceptedItems = $request->input('accepted_items', []);
            $rejectedItems = $request->input('rejected_items', []);
            
            $allItems = $purchaseOrder->items;
            $acceptedItemList = [];
            $rejectedItemList = [];
            
            // Separate accepted and rejected items
            foreach ($allItems as $index => $item) {
                if (in_array($index, $acceptedItems)) {
                    $acceptedItemList[] = $item;
                }
                if (in_array($index, $rejectedItems)) {
                    $rejectedItemList[] = $item;
                }
            }
            
            // Validate that each item is either accepted OR rejected (not both)
            $conflictItems = array_intersect($acceptedItems, $rejectedItems);
            if (!empty($conflictItems)) {
                return redirect()->back()->with('error', 'Some items are marked as both accepted and rejected. Please review your selections.');
            }
            
            // Validate that at least one item is selected
            if (empty($acceptedItems) && empty($rejectedItems)) {
                return redirect()->back()->with('error', 'Please select at least one item to accept or reject.');
            }
            
            if (count($rejectedItemList) == count($allItems)) {
                // All items rejected
                $purchaseOrder->update([
                    'craftsman_status' => 'rejected', 
                    'rejected_items' => $rejectedItemList,
                    'items' => [] // Clear items since all are rejected
                ]);
                return redirect()->route('craftsman_staff.purchase-order.index', ['tab' => 'rejected'])
                    ->with('success', 'All items rejected. Order moved to rejected tab.');
            } elseif (count($acceptedItemList) == count($allItems)) {
                // All items accepted
                $updateData = [
                    'craftsman_status' => 'in_process',
                    'craftsman_accepted_at' => now(),
                    'status' => 'in_process',
                    'rejected_items' => [],
                    'items' => $acceptedItemList // Keep all items
                ];
                if ($staff = $this->currentStaff()) {
                    $updateData['craftsman_staff_id'] = $staff->id;
                    $updateData['accepted_by_staff_id'] = $staff->id;
                    $updateData['staff_accepted_at'] = now();
                }
                $purchaseOrder->update($updateData);
                return redirect()->route('craftsman_staff.purchase-order.index', ['tab' => 'in-process'])
                    ->with('success', 'All items accepted. Order moved to in-process tab.');
            } else {
                // Mixed: some accepted, some rejected
                $updateData = [
                    'craftsman_status' => 'in_process',
                    'craftsman_accepted_at' => now(),
                    'status' => 'in_process',
                    'rejected_items' => $rejectedItemList,
                    'items' => $acceptedItemList
                ];
                if ($staff = $this->currentStaff()) {
                    $updateData['craftsman_staff_id'] = $staff->id;
                    $updateData['accepted_by_staff_id'] = $staff->id;
                    $updateData['staff_accepted_at'] = now();
                }
                $purchaseOrder->update($updateData);

                return redirect()->route('craftsman_staff.purchase-order.index', ['tab' => 'in-process'])
                    ->with('success', count($acceptedItemList) . ' item(s) accepted, ' . count($rejectedItemList) . ' item(s) rejected.');
            }
            
        default:
            return redirect()->back()->with('error', 'Invalid action.');
    }
}

    /**
     * Mark specific items of a purchase order as completed.
     */
    public function completeItems(Request $request, PurchaseOrder $purchaseOrder)
    {
        $craftsman = $this->currentCraftsman();
        
        // Ensure the purchase order is allocated to this craftsman
        if ($purchaseOrder->allocated_craftsman_code !== $craftsman->craftman_code) {
            return redirect()->route('craftsman_staff.purchase-order.index')
                ->with('error', 'Unauthorized.');
        }

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
            $updateData = [
                'craftsman_status' => 'completed',
                'craftsman_completed_at' => now(),
                'status' => 'for_approval',
            ];
            if ($staff = $this->currentStaff()) {
                $updateData['craftsman_staff_id'] = $staff->id;
                $updateData['staff_completed_at'] = now();
            }
            $purchaseOrder->update($updateData);
            return redirect()->route('craftsman_staff.purchase-order.index', ['tab' => 'completed'])
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
            $newPO->craftsman_status = 'completed';
            $newPO->craftsman_completed_at = now();
            $newPO->status = 'for_approval';
            $newPO->items = $completedItems;
            if ($staff = $this->currentStaff()) {
                $newPO->craftsman_staff_id = $staff->id;
                $newPO->staff_completed_at = now();
            }
            $newPO->save();

            // 2. Update existing PO with remaining items
            $purchaseOrder->update([
                'items' => $remainingItems,
            ]);

            return redirect()->route('craftsman_staff.purchase-order.show', $purchaseOrder)
                ->with('success', count($completedItems) . ' items completed and sent for approval. Remaining items are still in process.');
        }
    }
}
