<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProcessOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLoginForm()
    {
        return view('admin.login');
    }

    /**
     * Handle admin login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email_or_user_code' => 'required',
            'password' => 'required',
        ]);

        // Find admin user by email or user_code
        $admin = ProcessOwner::where(function($query) use ($request) {
                $query->where('email_id', $request->email_or_user_code)
                      ->orWhere('user_code', $request->email_or_user_code);
            })
            ->where('role', 'admin')
            ->first();

        // Check if admin exists and password is correct
        if ($admin && Hash::check($request->password, $admin->password)) {
            // Check if account is frozen
            if ($admin->is_frozen) {
                return back()->withErrors([
                    'email_or_user_code' => 'Your account has been frozen. Please contact the Super Admin.',
                ])->withInput($request->only('email_or_user_code'));
            }
            
            Auth::guard('admin')->login($admin);
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'email_or_user_code' => 'Invalid credentials or you are not authorized as an admin.',
        ])->withInput($request->only('email_or_user_code'));
    }

    /**
     * Show the admin dashboard.
     */
    public function dashboard()
    {
        $admin = Auth::guard('admin')->user();
        
        // Get real-time data for admin dashboard
        $buyersCount = \App\Models\Buyer::count();
        $craftsmenCount = \App\Models\Craftman::count();
        $productsCount = \App\Models\Product::notFromFrozenAccounts()
                                    ->whereNotNull('type')
                                    ->count();
        $designsCount = \App\Models\Product::notFromFrozenAccounts()
                                    ->whereNotNull('type')
                                    ->where('design_status', 'Pending')
                                    ->count();
        $workOrdersCount = \App\Models\WorkOrder::where('status', 'new')->count();
        $purchaseOrdersCount = \App\Models\PurchaseOrder::where('status', 'created')
                                    ->whereNull('allocated_craftsman_code')
                                    ->count();
        $stockOrdersCount = \App\Models\StockOrder::where('status', 'Pending')->count();
        $repairsCount = \App\Models\Repair::whereIn('status', ['Pending', 'Accepted'])->count();
        $usersCount = \App\Models\User::count();
        $keyUsersCount = \App\Models\KeyUser::count();
        
        // Overdue counts
        $workOrdersOverdueCount = \App\Models\WorkOrder::where('status', '!=', 'completed')
                                    ->get()
                                    ->filter(function($wo) { return $wo->isOverdue(); })
                                    ->count();
        $purchaseOrdersOverdueCount = \App\Models\PurchaseOrder::where('status', '!=', 'completed')
                                    ->where('due_date', '<', now())
                                    ->count();
        
        // Get admins count (ProcessOwner with role = 'admin')
        $adminsCount = \App\Models\ProcessOwner::where('role', 'admin')->count();
        
        // Set KYC pending count to 0 since there's no status field in the tables
        $kycPendingCount = 0;
        
        // Get most selling products with design codes
        $productCounts = [];
        $wos = \App\Models\WorkOrder::whereNotNull('product_name')->get();
        foreach ($wos as $wo) {
            $key = $wo->product_name . '|' . ($wo->design_code ?? 'N/A');
            if (!isset($productCounts[$key])) {
                $productCounts[$key] = [
                    'name' => $wo->product_name,
                    'design_code' => $wo->design_code ?? 'N/A',
                    'count' => 0
                ];
            }
            $productCounts[$key]['count']++;
        }

        // Add counts from Purchase Orders
        $pos = \App\Models\PurchaseOrder::all();
        foreach ($pos as $po) {
            $items = is_string($po->items) ? json_decode($po->items, true) : ($po->items ?? []);
            if (is_array($items)) {
                foreach ($items as $item) {
                    $pName = $item['product_name'] ?? ($item['name'] ?? null);
                    $dCode = $item['design_code'] ?? ($item['code'] ?? 'N/A');
                    if ($pName) {
                        $key = $pName . '|' . $dCode;
                        if (!isset($productCounts[$key])) {
                            $productCounts[$key] = [
                                'name' => $pName,
                                'design_code' => $dCode,
                                'count' => 0
                            ];
                        }
                        $productCounts[$key]['count']++;
                    }
                }
            }
        }
        
        uasort($productCounts, function($a, $b) { return $b['count'] <=> $a['count']; });
        $mostSellingProductsFull = array_slice($productCounts, 0, 15, true);
        $mostSellingProducts = array_map(function($p) { return $p['count']; }, $mostSellingProductsFull);
        
        uasort($productCounts, function($a, $b) { return $a['count'] <=> $b['count']; });
        $leastSellingProductsFull = array_slice($productCounts, 0, 15, true);
        $leastSellingProducts = array_map(function($p) { return $p['count']; }, $leastSellingProductsFull);

        // Get craftsman performance
        $craftsmen = \App\Models\Craftman::all();
        $craftsmanStats = [];
        
        // Bulk fetch all relevant orders to avoid N+1 queries
        $allWorkOrders = \App\Models\WorkOrder::whereNotNull('allocated_craftsman_bp_code')->get();
        $allPurchaseOrders = \App\Models\PurchaseOrder::whereNotNull('allocated_craftsman_code')->get();

        foreach ($craftsmen as $c) {
            $code = $c->craftman_code;
            $name = $c->name ?? $c->business_name;

            $stats = [
                'name' => $name,
                'allocated' => 0,
                'completed' => 0,
                'in_process' => 0,
                'total_weight' => 0,
                'wa_total_weight' => 0,
                'po_total_weight' => 0,
                'total_amount' => 0,
                
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
                // Use weight_to as requested, fallback to weight_from if missing
                $w = floatval($wo->weight_to ?: $wo->weight_from);
                $dueDateStr = $wo->craftsman_due_date ? \Carbon\Carbon::parse($wo->craftsman_due_date)->format('d-m-Y') : 'N/A';
                $overdueDaysText = '';
                if ($wo->isOverdue() && $wo->craftsman_due_date) {
                    $overdueDays = \Carbon\Carbon::now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($wo->craftsman_due_date)->startOfDay());
                    $overdueDaysText = " [{$overdueDays} days overdue]";
                }
                $woStr = $wo->work_order_number . ' - ' . ($wo->bp_code ?? 'N/A') . ' - ' . ($wo->customer_name ?? 'Unknown') . ' (W: ' . floatval($wo->weight_from) . 'g, Qty: ' . floatval($wo->quantity) . ', Due: ' . $dueDateStr . ')' . $overdueDaysText;
                
                $stats['wo']['allocated']['count']++;
                $stats['wo']['allocated']['weight'] += $w;
                $stats['wo']['allocated']['orders'][] = $woStr;
                
                if (!$wo->craftsman_status || $wo->craftsman_status == 'new' || $wo->craftsman_status == 'allocated') {
                    $stats['wo']['new']['count']++;
                    $stats['wo']['new']['weight'] += $w;
                    $stats['wo']['new']['orders'][] = $woStr;
                }
                
                if ($wo->craftsman_status == 'in_process') {
                    $stats['wo']['in_process']['count']++;
                    $stats['wo']['in_process']['weight'] += $w;
                    $stats['wo']['in_process']['orders'][] = $woStr;
                }
                
                if ($wo->craftsman_status == 'completed' || $wo->status == 'completed') {
                    $stats['wo']['completed']['count']++;
                    $stats['wo']['completed']['weight'] += $w;
                    $stats['wo']['completed']['orders'][] = $woStr;
                }

                if ($wo->isOverdue()) {
                    $stats['wo']['overdue']['count']++;
                    $stats['wo']['overdue']['weight'] += $w;
                    $stats['wo']['overdue']['orders'][] = $woStr;
                }

                if ($wo->status == 'for_approval') {
                    $stats['wo']['for_approval']['count']++;
                    $stats['wo']['for_approval']['weight'] += $w;
                    $stats['wo']['for_approval']['orders'][] = $woStr;
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
                $items = is_string($po->items) ? json_decode($po->items, true) : ($po->items ?? []);
                if (is_array($items)) {
                    foreach ($items as $item) {
                        // Use the pre-calculated 'total' if available, otherwise sum grams * quantity
                        if (isset($item['total'])) {
                            $poWeight += floatval($item['total']);
                        } elseif (isset($item['grams']) && is_array($item['grams'])) {
                            foreach ($item['grams'] as $i => $gram) {
                                $poWeight += floatval($gram) * intval($item['quantity'][$i] ?? 1);
                            }
                        }
                        
                        if (isset($item['quantity']) && isset($item['rate'])) {
                            // If quantity is an array, take its sum
                            $qty = is_array($item['quantity']) ? array_sum($item['quantity']) : floatval($item['quantity']);
                            $poAmount += ($qty * floatval($item['rate']));
                        }
                    }
                }

                $poDueDateStr = $po->due_date ? \Carbon\Carbon::parse($po->due_date)->format('d-m-Y') : 'N/A';
                $poOverdueDaysText = '';
                if ($po->due_date && $po->due_date < now() && $po->status != 'completed') {
                    $poOverdueDays = \Carbon\Carbon::now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($po->due_date)->startOfDay());
                    $poOverdueDaysText = " [{$poOverdueDays} days overdue]";
                }
                $poStr = $po->purchase_order_code . ' (Due: ' . $poDueDateStr . ')' . $poOverdueDaysText;

                $stats['po']['allocated']['count']++;
                $stats['po']['allocated']['weight'] += $poWeight;
                $stats['po']['allocated']['orders'][] = $poStr;

                if (!$po->craftsman_status || $po->craftsman_status == 'allocated') {
                    $stats['po']['new']['count']++;
                    $stats['po']['new']['weight'] += $poWeight;
                    $stats['po']['new']['orders'][] = $poStr;
                }

                if ($po->craftsman_status == 'in_process') {
                    $stats['po']['in_process']['count']++;
                    $stats['po']['in_process']['weight'] += $poWeight;
                    $stats['po']['in_process']['orders'][] = $poStr;
                }

                if ($po->craftsman_status == 'completed' || $po->status == 'completed') {
                    $stats['po']['completed']['count']++;
                    $stats['po']['completed']['weight'] += $poWeight;
                    $stats['po']['completed']['orders'][] = $poStr;
                }

                if ($po->due_date && $po->due_date < now() && $po->status != 'completed') {
                    $stats['po']['overdue']['count']++;
                    $stats['po']['overdue']['weight'] += $poWeight;
                    $stats['po']['overdue']['orders'][] = $poStr;
                }

                if ($po->status == 'for_approval') {
                    $stats['po']['for_approval']['count']++;
                    $stats['po']['for_approval']['weight'] += $poWeight;
                    $stats['po']['for_approval']['orders'][] = $poStr;
                }

                $stats['po_total_weight'] += $poWeight;
                $stats['allocated']++;
                $stats['total_weight'] += $poWeight;
                $stats['total_amount'] += $poAmount;
                if ($po->craftsman_status == 'completed' || $po->status == 'completed') $stats['completed']++;
                if ($po->craftsman_status == 'in_process') $stats['in_process']++;
            }

            if ($stats['allocated'] > 0) {
                $craftsmanStats[$code] = $stats;
            }
        }
        
        // Top Picks Craftsman (Sorted by Total Overdue count first, then allocated)
        uasort($craftsmanStats, function($a, $b) {
            $aOverdue = $a['wo']['overdue']['count'] + $a['po']['overdue']['count'];
            $bOverdue = $b['wo']['overdue']['count'] + $b['po']['overdue']['count'];
            if ($aOverdue === $bOverdue) {
                return $b['allocated'] <=> $a['allocated'];
            }
            return $bOverdue <=> $aOverdue;
        });
        $topPicksCraftsmanFull = $craftsmanStats;
        $topPicksCraftsman = array_map(function($stat) { return $stat['allocated']; }, $topPicksCraftsmanFull);
        
        uasort($craftsmanStats, function($a, $b) { return $a['allocated'] <=> $b['allocated']; });
        $leastPicksCraftsmanFull = array_slice($craftsmanStats, 0, 15, true);
        $leastPicksCraftsman = array_map(function($stat) { return $stat['allocated']; }, $leastPicksCraftsmanFull);

        // Get buyer order counts for top/least picks with BP codes
        $clientStats = [];
        $wos = \App\Models\WorkOrder::with('craftsman')->whereNotNull('bp_code')->get();
        foreach ($wos as $wo) {
            $code = $wo->bp_code;
            if (!isset($clientStats[$code])) {
                $clientStats[$code] = [
                    'name' => $wo->customer_name ?? 'Unknown',
                    'orders' => 0,
                    'new' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    'in_process' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    'for_approval' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    'overdue' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    'completed' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    'rejected' => ['count' => 0, 'weight' => 0, 'orders' => []]
                ];
            }
            $clientStats[$code]['orders']++;
            
            // Use weight_to as requested, fallback to weight_from if missing
            $w = floatval($wo->weight_to ?: $wo->weight_from);
            
            $orderDetails = [
                'number' => $wo->work_order_number,
                'weight' => $w,
                'qty' => floatval($wo->quantity),
                'due_date' => $wo->craftsman_due_date ? \Carbon\Carbon::parse($wo->craftsman_due_date)->format('d-m-Y') : 'N/A',
                'craftsman_code' => $wo->allocated_craftsman_bp_code ?? 'N/A',
                'craftsman_name' => $wo->craftsman ? ($wo->craftsman->name ?? $wo->craftsman->business_name) : 'N/A',
                'overdue_days' => ($wo->isOverdue() && $wo->craftsman_due_date) ? \Carbon\Carbon::now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($wo->craftsman_due_date)->startOfDay()) : 0
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
            if ($wo->isOverdue()) {
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
        $topPicksClients = array_map(function($c) { return $c['orders']; }, $topPicksClientsFull);
        
        uasort($clientStats, function($a, $b) { return $a['orders'] <=> $b['orders']; });
        $leastPicksClientsFull = array_slice($clientStats, 0, 15, true);
        $leastPicksClients = array_map(function($c) { return $c['orders']; }, $leastPicksClientsFull);
        
        // Get buyer order counts for top/least picks
        $topPicksClients = \App\Models\WorkOrder::select('customer_name')
            ->selectRaw('COUNT(*) as order_count')
            ->whereNotNull('customer_name')
            ->groupBy('customer_name')
            ->orderBy('order_count', 'desc')
            ->limit(10)
            ->pluck('order_count', 'customer_name');
        
        $leastPicksClients = \App\Models\WorkOrder::select('customer_name')
            ->selectRaw('COUNT(*) as order_count')
            ->whereNotNull('customer_name')
            ->groupBy('customer_name')
            ->orderBy('order_count', 'asc')
            ->limit(10)
            ->pluck('order_count', 'customer_name');
        
        // Get quick payments (recent purchase orders with pending payment)
        $quickPayments = \App\Models\PurchaseOrder::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->count();
        
        // Get overdue payments (purchase orders past due date)
        $overduePayments = \App\Models\PurchaseOrder::where('status', 'pending')
            ->where('due_date', '<', now())
            ->count();
        
        // Finance total (calculate from purchase order items)
        $purchaseOrders = \App\Models\PurchaseOrder::all();
        $financeTotal = 0;
        
        foreach ($purchaseOrders as $po) {
            if ($po->items) {
                // Check if items is a string (JSON) or already an array
                if (is_string($po->items)) {
                    $items = json_decode($po->items, true);
                } else {
                    $items = $po->items;
                }
                
                if (is_array($items)) {
                    foreach ($items as $item) {
                        if (isset($item['quantity']) && isset($item['rate'])) {
                            $financeTotal += ($item['quantity'] * $item['rate']);
                        }
                    }
                }
            }
        }
        
        // Count catalogues (products with design_status = 'Accepted' and not null design_code)
        $cataloguesCount = \App\Models\Product::where('design_status', 'Accepted')
                                    ->whereNotNull('design_code')
                                    ->whereNotNull('type')
                                    ->where('type', '!=', '')
                                    ->notFromFrozenAccounts()
                                    ->count();
        
        return view('admin.dashboard', compact(
            'admin',
            'buyersCount',
            'craftsmenCount',
            'productsCount',
            'designsCount',
            'workOrdersCount',
            'purchaseOrdersCount',
            'usersCount',
            'keyUsersCount',
            'adminsCount',
            'kycPendingCount',
            'mostSellingProducts',
            'leastSellingProducts',
            'topPicksCraftsman',
            'leastPicksCraftsman',
            'topPicksClients',
            'leastPicksClients',
            'quickPayments',
            'overduePayments',
            'financeTotal',
            'cataloguesCount',
            'topPicksCraftsmanFull',
            'leastPicksCraftsmanFull',
            'mostSellingProductsFull',
            'leastSellingProductsFull',
            'topPicksClientsFull',
            'leastPicksClientsFull',
            'stockOrdersCount',
            'repairsCount',
            'workOrdersOverdueCount',
            'purchaseOrdersOverdueCount'
        ));
    }

    /**
     * Handle admin logout.
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    /**
     * Show the admin finance page.
     */
    public function finance()
    {
        return view('admin.finance.index');
    }
}