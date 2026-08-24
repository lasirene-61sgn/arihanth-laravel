<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ProcessOwner;
use App\Models\Buyer;
use App\Models\Craftman;
use App\Models\Product;
use App\Models\Design;
use App\Models\WorkOrder;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\KeyUser;
use App\Models\StockOrder;
use App\Models\Repair;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('super-admin.login');
    }

    /**
     * Handle the login request
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_or_user_code' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if input is email or user code for super admins only
        $superAdmin = ProcessOwner::where('role', 'super_admin')
            ->where(function ($query) use ($request) {
                $query->where('email_id', $request->email_or_user_code)
                    ->orWhere('user_code', $request->email_or_user_code);
            })
            ->first();

        if (!$superAdmin) {
            return redirect()->back()
                ->with('error', 'Invalid credentials.')
                ->withInput();
        }

        // Verify password
        if (!Hash::check($request->password, $superAdmin->password)) {
            return redirect()->back()
                ->with('error', 'Invalid credentials.')
                ->withInput();
        }

        // Check if account is frozen
        if ($superAdmin->is_frozen) {
            return redirect()->back()
                ->with('error', 'Your account has been frozen. Please contact the Super Admin.')
                ->withInput();
        }

        // Log in the super admin using the correct guard
        Auth::guard('super_admin')->login($superAdmin);

        return redirect()->route('super-admin.dashboard');
    }

    /**
     * Handle the logout request
     */
    public function logout()
    {
        Auth::guard('super_admin')->logout();
        return redirect()->route('super-admin.login');
    }

    /**
     * Show the dashboard
     */
    public function dashboard()
    {
        // Ensure only super admins can access this dashboard
        $superAdmin = Auth::guard('super_admin')->user();

        if (!$superAdmin || $superAdmin->role !== 'super_admin') {
            Auth::guard('super_admin')->logout();
            return redirect()->route('super-admin.login');
        }

        // Get real-time data
        $buyersCount = Buyer::count();
        $craftsmenCount = Craftman::count();
        $productsCount = Product::notFromFrozenAccounts()
            ->whereNotNull('type')
            ->count();
        $designsCount = Product::notFromFrozenAccounts()
            ->whereNotNull('type')
            ->where('design_status', 'Pending')
            ->count();
        $workOrdersCount = WorkOrder::where('status', 'new')->count();
        $purchaseOrdersCount = PurchaseOrder::where('status', 'created')
            ->whereNull('allocated_craftsman_code')
            ->count();
        $stockOrdersCount = StockOrder::where('status', 'Pending')->count();
        $repairsCount = Repair::whereIn('status', ['Pending', 'Accepted'])->count();
        $usersCount = User::count();
        $keyUsersCount = KeyUser::count();
        
        // Overdue counts
        $workOrdersOverdueCount = WorkOrder::where('status', '!=', 'completed')
                                    ->get()
                                    ->filter(function($wo) { return $wo->isOverdue(); })
                                    ->count();
        $purchaseOrdersOverdueCount = PurchaseOrder::where('status', '!=', 'completed')
                                    ->where('due_date', '<', now())
                                    ->count();

        $cataloguesCount = Product::where('design_status', 'Accepted')
            ->whereNotNull('design_code')
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->notFromFrozenAccounts()
            ->count();

        // Count products that have designs
        $productsWithDesignsCount = Product::whereHas('designs')->count();

        // Get admins count (ProcessOwner with role = 'admin')
        $adminsCount = ProcessOwner::where('role', 'admin')->count();

        // Set KYC pending count to 0 since there's no status field in the tables
        $kycPendingCount = 0;

        // Get most selling products with design codes
        $productCounts = [];
        $wos = \App\Models\WorkOrder::whereNotNull('product_name')->get();
        foreach ($wos as $wo) {
            $key = $wo->product_name . '|' . ($wo->design_code ?? 'N/A');
            if (!isset($productCounts[$key])) {
                $productCounts[$key] = [
                    'product_category' => $wo->product_category,

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

        uasort($productCounts, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        $mostSellingProductsFull = array_slice($productCounts, 0, 15, true);
        $mostSellingProducts = array_map(function ($p) {
            return $p['count'];
        }, $mostSellingProductsFull);

        uasort($productCounts, function ($a, $b) {
            return $a['count'] <=> $b['count'];
        });
        $leastSellingProductsFull = array_slice($productCounts, 0, 15, true);
        $leastSellingProducts = array_map(function ($p) {
            return $p['count'];
        }, $leastSellingProductsFull);

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
                
                $stats['wo']['allocated']['count']++;
                $stats['wo']['allocated']['weight'] += $w;
                $stats['wo']['allocated']['orders'][] = $wo->work_order_number;
                
                if (!$wo->craftsman_status || $wo->craftsman_status == 'new' || $wo->craftsman_status == 'allocated') {
                    $stats['wo']['new']['count']++;
                    $stats['wo']['new']['weight'] += $w;
                    $stats['wo']['new']['orders'][] = $wo->work_order_number;
                }
                
                if ($wo->craftsman_status == 'in_process') {
                    $stats['wo']['in_process']['count']++;
                    $stats['wo']['in_process']['weight'] += $w;
                    $stats['wo']['in_process']['orders'][] = $wo->work_order_number;
                }
                
                if ($wo->craftsman_status == 'completed' || $wo->status == 'completed') {
                    $stats['wo']['completed']['count']++;
                    $stats['wo']['completed']['weight'] += $w;
                    $stats['wo']['completed']['orders'][] = $wo->work_order_number;
                }

                if ($wo->isOverdue()) {
                    $stats['wo']['overdue']['count']++;
                    $stats['wo']['overdue']['weight'] += $w;
                    $stats['wo']['overdue']['orders'][] = $wo->work_order_number;
                }

                if ($wo->status == 'for_approval') {
                    $stats['wo']['for_approval']['count']++;
                    $stats['wo']['for_approval']['weight'] += $w;
                    $stats['wo']['for_approval']['orders'][] = $wo->work_order_number;
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

                $stats['po']['allocated']['count']++;
                $stats['po']['allocated']['weight'] += $poWeight;
                $stats['po']['allocated']['orders'][] = $po->purchase_order_code;

                if (!$po->craftsman_status || $po->craftsman_status == 'allocated') {
                    $stats['po']['new']['count']++;
                    $stats['po']['new']['weight'] += $poWeight;
                    $stats['po']['new']['orders'][] = $po->purchase_order_code;
                }

                if ($po->craftsman_status == 'in_process') {
                    $stats['po']['in_process']['count']++;
                    $stats['po']['in_process']['weight'] += $poWeight;
                    $stats['po']['in_process']['orders'][] = $po->purchase_order_code;
                }

                if ($po->craftsman_status == 'completed' || $po->status == 'completed') {
                    $stats['po']['completed']['count']++;
                    $stats['po']['completed']['weight'] += $poWeight;
                    $stats['po']['completed']['orders'][] = $po->purchase_order_code;
                }

                if ($po->due_date && $po->due_date < now() && $po->status != 'completed') {
                    $stats['po']['overdue']['count']++;
                    $stats['po']['overdue']['weight'] += $poWeight;
                    $stats['po']['overdue']['orders'][] = $po->purchase_order_code;
                }

                if ($po->status == 'for_approval') {
                    $stats['po']['for_approval']['count']++;
                    $stats['po']['for_approval']['weight'] += $poWeight;
                    $stats['po']['for_approval']['orders'][] = $po->purchase_order_code;
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

        // Top Picks Craftsman (by total allocated)
        uasort($craftsmanStats, function ($a, $b) {
            return $b['allocated'] <=> $a['allocated'];
        });
        $topPicksCraftsmanFull = $craftsmanStats;
        $topPicksCraftsman = array_map(function ($stat) {
            return $stat['allocated'];
        }, array_slice($craftsmanStats, 0, 50, true));

        // Least Picks Craftsman (by total allocated)
        uasort($craftsmanStats, function ($a, $b) {
            return $a['allocated'] <=> $b['allocated'];
        });
        $leastPicksCraftsmanFull = array_slice($craftsmanStats, 0, 15, true);
        $leastPicksCraftsman = array_map(function ($stat) {
            return $stat['allocated'];
        }, $leastPicksCraftsmanFull);

        // Get buyer order counts for top/least picks with BP codes
        $clientStats = [];
        $wos = \App\Models\WorkOrder::whereNotNull('bp_code')->get();
        foreach ($wos as $wo) {
            $code = $wo->bp_code;
            if (!isset($clientStats[$code])) {
                $clientStats[$code] = [
                    'name' => $wo->customer_name ?? 'Unknown',
                    'orders' => 0,
                    'new' => ['count' => 0, 'weight' => 0],
                    'in_process' => ['count' => 0, 'weight' => 0],
                    'for_approval' => ['count' => 0, 'weight' => 0],
                    'overdue' => ['count' => 0, 'weight' => 0],
                    'completed' => ['count' => 0, 'weight' => 0],
                    'rejected' => ['count' => 0, 'weight' => 0]
                ];
            }
            $clientStats[$code]['orders']++;
            
            // Use weight_to as requested, fallback to weight_from if missing
            $w = floatval($wo->weight_to ?: $wo->weight_from);
            
            if (!$wo->craftsman_status || $wo->craftsman_status == 'new' || $wo->craftsman_status == 'allocated') {
                $clientStats[$code]['new']['count']++;
                $clientStats[$code]['new']['weight'] += $w;
            }
            if ($wo->craftsman_status == 'in_process') {
                $clientStats[$code]['in_process']['count']++;
                $clientStats[$code]['in_process']['weight'] += $w;
            }
            if ($wo->status == 'for_approval') {
                $clientStats[$code]['for_approval']['count']++;
                $clientStats[$code]['for_approval']['weight'] += $w;
            }
            if ($wo->isOverdue()) {
                $clientStats[$code]['overdue']['count']++;
                $clientStats[$code]['overdue']['weight'] += $w;
            }
            if ($wo->craftsman_status == 'completed' || $wo->status == 'completed') {
                $clientStats[$code]['completed']['count']++;
                $clientStats[$code]['completed']['weight'] += $w;
            }
            if ($wo->craftsman_status == 'rejected') {
                $clientStats[$code]['rejected']['count']++;
                $clientStats[$code]['rejected']['weight'] += $w;
            }
        }

        uasort($clientStats, function ($a, $b) {
            return $b['orders'] <=> $a['orders'];
        });
        $topPicksClientsFull = array_slice($clientStats, 0, 15, true);
        $topPicksClients = array_map(function ($c) {
            return $c['orders'];
        }, $topPicksClientsFull);

        uasort($clientStats, function ($a, $b) {
            return $a['orders'] <=> $b['orders'];
        });
        $leastPicksClientsFull = array_slice($clientStats, 0, 15, true);
        $leastPicksClients = array_map(function ($c) {
            return $c['orders'];
        }, $leastPicksClientsFull);

        // Get quick payments (recent purchase orders with pending payment)
        $quickPayments = PurchaseOrder::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->count();

        // Get overdue payments (purchase orders past due date)
        $overduePayments = PurchaseOrder::where('status', 'pending')
            ->where('due_date', '<', now())
            ->count();

        // Finance total (calculate from purchase order items)
        $purchaseOrders = PurchaseOrder::all();
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

        return view('super-admin.dashboard', compact(
            'buyersCount',
            'craftsmenCount',
            'productsCount',
            'designsCount',
            'workOrdersCount',
            'purchaseOrdersCount',
            'usersCount',
            'keyUsersCount',
            'cataloguesCount',
            'productsWithDesignsCount',
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
     * Get real-time dashboard statistics
     */
    public function getDashboardStats()
    {
        // Get real-time counts for dashboard
        $totalBusinessPartners = Buyer::count() + Craftman::count();
        $totalBuyers = Buyer::count();
        $totalCraftsmen = Craftman::count();
        // $pendingKycCount = Buyer::where('kyc_status', 'pending')->count();
        $totalAdmins = ProcessOwner::where('role', 'admin')->count();
        $totalKeyUsers = KeyUser::count();
        $totalUsers = User::count();
        // Count products that have designs
        $totalProductsWithDesigns = \App\Models\Product::whereHas('designs')->count();
        $totalWorkOrders = WorkOrder::where('status', 'new')->count();
        $totalPurchaseOrders = PurchaseOrder::where('status', 'created')
            ->whereNull('allocated_craftsman_code')
            ->count();
        $totalDesigns = Product::notFromFrozenAccounts()
            ->whereNotNull('type')
            ->where('design_status', 'Accepted')
            ->count();
        $totalStockOrders = StockOrder::where('status', 'Pending')->count();
        $totalRepairs = Repair::whereIn('status', ['Pending', 'Accepted'])->count();

        // Calculate finance total for API response
        $purchaseOrders = PurchaseOrder::all();
        $apiFinanceTotal = 0;

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
                            $apiFinanceTotal += ($item['quantity'] * $item['rate']);
                        }
                    }
                }
            }
        }

        return response()->json([
            'totalBusinessPartners' => $totalBusinessPartners,
            'totalBuyers' => $totalBuyers,
            'totalCraftsmen' => $totalCraftsmen,
            'pendingKycCount' => 0,
            'totalAdmins' => $totalAdmins,
            'totalKeyUsers' => $totalKeyUsers,
            'totalUsers' => $totalUsers,
            'totalWorkOrders' => $totalWorkOrders,
            'totalProducts' => $totalProducts,
            'totalDesigns' => $totalDesigns,
            'totalCatalogues' => $totalCatalogues,
            'totalPurchaseOrders' => $totalPurchaseOrders,
            'totalStockOrders' => $totalStockOrders,
            'totalRepairs' => $totalRepairs,
            'financeTotal' => $apiFinanceTotal
        ]);
    }

    /**
     * Show the finance page (dummy for now)
     */
    public function finance()
    {
        return view('super-admin.finance.index');
    }

    /**
     * Get calendar data for orders and holidays
     */
    public function getCalendarData(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // Fetch Orders
        $workOrders = \App\Models\WorkOrder::whereBetween('created_at', [$startDate, $endDate])->get();
        $purchaseOrders = \App\Models\PurchaseOrder::whereBetween('created_at', [$startDate, $endDate])->get();
        $stockOrders = \App\Models\StockOrder::whereBetween('created_at', [$startDate, $endDate])->get();

        $events = [];
        
        // Initialize days
        $tempDate = $startDate->copy();
        while ($tempDate->lte($endDate)) {
            $date = $tempDate->format('Y-m-d');
            $events[$date] = [
                'work_orders' => ['new' => 0, 'allocated' => 0, 'in_process' => 0, 'completed' => 0, 'overdue' => 0, 'for_approval' => 0, 'rejected' => 0],
                'purchase_orders' => ['new' => 0, 'allocated' => 0, 'in_process' => 0, 'completed' => 0, 'overdue' => 0, 'for_approval' => 0, 'rejected' => 0],
                'stock_orders' => 0,
                'holidays' => []
            ];
            
            if ($tempDate->isSunday()) {
                $events[$date]['holidays'][] = 'Sunday Leave';
            }
            $tempDate->addDay();
        }

        $holidays = [
            '2026-01-26' => 'Republic Day',
            '2026-08-15' => 'Independence Day',
            '2026-10-02' => 'Gandhi Jayanti',
        ];

        foreach ($holidays as $date => $title) {
            if (\Carbon\Carbon::parse($date)->between($startDate, $endDate)) {
                if (isset($events[$date])) {
                    $events[$date]['holidays'][] = $title;
                }
            }
        }

        // Aggregate Work Orders
        foreach ($workOrders as $wo) {
            $date = $wo->created_at->format('Y-m-d');
            if (isset($events[$date])) {
                if (!$wo->craftsman_status || $wo->craftsman_status == 'new') $events[$date]['work_orders']['new']++;
                elseif ($wo->craftsman_status == 'allocated') $events[$date]['work_orders']['allocated']++;
                elseif ($wo->craftsman_status == 'in_process') $events[$date]['work_orders']['in_process']++;
                elseif ($wo->craftsman_status == 'rejected') $events[$date]['work_orders']['rejected']++;
                
                if ($wo->craftsman_status == 'completed' || $wo->status == 'completed') $events[$date]['work_orders']['completed']++;
                if ($wo->status == 'for_approval') $events[$date]['work_orders']['for_approval']++;
                if ($wo->isOverdue()) $events[$date]['work_orders']['overdue']++;
            }
        }

        // Aggregate Purchase Orders
        foreach ($purchaseOrders as $po) {
            $date = $po->created_at->format('Y-m-d');
            if (isset($events[$date])) {
                if (!$po->craftsman_status || $po->craftsman_status == 'new') $events[$date]['purchase_orders']['new']++;
                elseif ($po->craftsman_status == 'allocated') $events[$date]['purchase_orders']['allocated']++;
                elseif ($po->craftsman_status == 'in_process') $events[$date]['purchase_orders']['in_process']++;
                elseif ($po->craftsman_status == 'rejected') $events[$date]['purchase_orders']['rejected']++;
                
                if ($po->craftsman_status == 'completed' || $po->status == 'completed') $events[$date]['purchase_orders']['completed']++;
                if ($po->status == 'for_approval') $events[$date]['purchase_orders']['for_approval']++;
                if ($po->due_date && $po->due_date < now() && $po->status != 'completed') $events[$date]['purchase_orders']['overdue']++;
            }
        }

        // Aggregate Stock Orders
        foreach ($stockOrders as $so) {
            $date = $so->created_at->format('Y-m-d');
            if (isset($events[$date])) {
                $events[$date]['stock_orders']++;
            }
        }
        
        // Remove empty days to save payload size
        $finalEvents = [];
        foreach ($events as $date => $data) {
            $hasData = false;
            foreach ($data['work_orders'] as $v) if($v > 0) $hasData = true;
            foreach ($data['purchase_orders'] as $v) if($v > 0) $hasData = true;
            if ($data['stock_orders'] > 0) $hasData = true;
            if (!empty($data['holidays'])) $hasData = true;
            
            if ($hasData) {
                // Determine event types for the frontend dots
                $types = [];
                if (!empty($data['holidays'])) $types[] = ['type' => 'holiday'];
                
                $hasWorkOrder = false;
                foreach ($data['work_orders'] as $v) if($v > 0) $hasWorkOrder = true;
                if ($hasWorkOrder) $types[] = ['type' => 'work_order'];
                
                $hasPurchaseOrder = false;
                foreach ($data['purchase_orders'] as $v) if($v > 0) $hasPurchaseOrder = true;
                if ($hasPurchaseOrder) $types[] = ['type' => 'purchase_order'];
                
                if ($data['stock_orders'] > 0) $types[] = ['type' => 'stock_order'];
                
                $data['types'] = $types; // Used by frontend calendar rendering to place dots
                $finalEvents[$date] = $data;
            }
        }

        return response()->json($finalEvents);
    }
}