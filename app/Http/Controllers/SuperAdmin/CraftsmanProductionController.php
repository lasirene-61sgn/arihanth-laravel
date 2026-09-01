<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Craftman;
use App\Models\WorkOrder;
use App\Models\PurchaseOrder;
use App\Models\Buyer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class CraftsmanProductionController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $search = trim($request->get('search', ''));

        $query = Craftman::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('craftman_code', 'LIKE', "%{$search}%")
                  ->orWhere('business_name', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%");
            });
        }

        $craftsmen = $query->paginate($perPage)->withQueryString();

        return view('super-admin.craftsman-production.index', compact('craftsmen', 'search', 'perPage'));
    }

    public function show(Request $request, $code)
    {
        $craftsman = Craftman::where('craftman_code', $code)->firstOrFail();
        $tab = $request->get('tab', 'new');
        $buyerCode = $request->get('buyer_code');
        $search = trim($request->get('search', ''));
        $perPage = (int) $request->get('per_page', 10);
        $woPage = (int) $request->get('wo_page', 1);
        $poPage = (int) $request->get('po_page', 1);

        $buyers = Buyer::orderBy('name')->get();
        $now = Carbon::now();

        // Work orders & Purchase orders queries
        $allWorkOrdersQuery = WorkOrder::where('allocated_craftsman_bp_code', $code);
        $allPurchaseOrdersQuery = PurchaseOrder::where('allocated_craftsman_code', $code);

        if ($buyerCode) {
            $allWorkOrdersQuery->where('bp_code', $buyerCode);
        }

        $allWorkOrders = $allWorkOrdersQuery->get();
        $allPurchaseOrders = $allPurchaseOrdersQuery->get();

        // Filter PO by buyer if buyer filter is active
        if ($buyerCode) {
            $allPurchaseOrders = $allPurchaseOrders->filter(function ($po) use ($buyerCode) {
                $items = $po->items ?? [];
                foreach ($items as $item) {
                    $productId = $item['product_id'] ?? null;
                    if ($productId) {
                        $product = Product::find($productId);
                        if ($product && $product->bp_code === $buyerCode) {
                            return true;
                        }
                    }
                }
                return false;
            });
        }

        // Metrics Calculation
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
            } elseif (method_exists($wo, 'isOverdue') && $wo->isOverdue()) {
                $buyerMetrics['work_orders']['overdue']++;
            } elseif ($wo->craftsman_status === 'in_process') {
                $buyerMetrics['work_orders']['in_process']++;
            } else {
                $buyerMetrics['work_orders']['allocated']++;
            }
        }

        foreach ($allPurchaseOrders as $po) {
            if ($po->status === 'approved' || $po->status === 'completed') {
                $buyerMetrics['purchase_orders']['completed']++;
            } elseif ($po->craftsman_status === 'rejected') {
                $buyerMetrics['purchase_orders']['rejected']++;
            } elseif ($po->due_date && Carbon::parse($po->due_date)->isBefore($now->startOfDay()) && $po->status !== 'approved') {
                $buyerMetrics['purchase_orders']['overdue']++;
            } elseif ($po->craftsman_status === 'in_process') {
                $buyerMetrics['purchase_orders']['in_process']++;
            } elseif ($po->craftsman_status === 'allocated') {
                $buyerMetrics['purchase_orders']['allocated']++;
            }
        }

        // Filter by Tab
        $filteredWorkOrders = $allWorkOrders->filter(function ($wo) use ($tab) {
            switch ($tab) {
                case 'new': return $wo->status === 'new';
                case 'in_process': return $wo->craftsman_status === 'in_process';
                case 'completed': return $wo->status === 'completed';
                case 'overdue': return method_exists($wo, 'isOverdue') ? $wo->isOverdue() : false;
                default: return true;
            }
        });

        $filteredPurchaseOrders = $allPurchaseOrders->filter(function ($po) use ($tab, $now) {
            switch ($tab) {
                case 'new': return $po->status === 'new';
                case 'in_process': return $po->craftsman_status === 'in_process';
                case 'completed': return in_array($po->status, ['approved', 'completed']);
                case 'overdue':
                    return !in_array($po->status, ['approved', 'completed'])
                        && $po->craftsman_status !== 'rejected'
                        && $po->due_date
                        && Carbon::parse($po->due_date)->isBefore($now->startOfDay());
                default: return true;
            }
        });

        // Calculate PO weights
        foreach ($filteredPurchaseOrders as $po) {
            $totalWeight = 0;
            $items = $po->items ?? [];
            foreach ($items as $item) {
                $totalWeight += (float)($item['weight'] ?? 0);
            }
            $po->total_calculated_weight = $totalWeight;
        }

        // Paginate Work Orders
        $workOrders = new LengthAwarePaginator(
            $filteredWorkOrders->forPage($woPage, $perPage)->values(),
            $filteredWorkOrders->count(),
            $perPage,
            $woPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'wo_page',
            ]
        );

        // Paginate Purchase Orders
        $purchaseOrders = new LengthAwarePaginator(
            $filteredPurchaseOrders->forPage($poPage, $perPage)->values(),
            $filteredPurchaseOrders->count(),
            $perPage,
            $poPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'po_page',
            ]
        );

        return view('super-admin.craftsman-production.show', compact(
            'craftsman',
            'workOrders',
            'purchaseOrders',
            'tab',
            'buyers',
            'buyerCode',
            'buyerMetrics',
            'search',
            'perPage'
        ));
    }
}