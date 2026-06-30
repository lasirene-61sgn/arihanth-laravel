<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Craftman;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockOrder;
use App\Models\StockOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Notifications\StockOrderAllocated;
use App\Notifications\StockOrderCompleted;

class StockOrderController extends Controller
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

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = StockOrder::with(['buyer', 'craftsman', 'items'])
            ->withCount('items');

        // Role-based filtering
        if (!$this->isAdmin($user)) {
            if ($this->isCraftsman($user)) {
                $query->where('craftsman_id', $user->id);
            } elseif ($this->isBuyerSide($user)) {
                $query->where('buyer_id', $user->id);
            }
        }

        // Calculate counts for tabs
        $countsQuery = clone $query;
        $counts = [
            'new-orders' => (clone $countsQuery)->where('status', 'Pending')->count(),
            'allocated-orders' => (clone $countsQuery)->where('status', 'Allocated')
                ->whereDoesntHave('items', function($q) {
                    $q->whereIn('status', ['Accepted', 'Ready for Approval', 'Completed', 'Rejected']);
                })->count(),
            'in-process-orders' => (clone $countsQuery)->where('status', 'Allocated')
                ->whereHas('items', function($q) {
                    $q->where('status', 'Accepted');
                })
                ->whereDoesntHave('items', function($q) {
                    $q->whereIn('status', ['Ready for Approval', 'Completed']);
                })->count(),
            'for-approval-orders' => (clone $countsQuery)->where('status', 'Allocated')
                ->whereHas('items', function($q) {
                    $q->where('status', 'Ready for Approval');
                })->count(),
            'completed-orders' => (clone $countsQuery)->where('status', 'Completed')->count(),
            'rejected-orders' => (clone $countsQuery)->where('status', 'Allocated')
                ->whereHas('items', function($q) {
                    $q->where('status', 'Rejected');
                })->count(),
            'all-orders' => (clone $countsQuery)->count(),
        ];

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('buyer', function($bq) use ($search) {
                      $bq->where('business_name', 'like', "%{$search}%")
                        ->orWhere('bp_code', 'like', "%{$search}%");
                  });
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Tab Filter
        if ($request->filled('tab')) {
            $tab = $request->tab;
            if ($tab === 'new-orders') {
                $query->where('status', 'Pending');
            } elseif ($tab === 'allocated-orders') {
                $query->where('status', 'Allocated')
                      ->whereDoesntHave('items', function($q) {
                          $q->whereIn('status', ['Accepted', 'Ready for Approval', 'Completed', 'Rejected']);
                      });
            } elseif ($tab === 'in-process-orders') {
                $query->where('status', 'Allocated')
                      ->whereHas('items', function($q) {
                          $q->where('status', 'Accepted');
                      })
                      ->whereDoesntHave('items', function($q) {
                          $q->whereIn('status', ['Ready for Approval', 'Completed']);
                      });
            } elseif ($tab === 'for-approval-orders') {
                $query->where('status', 'Allocated')
                      ->whereHas('items', function($q) {
                          $q->where('status', 'Ready for Approval');
                      });
            } elseif ($tab === 'completed-orders') {
                $query->where('status', 'Completed');
            } elseif ($tab === 'rejected-orders') {
                $query->where('status', 'Allocated')
                      ->whereHas('items', function($q) {
                          $q->where('status', 'Rejected');
                      });
            }
        }

        // Date Filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate($request->get('per_page', 15));

        // Flatten to include first item details at top level and remove items array
        $orders->getCollection()->transform(function ($order) {
            $firstItem = $order->items->first();
            if ($firstItem) {
                $order->item_id     = $firstItem->id;
                $order->product_id  = $firstItem->product_id;
                $order->design_code = $firstItem->design_code;
                $order->weight_from = $firstItem->weight_from;
                $order->weight_to   = $firstItem->weight_to;
                $order->size        = $firstItem->size;
                $order->grams       = $firstItem->grams;
                $order->quantity    = $firstItem->quantity;
                $order->item_status = $firstItem->status;
                $order->image_url   = $firstItem->image_path 
                    ? (str_starts_with($firstItem->image_path, 'http') ? $firstItem->image_path : asset('storage/' . $firstItem->image_path))
                    : null;
            }
            unset($order->items);

            // Add dynamic status colors for Admin & Craftsman roles
            $colorData = $this->calculateStockOrderColors($order);
            $order->setAttribute('color_key', $colorData['color_key']);
            $order->setAttribute('color_hex', $colorData['color_hex']);

            return $order;
        });

        return response()->json([
            'success' => true,
            'data' => $orders,
            'counts' => $counts
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'buyer_id' => $this->isAdmin($user) ? 'required|exists:buyers,id' : 'nullable',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.design_code' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.grams' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $buyerId = $this->isAdmin($user) ? $request->buyer_id : $user->id;

        try {
            DB::beginTransaction();

            $order = StockOrder::create([
                'buyer_id' => $buyerId,
                'notes' => $request->notes,
                'status' => 'Pending',
                'total_items' => count($request->items),
            ]);

            foreach ($request->items as $itemData) {
                $order->items()->create([
                    'product_id' => $itemData['product_id'] ?? $itemData['id'] ?? null,
                    'design_code' => $itemData['design_code'],
                    'category_name' => $itemData['category_name'] ?? $itemData['category'] ?? null,
                    'subcategory_name' => $itemData['subcategory_name'] ?? $itemData['subcategory'] ?? null,
                    'weight_from' => $itemData['weight_from'] ?? null,
                    'weight_to' => $itemData['weight_to'] ?? null,
                    'size' => $itemData['size'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'grams' => $itemData['grams'] ?? null,
                    'item_notes' => $itemData['item_notes'] ?? null,
                    'image_path' => $itemData['image_path'] ?? $itemData['image_raw'] ?? null,
                    'status' => 'Pending',
                ]);
            }

            DB::commit();

            $responseData = $order->load('items')->toArray();
            $colorData = $this->calculateStockOrderColors($order);
            $responseData['color_key'] = $colorData['color_key'];
            $responseData['color_hex'] = $colorData['color_hex'];

            return response()->json([
                'success' => true,
                'message' => 'Stock order created successfully!',
                'data' => $responseData
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to create stock order: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $user = Auth::user();
        $stockOrder = StockOrder::with(['buyer', 'items', 'craftsman'])->find($id);

        if (!$stockOrder) {
            return response()->json(['success' => false, 'message' => 'Stock order not found'], 404);
        }

        // Role-based authorization
        if (!$this->isAdmin($user)) {
            if ($this->isCraftsman($user) && $stockOrder->craftsman_id != $user->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            if ($this->isBuyerSide($user) && $stockOrder->buyer_id != $user->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        }

        // Flatten item details to top level
        $firstItem = $stockOrder->items->first();
        if ($firstItem) {
            $stockOrder->item_id     = $firstItem->id;
            $stockOrder->product_id  = $firstItem->product_id;
            $stockOrder->design_code = $firstItem->design_code;
            $stockOrder->weight_from = $firstItem->weight_from;
            $stockOrder->weight_to   = $firstItem->weight_to;
            $stockOrder->size        = $firstItem->size;
            $stockOrder->grams       = $firstItem->grams;
            $stockOrder->quantity    = $firstItem->quantity;
            $stockOrder->item_status = $firstItem->status;
            $stockOrder->image_url   = $firstItem->image_path 
                ? (str_starts_with($firstItem->image_path, 'http') ? $firstItem->image_path : asset('storage/' . $firstItem->image_path))
                : null;
        }
        
        // Remove items array as requested
        $stockOrder->unsetRelation('items');

        // Add dynamic status colors for Admin & Craftsman roles
        $colorData = $this->calculateStockOrderColors($stockOrder);
        $stockOrder->setAttribute('color_key', $colorData['color_key']);
        $stockOrder->setAttribute('color_hex', $colorData['color_hex']);

        return response()->json([
            'success' => true,
            'data' => $stockOrder
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $stockOrder = StockOrder::find($id);

        if (!$stockOrder) {
            return response()->json(['success' => false, 'message' => 'Stock order not found'], 404);
        }

        // Role-based authorization
        if (!$this->isAdmin($user) && $stockOrder->buyer_id != $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'buyer_id' => $this->isAdmin($user) ? 'required|exists:buyers,id' : 'nullable',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.design_code' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.grams' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $stockOrder->update([
                'buyer_id' => $this->isAdmin($user) ? $request->buyer_id : $stockOrder->buyer_id,
                'notes' => $request->notes,
                'total_items' => count($request->items),
            ]);

            // Simple approach: delete old items and create new ones
            $stockOrder->items()->delete();

            foreach ($request->items as $itemData) {
                $stockOrder->items()->create([
                    'product_id' => $itemData['product_id'] ?? $itemData['id'] ?? null,
                    'design_code' => $itemData['design_code'],
                    'category_name' => $itemData['category_name'] ?? $itemData['category'] ?? null,
                    'subcategory_name' => $itemData['subcategory_name'] ?? $itemData['subcategory'] ?? null,
                    'weight_from' => $itemData['weight_from'] ?? null,
                    'weight_to' => $itemData['weight_to'] ?? null,
                    'size' => $itemData['size'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'grams' => $itemData['grams'] ?? null,
                    'item_notes' => $itemData['item_notes'] ?? null,
                    'image_path' => $itemData['image_path'] ?? $itemData['image_raw'] ?? null,
                    'status' => 'Pending',
                ]);
            }

            DB::commit();

            $responseData = $stockOrder->load('items')->toArray();
            $colorData = $this->calculateStockOrderColors($stockOrder);
            $responseData['color_key'] = $colorData['color_key'];
            $responseData['color_hex'] = $colorData['color_hex'];

            return response()->json([
                'success' => true,
                'message' => 'Stock order updated successfully!',
                'data' => $responseData
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to update stock order: ' . $e->getMessage()], 500);
        }
    }

    public function allocate(Request $request, $id)
    {
        $user = Auth::user();
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $stockOrder = StockOrder::find($id);
        if (!$stockOrder) {
            return response()->json(['success' => false, 'message' => 'Stock order not found'], 404);
        }

        $code = $request->input('craftsman_code', $request->input('craftman_code'));
        $request->merge(['craftman_code' => $code]);

        $validator = Validator::make($request->all(), [
            'craftman_code' => 'required|exists:craftmen,craftman_code',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'The craftsman code is required and must be valid.'], 422);
        }

        $craftsman = \App\Models\Craftman::where('craftman_code', $code)->first();

        try {
            DB::beginTransaction();

            $stockOrder->update([
                'craftsman_id' => $craftsman->id,
                'status' => 'Allocated'
            ]);

            // Also update all items to this craftsman
            $stockOrder->items()->update([
                'craftsman_id' => $craftsman->id,
                'status' => 'Pending'
            ]);

            DB::commit();
            $this->notifyCraftsman($stockOrder, $craftsman);

            return response()->json([
                'success' => true,
                'message' => 'Stock order allocated successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to allocate stock order: ' . $e->getMessage()], 500);
        }
    }
    
    private function notifyCraftsman(StockOrder $stockOrder, $craftsman)
    {
        if ($craftsman && method_exists($craftsman, 'notify')) {
            $stockOrder->craftsman->notify(new StockOrderAllocated($stockOrder));
        }
    }
    
    private function notifyBuyer(StockOrder $stockOrder)
    {
        if ($stockOrder->buyer && method_exists($stockOrder->buyer, 'notify')) {
            $stockOrder->buyer->notify(new StockOrderCompleted($stockOrder));
        }
    }
    
    public function reallocate(Request $request, $id)
    {
        $user = Auth::user();
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $stockOrder = StockOrder::find($id);
        if (!$stockOrder) {
            return response()->json(['success' => false, 'message' => 'Stock order not found'], 404);
        }

        $code = $request->input('craftsman_code', $request->input('craftman_code'));
        $request->merge(['craftman_code' => $code]);

        $validator = Validator::make($request->all(), [
            'craftman_code' => 'required|exists:craftmen,craftman_code',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'The craftsman code is required and must be valid.'], 422);
        }
        
        $craftsman = \App\Models\Craftman::where('craftman_code', $code)->first();

        try {
            DB::beginTransaction();

            // Store old craftsman for logging if needed
            $oldCraftsmanId = $stockOrder->craftsman_id;

            $stockOrder->update([
                'craftsman_id' => $craftsman->id,
                'status' => 'Allocated' // Reset status to allocated
            ]);

            // Update items - move any non-completed items to new craftsman
            $stockOrder->items()->where('status', '!=', 'Completed')->update([
                'craftsman_id' => $craftsman->id,
                'status' => 'Pending'
            ]);

            DB::commit();
            $this->notifyCraftsman($stockOrder, $craftsman);

            return response()->json([
                'success' => true,
                'message' => 'Stock order reallocated successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to reallocate: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $stockOrder = StockOrder::find($id);

        if (!$stockOrder) {
            return response()->json(['success' => false, 'message' => 'Stock order not found'], 404);
        }

        // Authorization: Admin can change to any status, Craftsman/Buyer have limited options or no access
        if (!$this->isAdmin($user)) {
            // For now, only Admin can change overall order status
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Pending,Allocated,Completed,Cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $stockOrder->update(['status' => $request->status]);

        // Notify Buyer on Completion
        if ($request->status === 'Completed') {
            $this->notifyBuyer($stockOrder);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully!'
        ]);
    }

    public function updateItemStatus(Request $request, $id, $itemId)
    {
        $user = Auth::user();
        $stockOrder = StockOrder::find($id);

        if (!$stockOrder) {
            return response()->json(['success' => false, 'message' => 'Stock order not found'], 404);
        }

        $item = $stockOrder->items()->find($itemId);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        // Authorization
        if (!$this->isAdmin($user)) {
            if ($this->isCraftsman($user) && $item->craftsman_id != $user->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            if ($this->isBuyerSide($user)) {
                 return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Pending,Accepted,Rejected,Ready for Approval,Completed',
            'rejection_reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Restrict 'Completed' to Admin only
        if ($request->status === 'Completed' && !$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Only admins can mark items as Completed.'], 403);
        }

        $item->update([
            'status' => $request->status,
            'rejection_reason' => $request->status === 'Rejected' ? $request->rejection_reason : null
        ]);

        // Automatically update order status if all items are completed
        if ($stockOrder->items()->where('status', '!=', 'Completed')->count() === 0) {
            $stockOrder->update(['status' => 'Completed']);
            
            // Notify Buyer
            $this->notifyBuyer($stockOrder);
        }

        return response()->json([
            'success' => true,
            'message' => "Item status updated to {$request->status}!"
        ]);
    }

    public function acceptItem(Request $request, $id, $itemId)
    {
        $request->merge(['status' => 'Accepted']);
        return $this->updateItemStatus($request, $id, $itemId);
    }

    public function rejectItem(Request $request, $id, $itemId)
    {
        $request->validate(['rejection_reason' => 'required|string']);
        $request->merge(['status' => 'Rejected']);
        return $this->updateItemStatus($request, $id, $itemId);
    }

    public function finishItem(Request $request, $id, $itemId)
    {
        $request->merge(['status' => 'Ready for Approval']);
        return $this->updateItemStatus($request, $id, $itemId);
    }

    public function completeItem(Request $request, $id, $itemId)
    {
        $request->merge(['status' => 'Completed']);
        return $this->updateItemStatus($request, $id, $itemId);
    }

    public function lookup($code)
    {
        $product = Product::with(['category', 'subcategory', 'images'])
            ->where('design_code', $code)
            ->where('design_status', 'Accepted')
            ->first();

        // Fallback: If not found by design_code, check if the code is a numeric ID
        if (!$product && is_numeric($code)) {
            $product = Product::with(['category', 'subcategory', 'images'])
                ->where('id', $code)
                ->where('design_status', 'Accepted')
                ->first();
        }

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Design not found or not accepted.']);
        }

        // Handle image URL
        $firstImage = null;
        if ($product->images->count()) {
            $imagePath = $product->images->first()->path;
            if (str_starts_with($imagePath, 'http')) {
                $firstImage = $imagePath;
            } else {
                $firstImage = asset('storage/' . $imagePath);
            }
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'design_code' => $product->design_code,
                'product_name' => $product->product_name,
                'category' => $product->category->name ?? 'N/A',
                'subcategory' => $product->subcategory->name ?? 'N/A',
                'weight_from' => $product->weight_from,
                'weight_to' => $product->weight_to,
                'size' => $product->size ?? 'N/A',
                'image' => $firstImage,
                'image_raw' => $product->images->first()->path ?? null,
            ]
        ]);
    }

    public function bulkAllocate(Request $request)
    {
        $user = Auth::user();
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $code = $request->input('craftsman_code', $request->input('craftman_code'));
        $request->merge(['craftman_code' => $code]);

        $validator = Validator::make($request->all(), [
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:stock_orders,id',
            'craftman_code' => 'required|exists:craftmen,craftman_code',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'The craftsman code is required and must be valid.'], 422);
        }

        $craftsman = \App\Models\Craftman::where('craftman_code', $code)->first();

        try {
            DB::beginTransaction();

            StockOrder::whereIn('id', $request->order_ids)->update([
                'craftsman_id' => $craftsman->id,
                'status' => 'Allocated'
            ]);

            StockOrderItem::whereIn('stock_order_id', $request->order_ids)->update([
                'craftsman_id' => $craftsman->id,
                'status' => 'Pending'
            ]);

            DB::commit();
            foreach ($request->order_ids as $orderId) {
                $order = StockOrder::find($orderId);
                if ($order) $this->notifyCraftsman($order, $craftsman);
            }

            return response()->json([
                'success' => true,
                'message' => count($request->order_ids) . ' orders allocated successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to bulk allocate: ' . $e->getMessage()], 500);
        }
    }

    public function bulkComplete(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:stock_orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            if ($this->isAdmin($user)) {
                // Admin completes the order
                StockOrder::whereIn('id', $request->order_ids)->update([
                    'status' => 'Completed'
                ]);

                StockOrderItem::whereIn('stock_order_id', $request->order_ids)->update([
                    'status' => 'Completed'
                ]);
                
                $message = count($request->order_ids) . ' orders completed successfully!';
            } elseif ($this->isCraftsman($user)) {
                // Craftsman completes the order (moves to for approval)
                // Only update items belonging to this craftsman and in 'Accepted' status
                StockOrderItem::whereIn('stock_order_id', $request->order_ids)
                    ->where('craftsman_id', $user->id)
                    ->where('status', 'Accepted')
                    ->update([
                        'status' => 'Ready for Approval'
                    ]);
                
                $message = count($request->order_ids) . ' orders moved to for approval!';
            } else {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            DB::commit();

            if ($this->isAdmin($user)) {
                foreach ($request->order_ids as $orderId) {
                    $order = StockOrder::find($orderId);
                    if ($order) $this->notifyBuyer($order);
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to bulk complete: ' . $e->getMessage()], 500);
        }
    }

    public function bulkAccept(Request $request)
    {
        $user = Auth::user();
        if (!$this->isCraftsman($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:stock_orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            StockOrderItem::whereIn('stock_order_id', $request->order_ids)
                ->where('craftsman_id', $user->id)
                ->whereIn('status', ['Pending', 'Allocated'])
                ->update(['status' => 'Accepted']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($request->order_ids) . ' orders accepted successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to bulk accept: ' . $e->getMessage()], 500);
        }
    }

    public function bulkReject(Request $request)
    {
        $user = Auth::user();
        if (!$this->isCraftsman($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:stock_orders,id',
            'rejection_reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            StockOrderItem::whereIn('stock_order_id', $request->order_ids)
                ->where('craftsman_id', $user->id)
                ->whereIn('status', ['Pending', 'Allocated'])
                ->update([
                    'status' => 'Rejected',
                    'rejection_reason' => $request->rejection_reason
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($request->order_ids) . ' orders rejected successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to bulk reject: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if (!$this->isAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $stockOrder = StockOrder::find($id);
        if (!$stockOrder) {
            return response()->json(['success' => false, 'message' => 'Stock order not found'], 404);
        }

        $stockOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully.'
        ]);
    }

    /**
     * Calculate color keys and hex colors for stock orders based on status.
     */
    private function calculateStockOrderColors($order): array
    {
        $user = Auth::user();
        $isEligibleForColor = $user && ($this->isAdmin($user) || $this->isCraftsman($user));
        $colorKey = null;
        $colorHex = null;

        if ($isEligibleForColor) {
            $itemStatus = $order->item_status;
            if (!$itemStatus && $order->relationLoaded('items') && $order->items->isNotEmpty()) {
                $itemStatus = $order->items->first()->status;
            }

            if ($itemStatus === 'Rejected') {
                // Rejected -> Light Red
                $colorKey = 'light-red';
                $colorHex = '#FFCDD2';
            } elseif ($itemStatus === 'Accepted') {
                // Accepted/In-Process -> Light Orange
                $colorKey = 'light-orange';
                $colorHex = '#FFE0B2';
            } elseif ($order->status === 'Allocated' && ($itemStatus === 'Pending' || !$itemStatus)) {
                // Allocated (first 12 hours light-blue, after 12 hours light-yellow)
                $allocationTime = $order->updated_at;
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
