<?php

namespace App\Http\Controllers\Craftsman;

use App\Http\Controllers\Controller;
use App\Models\StockOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockOrderController extends Controller
{
    public function index(Request $request)
    {
        $craftsman = Auth::guard('craftsman')->user();
        $activeTab = $request->get('tab', 'allocated-orders');
        $perPage = 15;

        // Base Query
        $baseQuery = StockOrder::where(function($query) use ($craftsman) {
            $query->where('craftsman_id', $craftsman->id)
                  ->orWhereHas('items', function($q) use ($craftsman) {
                      $q->where('craftsman_id', $craftsman->id);
                  });
        });

        // Define queries for tabs
        $queries = [
            'allocated-orders' => (clone $baseQuery)->where('status', 'Allocated')
                ->whereHas('items', function($q) use ($craftsman) {
                    $q->where('craftsman_id', $craftsman->id)->where('status', 'Pending');
                }),
            'in-process-orders' => (clone $baseQuery)->where('status', 'Allocated')
                ->whereHas('items', function($q) use ($craftsman) {
                    $q->where('craftsman_id', $craftsman->id)->where('status', 'Accepted');
                }),
            'for-approval-orders' => (clone $baseQuery)->where('status', 'Allocated')
                ->whereHas('items', function($q) use ($craftsman) {
                    $q->where('craftsman_id', $craftsman->id)->where('status', 'Ready for Approval');
                }),
            'completed-orders' => (clone $baseQuery)->whereHas('items', function($q) use ($craftsman) {
                    $q->where('craftsman_id', $craftsman->id)->whereIn('status', ['Completed', 'Finished']);
                }),
            'rejected-orders' => (clone $baseQuery)->whereHas('items', function($q) use ($craftsman) {
                    $q->where('craftsman_id', $craftsman->id)->where('status', 'Rejected');
                }),
            'all-orders' => clone $baseQuery,
        ];

        // Apply filters to all queries for counts
        $counts = [];
        foreach ($queries as $tabKey => $query) {
            $counts[$tabKey] = $query->count();
        }

        // Get data for active tab
        $activeQuery = $queries[$activeTab] ?? $queries['allocated-orders'];
        
        $orders = $activeQuery->with(['buyer', 'craftsman'])
            ->withCount(['items' => function($query) use ($craftsman) {
                $query->where('craftsman_id', $craftsman->id);
            }])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('craftsman.stock-order.index', compact('orders', 'counts', 'activeTab'));
    }

    public function show(StockOrder $stockOrder)
    {
        $craftsman = Auth::guard('craftsman')->user();
        
        $hasItems = $stockOrder->items()->where('craftsman_id', $craftsman->id)->exists();
        
        if ($stockOrder->craftsman_id !== $craftsman->id && !$hasItems) {
            abort(403);
        }

        $stockOrder->load(['buyer', 'items' => function($query) use ($craftsman) {
            $query->where('craftsman_id', $craftsman->id);
        }]);
        
        return view('craftsman.stock-order.show', compact('stockOrder'));
    }

    public function updateStatus(Request $request, StockOrder $stockOrder)
    {
        $craftsman = Auth::guard('craftsman')->user();
        
        if ($stockOrder->craftsman_id !== $craftsman->id) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:Allocated,Completed,Cancelled',
        ]);

        $stockOrder->update(['status' => $request->status]);

        return back()->with('success', 'Order status updated successfully!');
    }
    public function updateItemStatus(Request $request, $stockOrder, $itemId)
    {
        $craftsman = Auth::guard('craftsman')->user();
        $item = \App\Models\StockOrderItem::where('id', $itemId)
            ->where('craftsman_id', $craftsman->id)
            ->firstOrFail();

        $request->validate([
            'status' => 'required|in:Accepted,Rejected,Completed,Finished',
            'rejection_reason' => 'required_if:status,Rejected',
        ]);

        $item->update([
            'status' => $request->status,
            'rejection_reason' => $request->status === 'Rejected' ? $request->rejection_reason : null
        ]);

        return back()->with('success', "Item marked as {$request->status}!");
    }

    public function bulkAccept(Request $request)
    {
        $craftsman = Auth::guard('craftsman')->user();
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:stock_orders,id',
        ]);

        \App\Models\StockOrderItem::whereIn('stock_order_id', $request->order_ids)
            ->where('craftsman_id', $craftsman->id)
            ->where('status', 'Pending')
            ->update(['status' => 'Accepted']);

        return back()->with('success', 'Selected orders accepted successfully!');
    }

    public function bulkReject(Request $request)
    {
        $craftsman = Auth::guard('craftsman')->user();
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:stock_orders,id',
            'rejection_reason' => 'required|string',
        ]);

        \App\Models\StockOrderItem::whereIn('stock_order_id', $request->order_ids)
            ->where('craftsman_id', $craftsman->id)
            ->where('status', 'Pending')
            ->update([
                'status' => 'Rejected',
                'rejection_reason' => $request->rejection_reason
            ]);

        return back()->with('success', 'Selected orders rejected successfully!');
    }
}
