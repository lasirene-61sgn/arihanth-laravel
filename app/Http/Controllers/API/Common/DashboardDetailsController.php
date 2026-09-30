<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Craftman;
use App\Models\Buyer;
use App\Models\KeyUser;
use App\Models\User;
use App\Models\CraftsmanStaff;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardDetailsController extends Controller
{
    /**
     * Get detailed paginated lists for dashboard clicks (e.g. work orders, favorites).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $type = $request->input('type'); // optional: 'work_orders', 'purchase_orders', 'favorites_by_category'
        $status = $request->input('status'); // optional filter for specific status clicks
        $perPage = $request->get('per_page', 10);

        $userType = null;
        if ($user instanceof Buyer || ($user->role ?? '') === 'buyer') $userType = 'buyer';
        elseif ($user instanceof Craftman || ($user->role ?? '') === 'craftsman') $userType = 'craftsman';
        elseif ($user instanceof CraftsmanStaff) $userType = 'craftsman_staff';
        elseif (($user->role ?? '') === 'key_user' || $user instanceof KeyUser || $user instanceof User) $userType = 'key_user';

        // Helper to get favorites
        $getFavorites = function() use ($user, $userType, $request) {
            if (!$userType || !in_array($userType, ['buyer', 'craftsman', 'craftsman_staff', 'key_user'])) {
                return null;
            }
            $query = \App\Models\Favorite::where('favorites.user_id', $user->id)
                ->where('favorites.user_type', $userType)
                ->join('products', 'favorites.product_id', '=', 'products.id')
                ->join('product_categories', 'products.product_category_id', '=', 'product_categories.id')
                ->select(
                    'products.id',
                    'products.design_code',
                    'favorites.design_name',
                    'product_categories.name as category_name',
                    'products.weight_from',
                    'products.weight_to',
                    'products.product_image'
                );
                
            if ($request->has('category_id')) {
                $query->where('products.product_category_id', $request->input('category_id'));
            }
            
            return $query;
        };

        // Helper to get work orders
        $getWorkOrders = function() use ($user, $status) {
            $query = WorkOrder::select('id', 'work_order_number', 'product_name', 'quantity', 'weight_from', 'status', 'craftsman_status', 'due_date', 'created_at', 'bp_code', 'allocated_craftsman_bp_code');
            if ($user instanceof Buyer || ($user->role ?? '') === 'buyer') {
                $query->where('bp_code', $user->bp_code);
            } elseif ($user instanceof Craftman || ($user->role ?? '') === 'craftsman') {
                $query->where('allocated_craftsman_bp_code', $user->craftman_code);
            } elseif ($user instanceof CraftsmanStaff) {
                $query->where('allocated_craftsman_bp_code', $user->craftsman->craftman_code ?? null);
            } elseif ($user instanceof KeyUser || ($user->role ?? '') === 'key_user' || $user instanceof User) {
                $query->where('creator_user_code', $user->user_code);
            } elseif (!in_array($user->role ?? '', ['super_admin', 'admin'])) {
                $query->whereRaw('1 = 0'); // Unauthorized
            }
            if ($status) {
                if ($status === 'in_process' || $status === 'rejected') {
                    $query->where('craftsman_status', $status);
                } else {
                    $query->where('status', $status);
                }
            }
            return $query->orderBy('created_at', 'desc');
        };

        // Helper to get purchase orders
        $getPurchaseOrders = function() use ($user, $status) {
            $query = PurchaseOrder::select('id', 'purchase_order_code', 'status', 'created_at', 'allocated_craftsman_code');
            if ($user instanceof Craftman || ($user->role ?? '') === 'craftsman') {
                $query->where('allocated_craftsman_code', $user->craftman_code);
            } elseif ($user instanceof CraftsmanStaff) {
                $query->where('allocated_craftsman_code', $user->craftsman->craftman_code ?? null);
            } elseif (!in_array($user->role ?? '', ['super_admin', 'admin'])) {
                $query->whereRaw('1 = 0'); // Unauthorized
            }
            if ($status) {
                $query->where('status', $status);
            }
            return $query->orderBy('created_at', 'desc');
        };

        // If no specific type is requested, return an overview WITH ALL stats
        if (!$type) {
            $data = [
                'top_picks_craftsman' => $this->getDetailsOverview($user, 'top_pick_craftsman', $request),
                'top_picks_client' => $this->getDetailsOverview($user, 'top_pick_client', $request),
                'overall_designs' => $this->getDetailsOverview($user, 'overall_designs', $request),
                'craftsman_favorites' => $this->getDetailsOverview($user, 'craftsman_favorites', $request),
                'buyer_favorites' => $this->getDetailsOverview($user, 'buyer_favorites', $request),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        }

        if ($type === 'favorites_by_category') {
            if (!$userType) return response()->json(['success' => false, 'message' => 'Unauthorized for favorites'], 403);
            $query = $getFavorites();

            if ($request->has('print')) {
                return response()->json(['success' => true, 'data' => $query->get()]);
            }
            return response()->json(['success' => true, 'data' => $query->paginate($perPage)]);
        }

        if ($type === 'work_orders') {
            $query = $getWorkOrders();

            if ($request->has('print')) {
                return response()->json(['success' => true, 'data' => $query->get()]);
            }
            return response()->json(['success' => true, 'data' => $query->paginate($perPage)]);
        }

        if ($type === 'purchase_orders') {
            $query = $getPurchaseOrders();

            if ($request->has('print')) {
                return response()->json(['success' => true, 'data' => $query->get()]);
            }
            return response()->json(['success' => true, 'data' => $query->paginate($perPage)]);
        }

        if (in_array($type, ['client_modal_orders', 'craftsman_modal_orders', 'category_modal_designs', 'category_modal_favorites'])) {
            return response()->json([
                'success' => true,
                'data' => $this->getModalData($user, $type, $request, $perPage)
            ]);
        }

        if (!$type || $type === 'all') {
            $types = ['top_pick_client', 'top_pick_craftsman', 'overall_designs', 'craftsman_favorites', 'buyer_favorites'];
            $responseData = [];
            $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
            
            foreach ($types as $t) {
                $dataArray = $this->getDetailsOverview($user, $t, $request);
                $total = count($dataArray);
                $items = array_slice($dataArray, ($page - 1) * $perPage, $perPage);
                
                $responseData[$t] = new \Illuminate\Pagination\LengthAwarePaginator(
                    $items, $total, $perPage, $page, [
                        'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                        'query' => $request->query()
                    ]
                );
            }
            return response()->json(['success' => true, 'data' => $responseData]);
        }

        if (in_array($type, ['top_pick_craftsman', 'top_pick_client', 'overall_designs', 'craftsman_favorites', 'buyer_favorites'])) {
            $dataArray = $this->getDetailsOverview($user, $type, $request);

            if ($request->has('print')) {
                return response()->json(['success' => true, 'data' => $dataArray]);
            }

            $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
            $total = count($dataArray);
            $items = array_slice($dataArray, ($page - 1) * $perPage, $perPage);
            
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $items, $total, $perPage, $page, [
                    'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                    'query' => $request->query()
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $paginator
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid type requested'], 400);
    }

    private function getModalData($user, $type, $request, $perPage)
    {
        $myBuyerCode = null;
        $myCraftsmanCode = null;
        if ($user instanceof Buyer || ($user instanceof User && $user->role == 'buyer')) {
            $myBuyerCode = $user->bp_code;
        } elseif ($user instanceof Craftman) {
            $myCraftsmanCode = $user->craftman_code;
        } elseif ($user instanceof CraftsmanStaff) {
            $myCraftsmanCode = $user->craftsman->craftman_code ?? null;
        }

        if ($type === 'client_modal_orders' || $type === 'craftsman_modal_orders') {
            $targetCode = $request->input('target_code'); 
            $status = $request->input('order_status'); 
            $orderType = $request->input('order_type', 'wo'); 

            if ($orderType === 'wo') {
                $query = WorkOrder::with('craftsman');
                if ($type === 'client_modal_orders') {
                    $query->where('bp_code', $targetCode);
                } else {
                    $query->where('allocated_craftsman_bp_code', $targetCode);
                    if ($myBuyerCode) $query->where('bp_code', $myBuyerCode);
                }

                if ($status === 'new') $query->where(function($q) { $q->whereIn('craftsman_status', ['new', 'allocated'])->orWhereNull('craftsman_status'); });
                elseif ($status === 'in_process') $query->where('craftsman_status', 'in_process');
                elseif ($status === 'for_approval') $query->where('status', 'for_approval');
                elseif ($status === 'completed') $query->where(function($q) { $q->where('craftsman_status', 'completed')->orWhere('status', 'completed'); });
                elseif ($status === 'rejected') $query->where('craftsman_status', 'rejected');
                elseif ($status === 'overdue') $query->where('due_date', '<', now())->where('status', '!=', 'completed');

                if ($request->has('print')) return $query->get();
                $paginator = $query->paginate($perPage);
                $paginator->getCollection()->transform(function($wo) {
                    $w = floatval($wo->weight_to ?: $wo->weight_from);
                    $isWoOverdue = (method_exists($wo, 'isOverdue') && $wo->isOverdue());
                    return [
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
                });
                return $paginator;
            } else {
                $query = PurchaseOrder::with(['buyer', 'craftsman']);
                if ($type === 'client_modal_orders') {
                    return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
                } else {
                    $query->where('allocated_craftsman_code', $targetCode);
                }

                if ($status === 'new') $query->where(function($q) { $q->whereIn('craftsman_status', ['new', 'allocated'])->orWhereNull('craftsman_status'); });
                elseif ($status === 'in_process') $query->where('craftsman_status', 'in_process');
                elseif ($status === 'for_approval') $query->where('status', 'for_approval');
                elseif ($status === 'completed') $query->where(function($q) { $q->where('craftsman_status', 'completed')->orWhere('status', 'completed'); });
                elseif ($status === 'rejected') $query->where('craftsman_status', 'rejected');
                elseif ($status === 'overdue') $query->where('due_date', '<', now())->where('status', '!=', 'completed');

                if ($request->has('print')) return $query->get();
                $paginator = $query->paginate($perPage);
                $paginator->getCollection()->transform(function($po) {
                    $poWeight = 0; $poQty = 0;
                    $items = is_string($po->items) ? json_decode($po->items, true) : ($po->items ?? []);
                    if (is_array($items)) {
                        foreach ($items as $item) {
                            if (isset($item['total'])) $poWeight += floatval($item['total']);
                            elseif (isset($item['grams']) && is_array($item['grams'])) {
                                foreach ($item['grams'] as $i => $gram) {
                                    $poWeight += floatval($gram) * intval($item['quantity'][$i] ?? 1);
                                }
                            }
                            if (isset($item['quantity'])) {
                                $poQty += is_array($item['quantity']) ? array_sum($item['quantity']) : floatval($item['quantity']);
                            }
                        }
                    }
                    $isPoOverdue = ($po->due_date && $po->due_date < now() && $po->status != 'completed');
                    return [
                        'number' => $po->purchase_order_code,
                        'qty' => $poQty,
                        'weight' => $poWeight,
                        'bp_code' => $po->bp_code ?? 'N/A',
                        'business_name' => $po->buyer ? ($po->buyer->name ?? $po->buyer->business_name) : 'Unknown',
                        'due_date' => $po->due_date ? \Carbon\Carbon::parse($po->due_date)->format('Y-m-d') : '-',
                        'craftsman_code' => $po->allocated_craftsman_code ?? 'N/A',
                        'craftsman_name' => $po->craftsman ? ($po->craftsman->name ?? $po->craftsman->business_name) : 'N/A',
                        'overdue_days' => ($isPoOverdue && $po->due_date) ? intval(\Carbon\Carbon::parse($po->due_date)->diffInDays(now())) : 0
                    ];
                });
                return $paginator;
            }
        }

        if ($type === 'category_modal_designs' || $type === 'category_modal_favorites') {
            $catName = $request->input('category_name');
            if ($type === 'category_modal_designs') {
                $query = Product::whereIn('design_status', ['Accepted', 'accepted'])->whereHas('category', function($q) use ($catName) {
                    $q->where('name', $catName);
                });
                if ($myCraftsmanCode) $query->where('bp_code', $myCraftsmanCode);
                if ($myBuyerCode) $query->where('bp_code', $myBuyerCode);
            } else {
                $userType = $request->input('target_user_type', 'craftsman'); 
                $query = Favorite::with('product')->where('user_type', $userType)->whereHas('product.category', function($q) use ($catName) {
                    $q->where('name', $catName);
                });
                if ($userType == 'craftsman' && $myCraftsmanCode) {
                    $targetId = Craftman::where('craftman_code', $myCraftsmanCode)->value('id');
                    $query->where('user_id', $targetId);
                } elseif ($userType == 'buyer' && $myBuyerCode) {
                    $targetId = Buyer::where('bp_code', $myBuyerCode)->value('id');
                    $query->where('user_id', $targetId);
                }
            }

            if ($request->has('print')) return $query->get();
            $paginator = $query->paginate($perPage);
            $paginator->getCollection()->transform(function($item) use ($type) {
                $p = ($type === 'category_modal_designs') ? $item : $item->product;
                if (!$p) return null;
                return [
                    'design_code' => $p->design_code ?? 'N/A',
                    'name' => $p->product_name ?? $p->design_name ?? $p->name ?? 'N/A',
                    'weight_from' => floatval($p->weight_from),
                    'weight_to' => floatval($p->weight_to),
                    'image_url' => $p->image_path ? \Illuminate\Support\Facades\Storage::url($p->image_path) : (filter_var($p->image, FILTER_VALIDATE_URL) ? $p->image : ($p->image ? \Illuminate\Support\Facades\Storage::url($p->image) : null))
                ];
            });
            return $paginator;
        }

        return [];
    }

    private function getDetailsOverview($user, $type, $request)
    {
        $isAdmin = in_array($user->role ?? '', ['super_admin', 'admin']);
        $isCraftsman = $user instanceof \App\Models\Craftman || ($user->role ?? '') === 'craftsman';
        $isStaff = $user instanceof \App\Models\CraftsmanStaff || ($user->role ?? '') === 'craftsman_staff';
        $isBuyer = $user instanceof \App\Models\Buyer || ($user->role ?? '') === 'buyer';
        
        $myCraftsmanCode = null;
        if ($isCraftsman) $myCraftsmanCode = $user->craftman_code;
        if ($isStaff) {
            $staff = $user instanceof \App\Models\CraftsmanStaff ? $user : \App\Models\CraftsmanStaff::with('craftsman')->find($user->id);
            $myCraftsmanCode = $staff->craftsman->craftman_code ?? null;
        }
        
        $myBuyerCode = null;
        if ($isBuyer) $myBuyerCode = $user->bp_code;

        // ==========================================
        // TOP PICK CRAFTSMAN
        // ==========================================
        if ($type === 'top_pick_craftsman') {
            $query = \App\Models\Craftman::query();
            if ($myCraftsmanCode) {
                $query->where('craftman_code', $myCraftsmanCode);
            } elseif ($myBuyerCode) {
                return []; // Buyers don't see craftsman stats
            }
            $craftsmen = $query->get();

            $woQuery = WorkOrder::whereNotNull('allocated_craftsman_bp_code');
            if ($myCraftsmanCode) $woQuery->where('allocated_craftsman_bp_code', $myCraftsmanCode);
            $allWorkOrders = $woQuery->get();

            $poQuery = PurchaseOrder::whereNotNull('allocated_craftsman_code');
            if ($myCraftsmanCode) $poQuery->where('allocated_craftsman_code', $myCraftsmanCode);
            $allPurchaseOrders = $poQuery->get();

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
                    'wo' => [
                        'new' => ['count' => 0, 'weight' => 0, 'orders' => []],
                        'allocated' => ['count' => 0, 'weight' => 0, 'orders' => []],
                        'in_process' => ['count' => 0, 'weight' => 0, 'orders' => []],
                        'completed' => ['count' => 0, 'weight' => 0, 'orders' => []],
                        'overdue' => ['count' => 0, 'weight' => 0, 'orders' => []],
                        'for_approval' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    ],
                    'po' => [
                        'new' => ['count' => 0, 'weight' => 0, 'orders' => []],
                        'allocated' => ['count' => 0, 'weight' => 0, 'orders' => []],
                        'in_process' => ['count' => 0, 'weight' => 0, 'orders' => []],
                        'completed' => ['count' => 0, 'weight' => 0, 'orders' => []],
                        'overdue' => ['count' => 0, 'weight' => 0, 'orders' => []],
                        'for_approval' => ['count' => 0, 'weight' => 0, 'orders' => []],
                    ]
                ];

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
                        'craftsman_code' => $wo->allocated_craftsman_bp_code ?? 'N/A',
                        'craftsman_name' => $wo->craftsman ? ($wo->craftsman->name ?? $wo->craftsman->business_name) : 'N/A',
                        'due_date' => $wo->due_date ? \Carbon\Carbon::parse($wo->due_date)->format('Y-m-d') : '-',
                        'overdue_days' => $isWoOverdue ? intval(\Carbon\Carbon::parse($wo->due_date)->diffInDays(now())) : 0
                    ];

                    if (!$wo->craftsman_status || $wo->craftsman_status == 'new' || $wo->craftsman_status == 'allocated') {
                        $stats['wo']['new']['count']++; $stats['wo']['new']['weight'] += $w; $stats['wo']['new']['orders'][] = $orderDetails; $stats['allocated']++;
                    }
                    if ($wo->craftsman_status == 'in_process') {
                        $stats['wo']['in_process']['count']++; $stats['wo']['in_process']['weight'] += $w; $stats['wo']['in_process']['orders'][] = $orderDetails; $stats['in_process']++;
                    }
                    if ($wo->craftsman_status == 'completed' || $wo->status == 'completed') {
                        $stats['wo']['completed']['count']++; $stats['wo']['completed']['weight'] += $w; $stats['wo']['completed']['orders'][] = $orderDetails; $stats['completed']++;
                    }
                    if ($isWoOverdue) {
                        $stats['wo']['overdue']['count']++; $stats['wo']['overdue']['weight'] += $w; $stats['wo']['overdue']['orders'][] = $orderDetails; $stats['overdue']++;
                    }
                    if ($wo->status == 'for_approval') {
                        $stats['wo']['for_approval']['count']++; $stats['wo']['for_approval']['weight'] += $w; $stats['wo']['for_approval']['orders'][] = $orderDetails; $stats['for_approval']++;
                    }
                    $stats['wa_total_weight'] += $w;
                    $stats['total_weight'] += $w;
                }

                $myPurchaseOrders = $allPurchaseOrders->where('allocated_craftsman_code', $code);
                foreach ($myPurchaseOrders as $po) {
                    $poWeight = 0; $poAmount = 0; $poQty = 0;
                    $items = is_string($po->items) ? json_decode($po->items, true) : ($po->items ?? []);
                    if (is_array($items)) {
                        foreach ($items as $item) {
                            if (isset($item['total'])) $poWeight += floatval($item['total']);
                            elseif (isset($item['grams']) && is_array($item['grams'])) {
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
                        'due_date' => $po->due_date ? \Carbon\Carbon::parse($po->due_date)->format('Y-m-d') : '-',
                        'overdue_days' => $isPoOverdue ? intval(\Carbon\Carbon::parse($po->due_date)->diffInDays(now())) : 0
                    ];

                    if (!$po->craftsman_status || $po->craftsman_status == 'allocated') {
                        $stats['po']['new']['count']++; $stats['po']['new']['weight'] += $poWeight; $stats['po']['new']['orders'][] = $poOrderDetails; $stats['allocated']++;
                    }
                    if ($po->craftsman_status == 'in_process') {
                        $stats['po']['in_process']['count']++; $stats['po']['in_process']['weight'] += $poWeight; $stats['po']['in_process']['orders'][] = $poOrderDetails; $stats['in_process']++;
                    }
                    if ($po->craftsman_status == 'completed' || $po->status == 'completed') {
                        $stats['po']['completed']['count']++; $stats['po']['completed']['weight'] += $poWeight; $stats['po']['completed']['orders'][] = $poOrderDetails; $stats['completed']++;
                    }
                    if ($isPoOverdue) {
                        $stats['po']['overdue']['count']++; $stats['po']['overdue']['weight'] += $poWeight; $stats['po']['overdue']['orders'][] = $poOrderDetails; $stats['overdue']++;
                    }
                    if ($po->status == 'for_approval') {
                        $stats['po']['for_approval']['count']++; $stats['po']['for_approval']['weight'] += $poWeight; $stats['po']['for_approval']['orders'][] = $poOrderDetails; $stats['for_approval']++;
                    }
                    $stats['po_total_weight'] += $poWeight;
                    $stats['total_weight'] += $poWeight;
                    $stats['total_amount'] += $poAmount;
                }
                $craftsmanStats[$code] = $stats;
            }

            $collection = collect($craftsmanStats);
            $status = $request->input('status', 'all');
            if ($status === 'in_process') $collection = $collection->filter(fn($s) => $s['in_process'] > 0);
            elseif ($status === 'for_approval') $collection = $collection->filter(fn($s) => $s['for_approval'] > 0);
            elseif ($status === 'allocated') $collection = $collection->filter(fn($s) => $s['allocated'] > 0);
            elseif ($status === 'completed') $collection = $collection->filter(fn($s) => $s['completed'] > 0);
            elseif ($status === 'overdue') $collection = $collection->filter(fn($s) => $s['overdue'] > 0);

            $sortBy = $request->input('sort_by', 'allocated');
            $sortOrder = $request->input('sort_order', 'desc');
            if ($sortOrder === 'desc') $collection = $collection->sortByDesc($sortBy);
            else $collection = $collection->sortBy($sortBy);

            return array_values($collection->all());
        }

        // ==========================================
        // TOP PICK CLIENT
        // ==========================================
        if ($type === 'top_pick_client') {
            $woQuery = WorkOrder::whereNotNull('bp_code');

            if ($myBuyerCode) {
                $woQuery->where('bp_code', $myBuyerCode);
            } elseif ($myCraftsmanCode) {
                return []; // Craftsmen don't see client list
            }

            $allWorkOrders = $woQuery->get();
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
                    $clientStats[$code]['new']['count']++; $clientStats[$code]['new']['weight'] += $w; $clientStats[$code]['new']['orders'][] = $orderDetails;
                }
                if ($wo->craftsman_status == 'in_process') {
                    $clientStats[$code]['in_process']['count']++; $clientStats[$code]['in_process']['weight'] += $w; $clientStats[$code]['in_process']['orders'][] = $orderDetails;
                }
                if ($wo->status == 'for_approval') {
                    $clientStats[$code]['for_approval']['count']++; $clientStats[$code]['for_approval']['weight'] += $w; $clientStats[$code]['for_approval']['orders'][] = $orderDetails;
                }
                if ($isWoOverdue) {
                    $clientStats[$code]['overdue']['count']++; $clientStats[$code]['overdue']['weight'] += $w; $clientStats[$code]['overdue']['orders'][] = $orderDetails;
                }
                if ($wo->craftsman_status == 'completed' || $wo->status == 'completed') {
                    $clientStats[$code]['completed']['count']++; $clientStats[$code]['completed']['weight'] += $w; $clientStats[$code]['completed']['orders'][] = $orderDetails;
                }
                if ($wo->craftsman_status == 'rejected') {
                    $clientStats[$code]['rejected']['count']++; $clientStats[$code]['rejected']['weight'] += $w; $clientStats[$code]['rejected']['orders'][] = $orderDetails;
                }
            }
            
            uasort($clientStats, function($a, $b) { return $b['orders'] <=> $a['orders']; });
            return array_values(array_slice($clientStats, 0, 15, true));
        }

        // ==========================================
        // OVERALL DESIGNS
        // ==========================================
        if ($type === 'overall_designs') {
            $acceptedProductsQuery = Product::with('category')->whereIn('design_status', ['Accepted', 'accepted']);
            if ($myCraftsmanCode) $acceptedProductsQuery->where('bp_code', $myCraftsmanCode);
            if ($myBuyerCode) $acceptedProductsQuery->where('bp_code', $myBuyerCode);
            
            $acceptedProducts = $acceptedProductsQuery->get();

            $overallDesignStats = [];
            foreach ($acceptedProducts as $p) {
                $catName = $p->category ? $p->category->name : 'Uncategorized';
                if (!isset($overallDesignStats[$catName])) {
                    $overallDesignStats[$catName] = [
                        'count' => 0,
                        'products' => []
                    ];
                }
                $overallDesignStats[$catName]['count']++;
                $overallDesignStats[$catName]['products'][] = [
                    'design_code' => $p->design_code ?? 'N/A',
                    'name' => $p->product_name ?? $p->design_name ?? $p->name ?? 'N/A',
                    'weight_from' => floatval($p->weight_from),
                    'weight_to' => floatval($p->weight_to),
                    'image_url' => $p->image_path ? \Illuminate\Support\Facades\Storage::url($p->image_path) : (filter_var($p->image, FILTER_VALIDATE_URL) ? $p->image : ($p->image ? \Illuminate\Support\Facades\Storage::url($p->image) : null))
                ];
            }
            
            $formattedOverall = [];
            foreach ($overallDesignStats as $cat => $stats) {
                $formattedOverall[] = [
                    'category' => $cat,
                    'count' => $stats['count'],
                    'products' => $stats['products']
                ];
            }
            return $formattedOverall;
        }

        // ==========================================
        // CRAFTSMAN FAVORITES
        // ==========================================
        if ($type === 'craftsman_favorites') {
            if ($myBuyerCode) return []; // Buyers don't see craftsman favorites
            
            $favoritesQuery = \App\Models\Favorite::with(['product.category', 'user'])
                ->where('user_type', 'craftsman');
                
            if ($myCraftsmanCode) {
                // Find user_id for this craftsman
                $craftsmanUserId = \App\Models\Craftman::where('craftman_code', $myCraftsmanCode)->value('id');
                $favoritesQuery->where('user_id', $craftsmanUserId);
            }
            
            $favorites = $favoritesQuery->get();
            $craftsmanFavoritesStats = [];

            foreach ($favorites as $fav) {
                if (!$fav->product || !$fav->product->category) continue;
                $catName = $fav->product->category->name;

                if ($fav->user) {
                    $key = $fav->user->craftman_code;
                    if (!isset($craftsmanFavoritesStats[$key])) {
                        $craftsmanFavoritesStats[$key] = [
                            'name' => $fav->user->name ?? $fav->user->business_name ?? 'Unknown',
                            'code' => $fav->user->craftman_code,
                            'user_id' => $fav->user_id,
                            'categories' => []
                        ];
                    }
                    if (!isset($craftsmanFavoritesStats[$key]['categories'][$catName])) {
                        $craftsmanFavoritesStats[$key]['categories'][$catName] = [
                            'count' => 0,
                            'products' => []
                        ];
                    }
                    $craftsmanFavoritesStats[$key]['categories'][$catName]['count']++;
                    $craftsmanFavoritesStats[$key]['categories'][$catName]['products'][] = [
                        'design_code' => $fav->product->design_code ?? 'N/A',
                        'name' => $fav->product->product_name ?? $fav->product->design_name ?? $fav->product->name ?? 'N/A',
                        'weight_from' => floatval($fav->product->weight_from),
                        'weight_to' => floatval($fav->product->weight_to),
                        'image_url' => $fav->product->image_path ? \Illuminate\Support\Facades\Storage::url($fav->product->image_path) : (filter_var($fav->product->image, FILTER_VALIDATE_URL) ? $fav->product->image : ($fav->product->image ? \Illuminate\Support\Facades\Storage::url($fav->product->image) : null))
                    ];
                }
            }
            return array_values($craftsmanFavoritesStats);
        }

        // ==========================================
        // BUYER FAVORITES
        // ==========================================
        if ($type === 'buyer_favorites') {
            if ($myCraftsmanCode) return []; // Craftsmen don't see buyer favorites
            
            $favoritesQuery = \App\Models\Favorite::with(['product.category', 'user'])
                ->where('user_type', 'buyer');
                
            if ($myBuyerCode) {
                $buyerUserId = \App\Models\Buyer::where('bp_code', $myBuyerCode)->value('id');
                $favoritesQuery->where('user_id', $buyerUserId);
            }

            $favorites = $favoritesQuery->get();
            $buyerFavoritesStats = [];

            foreach ($favorites as $fav) {
                if (!$fav->product || !$fav->product->category) continue;
                $catName = $fav->product->category->name;

                if ($fav->user) {
                    $key = $fav->user->bp_code;
                    if (!isset($buyerFavoritesStats[$key])) {
                        $buyerFavoritesStats[$key] = [
                            'name' => $fav->user->name ?? $fav->user->business_name ?? 'Unknown',
                            'code' => $fav->user->bp_code,
                            'user_id' => $fav->user_id,
                            'categories' => []
                        ];
                    }
                    if (!isset($buyerFavoritesStats[$key]['categories'][$catName])) {
                        $buyerFavoritesStats[$key]['categories'][$catName] = [
                            'count' => 0,
                            'products' => []
                        ];
                    }
                    $buyerFavoritesStats[$key]['categories'][$catName]['count']++;
                    $buyerFavoritesStats[$key]['categories'][$catName]['products'][] = [
                        'design_code' => $fav->product->design_code ?? 'N/A',
                        'name' => $fav->product->product_name ?? $fav->product->design_name ?? $fav->product->name ?? 'N/A',
                        'weight_from' => floatval($fav->product->weight_from),
                        'weight_to' => floatval($fav->product->weight_to),
                        'image_url' => $fav->product->image_path ? \Illuminate\Support\Facades\Storage::url($fav->product->image_path) : (filter_var($fav->product->image, FILTER_VALIDATE_URL) ? $fav->product->image : ($fav->product->image ? \Illuminate\Support\Facades\Storage::url($fav->product->image) : null))
                    ];
                }
            }
            return array_values($buyerFavoritesStats);
        }

        return [];
    }
}
