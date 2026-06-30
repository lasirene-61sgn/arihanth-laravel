<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockOrder;
use App\Models\StockOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockOrderController extends Controller
{
    public function index(Request $request)
    {
        $buyerId = Auth::guard('buyer')->id();
        $activeTab = $request->get('tab', 'new-orders');
        $perPage = 15;

        // Base Query with buyer check
        $baseQuery = StockOrder::where('buyer_id', $buyerId);

        // Define queries for tabs
        $queries = [
            'new-orders' => (clone $baseQuery)->where('status', 'Pending'),
            'allocated-orders' => (clone $baseQuery)->where('status', 'Allocated')
                ->whereDoesntHave('items', function($q) {
                    $q->whereIn('status', ['Accepted', 'Completed', 'Rejected', 'Ready for Approval']);
                }),
            'in-process-orders' => (clone $baseQuery)->where('status', 'Allocated')
                ->whereHas('items', function($q) {
                    $q->where('status', 'Accepted');
                })->whereDoesntHave('items', function($q) {
                    $q->whereIn('status', ['Completed', 'Rejected', 'Ready for Approval']);
                }),
            'for-approval-orders' => (clone $baseQuery)->where('status', 'Allocated')
                ->whereHas('items', function($q) {
                    $q->where('status', 'Ready for Approval');
                }),
            'completed-orders' => (clone $baseQuery)->where('status', 'Completed'),
            'rejected-orders' => (clone $baseQuery)->where('status', 'Allocated')
                ->whereHas('items', function($q) {
                    $q->where('status', 'Rejected');
                }),
            'all-orders' => (clone $baseQuery),
        ];

        // Apply filters to all queries for counts
        $counts = [];
        foreach ($queries as $tabKey => $query) {
            $counts[$tabKey] = $query->count();
        }

        // Get data for active tab
        $activeQuery = $queries[$activeTab] ?? $queries['new-orders'];
        
        $orders = $activeQuery->withCount('items')->latest()->paginate($perPage)->withQueryString();

        return view('buyer.stock-order.index', compact('orders', 'counts', 'activeTab'));
    }

    public function create()
    {
        return view('buyer.stock-order.create');
    }

    public function getProductByCode($code)
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

    public function store(Request $request)
    {
        $items = $request->input('items', []);
        
        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'No items in the order.']);
        }

        if (count($items) > 20) {
            return response()->json(['success' => false, 'message' => 'Maximum 20 items allowed per order.']);
        }

        try {
            DB::beginTransaction();

            foreach ($items as $item) {
                $order = StockOrder::create([
                    'buyer_id' => Auth::guard('buyer')->id(),
                    'status' => 'Pending',
                    'total_items' => 1,
                    'notes' => $request->input('notes'),
                ]);

                StockOrderItem::create([
                    'stock_order_id' => $order->id,
                    'product_id' => $item['id'],
                    'design_code' => $item['design_code'],
                    'category_name' => $item['category'],
                    'subcategory_name' => $item['subcategory'],
                    'weight_from' => $item['weight_from'],
                    'weight_to' => $item['weight_to'],
                    'size' => $item['size'],
                    'quantity' => $item['quantity'] ?? 1,
                    'grams' => $item['grams'] ?? 0,
                    'item_notes' => $item['item_notes'] ?? null,
                    'image_path' => $item['image_raw'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock Order created successfully!',
                'redirect' => route('buyer.stock-order.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function show(StockOrder $stockOrder)
    {
        if ($stockOrder->buyer_id !== Auth::guard('buyer')->id()) {
            abort(403);
        }

        $stockOrder->load('items', 'craftsman');
        return view('buyer.stock-order.show', compact('stockOrder'));
    }
}
