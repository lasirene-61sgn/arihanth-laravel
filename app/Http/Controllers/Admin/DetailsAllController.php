<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DetailsAllController extends Controller
{
    public function index(Request $request)
    {
        $craftsmen = \App\Models\Craftman::all();
        $allWorkOrders = \App\Models\WorkOrder::whereNotNull('allocated_craftsman_bp_code')->get();
        $allPurchaseOrders = \App\Models\PurchaseOrder::whereNotNull('allocated_craftsman_code')->get();
        
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
                
                $orderDetails = [
                    'number' => $wo->work_order_number,
                    'qty' => $wo->quantity,
                    'weight' => $w,
                    'bp_code' => $wo->bp_code,
                    'business_name' => $wo->customer_name,
                    'craftsman_code' => $wo->allocated_craftsman_bp_code ?? 'N/A',
                    'craftsman_name' => $wo->craftsman ? ($wo->craftsman->name ?? $wo->craftsman->business_name) : 'N/A',
                    'due_date' => $wo->due_date ? \Carbon\Carbon::parse($wo->due_date)->format('Y-m-d') : '-',
                    'overdue_days' => $wo->isOverdue() ? intval(\Carbon\Carbon::parse($wo->due_date)->diffInDays(now())) : 0
                ];
                
                if (!$wo->craftsman_status || $wo->craftsman_status == 'new' || $wo->craftsman_status == 'allocated') {
                    $stats['wo']['new']['count']++;
                    $stats['wo']['new']['weight'] += $w;
                    $stats['wo']['new']['orders'][] = $orderDetails;
                }
                
                if ($wo->craftsman_status == 'in_process') {
                    $stats['wo']['in_process']['count']++;
                    $stats['wo']['in_process']['weight'] += $w;
                    $stats['wo']['in_process']['orders'][] = $orderDetails;
                }
                
                if ($wo->craftsman_status == 'completed' || $wo->status == 'completed') {
                    $stats['wo']['completed']['count']++;
                    $stats['wo']['completed']['weight'] += $w;
                    $stats['wo']['completed']['orders'][] = $orderDetails;
                }

                if ($wo->isOverdue()) {
                    $stats['wo']['overdue']['count']++;
                    $stats['wo']['overdue']['weight'] += $w;
                    $stats['wo']['overdue']['orders'][] = $orderDetails;
                    $stats['overdue']++;
                }

                if ($wo->status == 'for_approval') {
                    $stats['wo']['for_approval']['count']++;
                    $stats['wo']['for_approval']['weight'] += $w;
                    $stats['wo']['for_approval']['orders'][] = $orderDetails;
                }
                
                $stats['wa_total_weight'] += $w;
                $stats['allocated']++;
                $stats['total_weight'] += $w;
                if ($wo->craftsman_status == 'completed' || $wo->status == 'completed') $stats['completed']++;
                if ($wo->craftsman_status == 'in_process') $stats['in_process']++;
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
                
                $poOrderDetails = [
                    'number' => $po->purchase_order_code,
                    'qty' => $poQty,
                    'weight' => $poWeight,
                    'bp_code' => '-',
                    'business_name' => '-',
                    'due_date' => $po->due_date ? \Carbon\Carbon::parse($po->due_date)->format('Y-m-d') : '-',
                    'overdue_days' => ($po->due_date && $po->due_date < now() && $po->status != 'completed') ? intval(\Carbon\Carbon::parse($po->due_date)->diffInDays(now())) : 0
                ];

                $stats['po']['allocated']['count']++;
                $stats['po']['allocated']['weight'] += $poWeight;
                $stats['po']['allocated']['orders'][] = $poOrderDetails;

                if (!$po->craftsman_status || $po->craftsman_status == 'allocated') {
                    $stats['po']['new']['count']++;
                    $stats['po']['new']['weight'] += $poWeight;
                    $stats['po']['new']['orders'][] = $poOrderDetails;
                }

                if ($po->craftsman_status == 'in_process') {
                    $stats['po']['in_process']['count']++;
                    $stats['po']['in_process']['weight'] += $poWeight;
                    $stats['po']['in_process']['orders'][] = $poOrderDetails;
                }

                if ($po->craftsman_status == 'completed' || $po->status == 'completed') {
                    $stats['po']['completed']['count']++;
                    $stats['po']['completed']['weight'] += $poWeight;
                    $stats['po']['completed']['orders'][] = $poOrderDetails;
                }

                if ($po->due_date && $po->due_date < now() && $po->status != 'completed') {
                    $stats['po']['overdue']['count']++;
                    $stats['po']['overdue']['weight'] += $poWeight;
                    $stats['po']['overdue']['orders'][] = $poOrderDetails;
                    $stats['overdue']++;
                }

                $stats['po_total_weight'] += $poWeight;
                $stats['total_weight'] += $poWeight;
                $stats['total_amount'] += $poAmount;
                $stats['allocated']++;
                if ($po->craftsman_status == 'completed' || $po->status == 'completed') $stats['completed']++;
                if ($po->craftsman_status == 'in_process') $stats['in_process']++;
            }

            $craftsmanStats[$code] = $stats;
        }

        $collection = collect($craftsmanStats);
        
        // Filter
        $status = $request->input('status', 'all');
        if ($status === 'in_process') {
            $collection = $collection->filter(function($stat) { return $stat['in_process'] > 0; });
        } elseif ($status === 'completed') {
            $collection = $collection->filter(function($stat) { return $stat['completed'] > 0; });
        } elseif ($status === 'overdue') {
            $collection = $collection->filter(function($stat) { return $stat['overdue'] > 0; });
        }

        // Sort
        $sortBy = $request->input('sort_by', 'allocated');
        $sortOrder = $request->input('sort_order', 'desc');

        if ($sortOrder === 'desc') {
            $collection = $collection->sortByDesc($sortBy);
        } else {
            $collection = $collection->sortBy($sortBy);
        }

        $craftsmenData = $collection->values()->all();

        $clientStats = [];
        foreach ($allWorkOrders as $wo) {
            if (!$wo->customer_name) continue;
            
            $code = $wo->bp_code ?? 'N/A';
            if (!isset($clientStats[$code])) {
                $clientStats[$code] = [
                    'name' => $wo->customer_name,
                    'orders' => 0,
                    'new' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    'in_process' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    'for_approval' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    'overdue' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    'completed' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    'rejected' => ['count' => 0, 'weight' => 0, 'orders' => []],
                ];
            }
            
            $clientStats[$code]['orders']++;
            $w = floatval($wo->weight_to ?: $wo->weight_from);
            
            $isWoOverdue = (method_exists($wo, 'isOverdue') && $wo->isOverdue());
            
            $orderDetails = [
                'number' => $wo->work_order_number,
                'qty' => $wo->quantity,
                'weight' => $w,
                'bp_code' => $wo->bp_code,
                'business_name' => $wo->customer_name,
                'due_date' => $wo->due_date ? \Carbon\Carbon::parse($wo->due_date)->format('Y-m-d') : '-',
                'craftsman_code' => $wo->allocated_craftsman_bp_code ?? 'N/A',
                'craftsman_name' => $wo->craftsman ? ($wo->craftsman->name ?? $wo->craftsman->business_name) : 'N/A',
                'overdue_days' => ($isWoOverdue && $wo->due_date) ? \Carbon\Carbon::now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($wo->due_date)->startOfDay()) : 0
            ];

            if (!$wo->craftsman_status || $wo->craftsman_status == 'new' || $wo->craftsman_status == 'allocated') {
                $clientStats[$code]['new']['count']++;
                $clientStats[$code]['new']['weight'] += $w;
                $clientStats[$code]['new']['orders'][] = $orderDetails;
            }
            if ($wo->craftsman_status == 'in_process') {
                $clientStats[$code]['in_process']['count']++;
                $clientStats[$code]['in_process']['weight'] += $w;
                $clientStats[$code]['in_process']['orders'][] = $orderDetails;
            }
            if ($wo->status == 'for_approval') {
                $clientStats[$code]['for_approval']['count']++;
                $clientStats[$code]['for_approval']['weight'] += $w;
                $clientStats[$code]['for_approval']['orders'][] = $orderDetails;
            }
            if ($isWoOverdue) {
                $clientStats[$code]['overdue']['count']++;
                $clientStats[$code]['overdue']['weight'] += $w;
                $clientStats[$code]['overdue']['orders'][] = $orderDetails;
            }
            if ($wo->craftsman_status == 'completed' || $wo->status == 'completed') {
                $clientStats[$code]['completed']['count']++;
                $clientStats[$code]['completed']['weight'] += $w;
                $clientStats[$code]['completed']['orders'][] = $orderDetails;
            }
            if ($wo->craftsman_status == 'rejected') {
                $clientStats[$code]['rejected']['count']++;
                $clientStats[$code]['rejected']['weight'] += $w;
                $clientStats[$code]['rejected']['orders'][] = $orderDetails;
            }
        }
        
        uasort($clientStats, function($a, $b) { return $b['orders'] <=> $a['orders']; });
        $topPicksClientsFull = array_slice($clientStats, 0, 15, true);

        return view('admin.details-all.index', compact('craftsmenData', 'status', 'sortBy', 'sortOrder', 'topPicksClientsFull'));
    }
}
