<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Craftman;
use App\Models\WorkOrder;
use App\Models\PurchaseOrder;

class DetailsAllController extends Controller
{
    public function index(Request $request)
    {
        $craftsmen = Craftman::all();
        $allWorkOrders = WorkOrder::whereNotNull('allocated_craftsman_bp_code')->get();
        $allPurchaseOrders = PurchaseOrder::whereNotNull('allocated_craftsman_code')->get();
        
        $craftsmanStats = [];
        
        foreach ($craftsmen as $c) {
            $code = $c->craftman_code;
            $name = $c->name ?? $c->business_name;

            $stats = [
                'code' => $code,
                'name' => $name,
                'allocated' => 0,
                'completed' => 0,
                'in_process' => 0,
                'for_approval' => 0,
                'total_weight' => 0,
                'wa_total_weight' => 0,
                'po_total_weight' => 0,
                'total_amount' => 0,
                'overdue' => 0,
                
                // Work Order Breakdown
                'wo' => [
                    'new' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    'allocated' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    'in_process' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    'completed' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    'overdue' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    'for_approval' => ['count' => 0, 'weight' => 0, 'orders' => []],
                ],
                
                // Purchase Order Breakdown
                'po' => [
                    'new' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    'allocated' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    'in_process' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    'completed' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    'overdue' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    'for_approval' => ['count' => 0, 'weight' => 0, 'orders' => []],
                ]
            ];

            // 1. Process Work Orders
            $myWorkOrders = $allWorkOrders->where('allocated_craftsman_bp_code', $code);
            foreach ($myWorkOrders as $wo) {
                $w = floatval($wo->weight_to ?: $wo->weight_from);
                $isWoOverdue = (method_exists($wo, 'isOverdue') && $wo->isOverdue());

                $orderDetails = [
                    'number' => $wo->work_order_number,
                    'qty' => $wo->quantity,
                    'weight' => $w,
                    'bp_code' => $wo->bp_code,
                    'business_name' => $wo->customer_name,
                    'due_date' => $wo->due_date ? Carbon::parse($wo->due_date)->format('Y-m-d') : '-',
                    'overdue_days' => $isWoOverdue ? intval(Carbon::parse($wo->due_date)->diffInDays(now())) : 0
                ];
                
                if (!$wo->craftsman_status || $wo->craftsman_status == 'new' || $wo->craftsman_status == 'allocated') {
                    $stats['wo']['new']['count']++;
                    $stats['wo']['new']['weight'] += $w;
                    $stats['wo']['new']['orders'][] = $orderDetails;
                    $stats['allocated']++;
                }
                
                if ($wo->craftsman_status == 'in_process') {
                    $stats['wo']['in_process']['count']++;
                    $stats['wo']['in_process']['weight'] += $w;
                    $stats['wo']['in_process']['orders'][] = $orderDetails;
                    $stats['in_process']++;
                }
                
                if ($wo->craftsman_status == 'completed' || $wo->status == 'completed') {
                    $stats['wo']['completed']['count']++;
                    $stats['wo']['completed']['weight'] += $w;
                    $stats['wo']['completed']['orders'][] = $orderDetails;
                    $stats['completed']++;
                }

                if ($isWoOverdue) {
                    $stats['wo']['overdue']['count']++;
                    $stats['wo']['overdue']['weight'] += $w;
                    $stats['wo']['overdue']['orders'][] = $orderDetails;
                    $stats['overdue']++;
                }

                if ($wo->status == 'for_approval') {
                    $stats['wo']['for_approval']['count']++;
                    $stats['wo']['for_approval']['weight'] += $w;
                    $stats['wo']['for_approval']['orders'][] = $orderDetails;
                    $stats['for_approval']++;
                }
                
                $stats['wa_total_weight'] += $w;
                $stats['total_weight'] += $w;
            }

            // 2. Process Purchase Orders
            $myPurchaseOrders = $allPurchaseOrders->where('allocated_craftsman_code', $code);
            foreach ($myPurchaseOrders as $po) {
                $poWeight = 0;
                $poAmount = 0;
                $poQty = 0;
                $items = is_string($po->items) ? json_decode($po->items, true) : ($po->items ?? []);
                if (is_array($items)) {
                    foreach ($items as $item) {
                        if (isset($item['total'])) {
                            $poWeight += floatval($item['total']);
                        } elseif (isset($item['grams']) && is_array($item['grams'])) {
                            foreach ($item['grams'] as $i => $gram) {
                                $poWeight += floatval($gram) * intval($item['quantity'][$i] ?? 1);
                            }
                        }
                        
                        if (isset($item['quantity']) && isset($item['rate'])) {
                            $qty = is_array($item['quantity']) ? array_sum($item['quantity']) : floatval($item['quantity']);
                            $poAmount += ($qty * floatval($item['rate']));
                            $poQty += $qty;
                        } elseif (isset($item['quantity'])) {
                            $poQty += is_array($item['quantity']) ? array_sum($item['quantity']) : floatval($item['quantity']);
                        }
                    }
                }
                
                $isPoOverdue = ($po->due_date && $po->due_date < now() && $po->status != 'completed');
                $poOrderDetails = [
                    'number' => $po->purchase_order_code,
                    'qty' => $poQty,
                    'weight' => $poWeight,
                    'bp_code' => '-',
                    'business_name' => '-',
                    'due_date' => $po->due_date ? Carbon::parse($po->due_date)->format('Y-m-d') : '-',
                    'overdue_days' => $isPoOverdue ? intval(Carbon::parse($po->due_date)->diffInDays(now())) : 0
                ];

                if (!$po->craftsman_status || $po->craftsman_status == 'allocated') {
                    $stats['po']['new']['count']++;
                    $stats['po']['new']['weight'] += $poWeight;
                    $stats['po']['new']['orders'][] = $poOrderDetails;
                    $stats['allocated']++;
                }

                if ($po->craftsman_status == 'in_process') {
                    $stats['po']['in_process']['count']++;
                    $stats['po']['in_process']['weight'] += $poWeight;
                    $stats['po']['in_process']['orders'][] = $poOrderDetails;
                    $stats['in_process']++;
                }

                if ($po->craftsman_status == 'completed' || $po->status == 'completed') {
                    $stats['po']['completed']['count']++;
                    $stats['po']['completed']['weight'] += $poWeight;
                    $stats['po']['completed']['orders'][] = $poOrderDetails;
                    $stats['completed']++;
                }

                if ($isPoOverdue) {
                    $stats['po']['overdue']['count']++;
                    $stats['po']['overdue']['weight'] += $poWeight;
                    $stats['po']['overdue']['orders'][] = $poOrderDetails;
                    $stats['overdue']++;
                }

                if ($po->status == 'for_approval') {
                    $stats['po']['for_approval']['count']++;
                    $stats['po']['for_approval']['weight'] += $poWeight;
                    $stats['po']['for_approval']['orders'][] = $poOrderDetails;
                    $stats['for_approval']++;
                }

                $stats['po_total_weight'] += $poWeight;
                $stats['total_weight'] += $poWeight;
                $stats['total_amount'] += $poAmount;
            }

            $craftsmanStats[$code] = $stats;
        }

        $collection = collect($craftsmanStats);
        
        // Filter logic
        $status = $request->input('status', 'all');
        if ($status === 'in_process') {
            $collection = $collection->filter(fn($s) => $s['in_process'] > 0);
        } elseif ($status === 'for_approval') {
            $collection = $collection->filter(fn($s) => $s['for_approval'] > 0);
        } elseif ($status === 'allocated') {
            $collection = $collection->filter(fn($s) => $s['allocated'] > 0);
        } elseif ($status === 'completed') {
            $collection = $collection->filter(fn($s) => $s['completed'] > 0);
        } elseif ($status === 'overdue') {
            // Must have overdue > 0. If overdue is 0 (even if all completed), they are NOT shown.
            $collection = $collection->filter(fn($s) => $s['overdue'] > 0);
        }

        // Sort By
        $sortBy = $request->input('sort_by', 'allocated');
        $sortOrder = $request->input('sort_order', 'desc');

        if ($sortOrder === 'desc') {
            $collection = $collection->sortByDesc($sortBy);
        } else {
            $collection = $collection->sortBy($sortBy);
        }

        $craftsmenData = $collection->values()->all();

        return view('super-admin.details-all.index', compact('craftsmenData', 'status', 'sortBy', 'sortOrder'));
    }
}