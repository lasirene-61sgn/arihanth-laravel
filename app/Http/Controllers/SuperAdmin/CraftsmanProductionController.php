<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Craftman;
use App\Models\WorkOrder;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CraftsmanProductionController extends Controller
{
    public function index(Request $request)
    {
        $query = Craftman::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('craftman_code', 'LIKE', "%{$search}%")
                  ->orWhere('business_name', 'LIKE', "%{$search}%");
            });
        }

        $craftsmen = $query->paginate(20);

        return view('super-admin.craftsman-production.index', compact('craftsmen'));
    }

    public function show(Request $request, $code)
    {
        $craftsman = Craftman::where('craftman_code', $code)->firstOrFail();
        $tab = $request->get('tab', 'new');
        $buyerCode = $request->get('buyer_code');

        // Fetch all buyers for the dropdown
        $buyers = \App\Models\Buyer::orderBy('name')->get();

        $now = Carbon::now();

        // We need counts for ALL statuses for the selected buyer (or all if no buyer selected)
        // So let's fetch ALL work orders and purchase orders for this craftsman (and buyer if selected)
        // to calculate counts, and then filter for the tab.

        $allWorkOrdersQuery = WorkOrder::where('allocated_craftsman_bp_code', $code);
        $allPurchaseOrdersQuery = PurchaseOrder::where('allocated_craftsman_code', $code);

        if ($buyerCode) {
            $allWorkOrdersQuery->where('bp_code', $buyerCode);
        }

        $allWorkOrders = $allWorkOrdersQuery->get();
        $allPurchaseOrders = $allPurchaseOrdersQuery->get();

        // If buyer selected, we need to filter Purchase Orders by buyer in PHP
        if ($buyerCode) {
            $allPurchaseOrders = $allPurchaseOrders->filter(function($po) use ($buyerCode) {
                $items = $po->items ?? [];
                foreach ($items as $item) {
                    $productId = $item['product_id'] ?? null;
                    if ($productId) {
                        $product = \App\Models\Product::find($productId);
                        if ($product && $product->bp_code === $buyerCode) {
                            return true;
                        }
                    }
                }
                return false;
            });
        }

        // Now calculate counts for the selected buyer (or all if no buyer selected)
        $buyerMetrics = [
            'work_orders' => [
                'allocated' => 0,
                'in_process' => 0,
                'overdue' => 0,
                'completed' => 0,
                'rejected' => 0,
            ],
            'purchase_orders' => [
                'allocated' => 0,
                'in_process' => 0,
                'overdue' => 0,
                'completed' => 0,
                'rejected' => 0,
            ],
        ];

        foreach ($allWorkOrders as $wo) {
            if ($wo->status === 'completed') {
                $buyerMetrics['work_orders']['completed']++;
            } elseif ($wo->craftsman_status === 'rejected') {
                $buyerMetrics['work_orders']['rejected']++;
            } elseif ($wo->isOverdue()) {
                $buyerMetrics['work_orders']['overdue']++;
            } elseif ($wo->craftsman_status === 'in_process') {
                $buyerMetrics['work_orders']['in_process']++;
            } else {
                $buyerMetrics['work_orders']['allocated']++; // Default to allocated if assigned but not in process
            }
        }

        foreach ($allPurchaseOrders as $po) {
            if ($po->status === 'approved' || $po->status === 'completed') {
                $buyerMetrics['purchase_orders']['completed']++;
            } elseif ($po->craftsman_status === 'rejected') {
                $buyerMetrics['purchase_orders']['rejected']++;
            } elseif ($po->due_date && $po->due_date->isBefore($now->startOfDay()) && $po->status !== 'approved') {
                $buyerMetrics['purchase_orders']['overdue']++;
            } elseif ($po->craftsman_status === 'in_process') {
                $buyerMetrics['purchase_orders']['in_process']++;
            } elseif ($po->craftsman_status === 'allocated') {
                $buyerMetrics['purchase_orders']['allocated']++;
            }
        }

        // Now apply tab filtering for the displayed lists
        $workOrders = $allWorkOrders->filter(function($wo) use ($tab, $now) {
            switch ($tab) {
                case 'new': return $wo->status === 'new';
                case 'in_process': return $wo->craftsman_status === 'in_process';
                case 'completed': return $wo->status === 'completed';
                case 'overdue': return $wo->isOverdue();
                default: return true;
            }
        });

        $purchaseOrders = $allPurchaseOrders->filter(function($po) use ($tab, $now) {
            switch ($tab) {
                case 'new': return $po->status === 'new';
                case 'in_process': return $po->craftsman_status === 'in_process';
                case 'completed': return $po->status === 'approved';
                case 'overdue': 
                    return $po->status !== 'approved' 
                        && $po->craftsman_status !== 'rejected' 
                        && $po->due_date 
                        && $po->due_date->isBefore($now->startOfDay());
                default: return true;
            }
        });

        // Calculate PO total weight for the filtered list
        foreach ($purchaseOrders as $po) {
            $totalWeight = 0;
            $items = $po->items ?? [];
            foreach ($items as $item) {
                $totalWeight += (float)($item['weight'] ?? 0);
            }
            $po->total_calculated_weight = $totalWeight;
        }

        return view('super-admin.craftsman-production.show', compact('craftsman', 'workOrders', 'purchaseOrders', 'tab', 'buyers', 'buyerCode', 'buyerMetrics'));
    }
}
