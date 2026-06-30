<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Craftman;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockOrder;
use App\Models\StockOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $activeTab = $request->get('tab', 'new-orders');
        $perPage = 15;

        // Define queries for tabs
        $queries = [
            'new-orders' => StockOrder::where('status', 'Pending'),
            'allocated-orders' => StockOrder::where('status', 'Allocated')
                ->whereDoesntHave('items', function($q) {
                    $q->whereIn('status', ['Accepted', 'Completed', 'Rejected', 'Ready for Approval']);
                }),
            'in-process-orders' => StockOrder::where('status', 'Allocated')
                ->whereHas('items', function($q) {
                    $q->where('status', 'Accepted');
                })->whereDoesntHave('items', function($q) {
                    $q->whereIn('status', ['Completed', 'Rejected', 'Ready for Approval']);
                }),
            'for-approval-orders' => StockOrder::where('status', 'Allocated')
                ->whereHas('items', function($q) {
                    $q->where('status', 'Ready for Approval');
                }),
            'completed-orders' => StockOrder::where('status', 'Completed'),
            'rejected-orders' => StockOrder::where('status', 'Allocated')
                ->whereHas('items', function($q) {
                    $q->where('status', 'Rejected');
                }),
            'all-orders' => StockOrder::query(),
        ];

        // Apply filters to all queries for counts
        $counts = [];
        foreach ($queries as $tabKey => $query) {
            $countQuery = clone $query;
            
            // Apply search
            if ($search) {
                $searchTerm = '%' . $search . '%';
                $countQuery->where(function($q) use ($searchTerm) {
                    $q->where('order_number', 'like', $searchTerm)
                      ->orWhereHas('buyer', function($bq) use ($searchTerm) {
                          $bq->where('business_name', 'like', $searchTerm)
                            ->orWhere('bp_code', 'like', $searchTerm);
                      });
                });
            }

            // Apply Date Filters
            if ($request->filled('date_from')) {
                $countQuery->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $countQuery->whereDate('created_at', '<=', $request->date_to);
            }

            $counts[$tabKey] = $countQuery->count();
        }

        // Get data for active tab
        $activeQuery = $queries[$activeTab] ?? $queries['new-orders'];
        
        // Apply search to active query
        if ($search) {
            $searchTerm = '%' . $search . '%';
            $activeQuery->where(function($q) use ($searchTerm) {
                $q->where('order_number', 'like', $searchTerm)
                  ->orWhereHas('buyer', function($bq) use ($searchTerm) {
                      $bq->where('business_name', 'like', $searchTerm)
                        ->orWhere('bp_code', 'like', $searchTerm);
                  });
            });
        }

        // Apply Date Filters to active query
        if ($request->filled('date_from')) {
            $activeQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $activeQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $activeQuery->with('buyer')->withCount('items')->latest()->paginate($perPage)->withQueryString();
        $craftsmen = Craftman::where('is_frozen', false)->get();

        return view('admin.stock-order.index', compact('orders', 'craftsmen', 'counts', 'activeTab'));
    }

    public function create()
    {
        $buyers = Buyer::where('is_frozen', false)->get();
        $categories = ProductCategory::all();
        return view('admin.stock-order.create', compact('buyers', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'buyer_id' => 'required|exists:buyers,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.design_code' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.grams' => 'nullable|numeric',
        ]);

        DB::transaction(function () use ($request) {
            // Group items by design_code to create one order per design
            $itemsByDesign = collect($request->items)->groupBy('design_code');

            foreach ($itemsByDesign as $designCode => $items) {
                $order = StockOrder::create([
                    'buyer_id' => $request->buyer_id,
                    'notes' => $request->notes,
                    'status' => 'Pending',
                    'total_items' => count($items),
                ]);

                foreach ($items as $itemData) {
                    $order->items()->create([
                        'product_id' => $itemData['id'] ?? $itemData['product_id'] ?? null,
                        'design_code' => $itemData['design_code'],
                        'category_name' => $itemData['category_name'] ?? $itemData['category'] ?? null,
                        'subcategory_name' => $itemData['subcategory_name'] ?? $itemData['subcategory'] ?? null,
                        'weight_from' => $itemData['weight_from'] ?? null,
                        'weight_to' => $itemData['weight_to'] ?? null,
                        'size' => $itemData['size'] ?? null,
                        'quantity' => $itemData['quantity'],
                        'grams' => $itemData['grams'] ?? null,
                        'item_notes' => $itemData['item_notes'] ?? null,
                        'image_path' => $itemData['image_raw'] ?? null,
                        'status' => 'Pending',
                    ]);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Stock order created successfully!'
        ]);
    }

    public function show(StockOrder $stockOrder)
    {
        $stockOrder->load(['buyer', 'items', 'craftsman']);
        $craftsmen = Craftman::where('is_frozen', false)->get();
        
        return view('admin.stock-order.show', compact('stockOrder', 'craftsmen'));
    }

    public function edit(StockOrder $stockOrder)
    {
        $stockOrder->load('items');
        $buyers = Buyer::where('is_frozen', false)->get();
        $categories = ProductCategory::all();
        return view('admin.stock-order.edit', compact('stockOrder', 'buyers', 'categories'));
    }

    public function update(Request $request, StockOrder $stockOrder)
    {
        $request->validate([
            'buyer_id' => 'required|exists:buyers,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.design_code' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.grams' => 'nullable|numeric',
        ]);

        DB::transaction(function () use ($request, $stockOrder) {
            $stockOrder->update([
                'buyer_id' => $request->buyer_id,
                'notes' => $request->notes,
                'total_items' => count($request->items),
            ]);

            // Simple approach: delete old items and create new ones
            $stockOrder->items()->delete();

            foreach ($request->items as $itemData) {
                $stockOrder->items()->create([
                    'design_code' => $itemData['design_code'],
                    'category_name' => $itemData['category_name'] ?? null,
                    'subcategory_name' => $itemData['subcategory_name'] ?? null,
                    'weight_from' => $itemData['weight_from'] ?? null,
                    'weight_to' => $itemData['weight_to'] ?? null,
                    'size' => $itemData['size'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'grams' => $itemData['grams'] ?? null,
                    'item_notes' => $itemData['item_notes'] ?? null,
                    'status' => 'Pending',
                ]);
            }
        });

        return redirect()->route('admin.stock-order.index')->with('success', 'Stock order updated successfully!');
    }

    public function allocate(Request $request, StockOrder $stockOrder)
    {
        $request->validate([
            'craftsman_id' => 'required|exists:craftmen,id',
        ]);

        $stockOrder->update([
            'craftsman_id' => $request->craftsman_id,
            'status' => 'Allocated'
        ]);

        // Also update all items to this craftsman
        $stockOrder->items()->update([
            'craftsman_id' => $request->craftsman_id,
            'status' => 'Pending'
        ]);

        return back()->with('success', 'Stock order allocated successfully!');
    }

    public function allocateItems(Request $request, StockOrder $stockOrder)
    {
        $request->validate([
            'craftsman_id' => 'required|exists:craftmen,id',
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:stock_order_items,id',
        ]);

        $stockOrder->items()->whereIn('id', $request->item_ids)->update([
            'craftsman_id' => $request->craftsman_id,
            'status' => 'Pending',
            'rejection_reason' => null
        ]);

        // If order was cancelled or pending, move to Allocated
        if ($stockOrder->status !== 'Allocated' && $stockOrder->status !== 'Completed') {
            $stockOrder->update(['status' => 'Allocated']);
        }

        return back()->with('success', 'Items re-allocated successfully!');
    }

    public function updateStatus(Request $request, StockOrder $stockOrder)
    {
        $request->validate([
            'status' => 'required|in:Pending,Allocated,Completed,Cancelled',
        ]);

        $stockOrder->update(['status' => $request->status]);

        return back()->with('success', 'Order status updated successfully!');
    }

    public function updateItemStatus(Request $request, StockOrder $stockOrder, $itemId)
    {
        $item = $stockOrder->items()->findOrFail($itemId);

        $request->validate([
            'status' => 'required|in:Pending,Accepted,Rejected,Completed',
        ]);

        $item->update([
            'status' => $request->status,
            'rejection_reason' => null
        ]);

        if ($stockOrder->items()->where('status', '!=', 'Completed')->count() === 0) {
            $stockOrder->update(['status' => 'Completed']);
        }

        return back()->with('success', "Item status updated to {$request->status}!");
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
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:stock_orders,id',
            'craftsman_id' => 'required|exists:craftmen,id',
        ]);

        StockOrder::whereIn('id', $request->order_ids)->update([
            'craftsman_id' => $request->craftsman_id,
            'status' => 'Allocated'
        ]);

        \App\Models\StockOrderItem::whereIn('stock_order_id', $request->order_ids)->update([
            'craftsman_id' => $request->craftsman_id,
            'status' => 'Pending'
        ]);

        return back()->with('success', count($request->order_ids) . ' orders allocated successfully!');
    }

    public function bulkComplete(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:stock_orders,id',
        ]);

        StockOrder::whereIn('id', $request->order_ids)->update([
            'status' => 'Completed'
        ]);

        // Also update items to Completed if they are not already
        \App\Models\StockOrderItem::whereIn('stock_order_id', $request->order_ids)->update([
            'status' => 'Completed'
        ]);

        return back()->with('success', count($request->order_ids) . ' orders completed successfully!');
    }
}
