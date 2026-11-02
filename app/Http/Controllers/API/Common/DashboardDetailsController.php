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
use App\Models\Favorite;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DashboardDetailsController extends Controller
{
    /**
     * Get detailed paginated lists for dashboard clicks (e.g. work orders, favorites).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $type = $request->input('type'); // optional: 'work_orders', 'purchase_orders', 'favorites_by_category', 'all', etc.
        $status = $request->input('status'); // optional filter for specific status clicks
        $perPage = (int) $request->input('per_page', 10);
        if ($perPage < 1) {
            $perPage = 10;
        }

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
            $query = Favorite::where('favorites.user_id', $user->id)
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

        if ($type === 'favorites_by_category') {
            if (!$userType) return response()->json(['success' => false, 'message' => 'Unauthorized for favorites'], 403);
            $query = $getFavorites();

            if ($request->has('print')) {
                return response()->json(['success' => true, 'data' => $query->get()]);
            }
            $paginator = $query->paginate($perPage);
            $paginator->appends($request->query());
            return response()->json(['success' => true, 'data' => $paginator]);
        }

        if ($type === 'work_orders') {
            $query = $getWorkOrders();

            if ($request->has('print')) {
                return response()->json(['success' => true, 'data' => $query->get()]);
            }
            $paginator = $query->paginate($perPage);
            $paginator->appends($request->query());
            return response()->json(['success' => true, 'data' => $paginator]);
        }

        if ($type === 'purchase_orders') {
            $query = $getPurchaseOrders();

            if ($request->has('print')) {
                return response()->json(['success' => true, 'data' => $query->get()]);
            }
            $paginator = $query->paginate($perPage);
            $paginator->appends($request->query());
            return response()->json(['success' => true, 'data' => $paginator]);
        }

        // =====================================================================
        // MODAL / DRILLDOWN CLICK DATA PAGINATION
        // =====================================================================
        $modalTypeAliases = [
            'client_modal_orders' => 'client_modal_orders',
            'client_orders' => 'client_modal_orders',
            'top_picks_client_orders' => 'client_modal_orders',
            'top_pick_client_orders' => 'client_modal_orders',
            'craftsman_modal_orders' => 'craftsman_modal_orders',
            'craftsman_orders' => 'craftsman_modal_orders',
            'top_picks_craftsman_orders' => 'craftsman_modal_orders',
            'top_pick_craftsman_orders' => 'craftsman_modal_orders',
            'category_modal_designs' => 'category_modal_designs',
            'category_designs' => 'category_modal_designs',
            'overall_designs_modal' => 'category_modal_designs',
            'overall_designs_click' => 'category_modal_designs',
            'category_modal_favorites' => 'category_modal_favorites',
            'category_favorites' => 'category_modal_favorites',
            'craftsman_favorites_modal' => 'category_modal_favorites',
            'buyer_favorites_modal' => 'category_modal_favorites',
            'favorites_modal' => 'category_modal_favorites',
        ];

        if (isset($modalTypeAliases[$type])) {
            $modalType = $modalTypeAliases[$type];
            if ($type === 'craftsman_favorites_modal' && !$request->has('target_user_type')) {
                $request->merge(['target_user_type' => 'craftsman']);
            } elseif ($type === 'buyer_favorites_modal' && !$request->has('target_user_type')) {
                $request->merge(['target_user_type' => 'buyer']);
            }

            return response()->json([
                'success' => true,
                'data' => $this->getModalData($user, $modalType, $request, $perPage)
            ]);
        }

        // =====================================================================
        // ALL OVERVIEW SECTIONS WITH PROPER PAGINATION
        // =====================================================================
        if (!$type || $type === 'all') {
            $types = [
                'top_picks_client' => 'top_pick_client',
                'top_picks_craftsman' => 'top_pick_craftsman',
                'overall_designs' => 'overall_designs',
                'craftsman_favorites' => 'craftsman_favorites',
                'buyer_favorites' => 'buyer_favorites'
            ];
            $responseData = [];

            foreach ($types as $key => $t) {
                $dataArray = $this->getDetailsOverview($user, $t, $request);

                if ($request->has('print')) {
                    $responseData[$key] = $dataArray;
                    continue;
                }

                $keyPage = (int) ($request->input("{$key}_page") ?? $request->input("page_{$key}") ?? $request->input('page', 1));
                if ($keyPage < 1) $keyPage = 1;
                $keyPerPage = (int) ($request->input("{$key}_per_page") ?? $request->input("per_page_{$key}") ?? $perPage);
                if ($keyPerPage < 1) $keyPerPage = 10;

                $total = count($dataArray);
                $items = array_values(array_slice($dataArray, ($keyPage - 1) * $keyPerPage, $keyPerPage));
                $pageName = $request->has("{$key}_page") ? "{$key}_page" : ($request->has("page_{$key}") ? "page_{$key}" : 'page');

                $paginator = new LengthAwarePaginator(
                    $items, $total, $keyPerPage, $keyPage, [
                        'path' => Paginator::resolveCurrentPath(),
                        'query' => $request->query(),
                        'pageName' => $pageName
                    ]
                );

                $responseData[$key] = $paginator;
            }
            return response()->json(['success' => true, 'data' => $responseData]);
        }

        // =====================================================================
        // INDIVIDUAL OVERVIEW TYPE WITH PAGINATION (OR DRILLDOWN IF FILTER GIVEN)
        // =====================================================================
        $overviewTypeAliases = [
            'top_picks_client' => 'top_pick_client',
            'top_picks_clients' => 'top_pick_client',
            'top_pick_client' => 'top_pick_client',
            'top_picks_craftsman' => 'top_pick_craftsman',
            'top_picks_craftsmen' => 'top_pick_craftsman',
            'top_pick_craftsman' => 'top_pick_craftsman',
            'overall_designs' => 'overall_designs',
            'overall_design' => 'overall_designs',
            'craftsman_favorites' => 'craftsman_favorites',
            'craftsman_favorite' => 'craftsman_favorites',
            'buyer_favorites' => 'buyer_favorites',
            'buyer_favorite' => 'buyer_favorites'
        ];

        if (isset($overviewTypeAliases[$type])) {
            $resolvedType = $overviewTypeAliases[$type];

            // If click/drilldown parameters are present, seamlessly return the paginated click data
            if ($resolvedType === 'top_pick_client' && ($request->has('target_code') || $request->has('code') || $request->has('bp_code') || $request->has('client_code') || $request->has('order_status'))) {
                return response()->json([
                    'success' => true,
                    'data' => $this->getModalData($user, 'client_modal_orders', $request, $perPage)
                ]);
            }
            if ($resolvedType === 'top_pick_craftsman' && ($request->has('target_code') || $request->has('code') || $request->has('craftsman_code') || $request->has('craftman_code') || $request->has('order_status') || $request->has('order_type'))) {
                return response()->json([
                    'success' => true,
                    'data' => $this->getModalData($user, 'craftsman_modal_orders', $request, $perPage)
                ]);
            }
            if ($resolvedType === 'overall_designs' && ($request->has('category_name') || $request->has('category_id') || $request->has('category'))) {
                return response()->json([
                    'success' => true,
                    'data' => $this->getModalData($user, 'category_modal_designs', $request, $perPage)
                ]);
            }
            if ($resolvedType === 'craftsman_favorites' && ($request->has('category_name') || $request->has('category_id') || $request->has('category') || $request->has('target_code') || $request->has('craftsman_code') || $request->has('user_id'))) {
                if (!$request->has('target_user_type')) $request->merge(['target_user_type' => 'craftsman']);
                return response()->json([
                    'success' => true,
                    'data' => $this->getModalData($user, 'category_modal_favorites', $request, $perPage)
                ]);
            }
            if ($resolvedType === 'buyer_favorites' && ($request->has('category_name') || $request->has('category_id') || $request->has('category') || $request->has('target_code') || $request->has('bp_code') || $request->has('user_id'))) {
                if (!$request->has('target_user_type')) $request->merge(['target_user_type' => 'buyer']);
                return response()->json([
                    'success' => true,
                    'data' => $this->getModalData($user, 'category_modal_favorites', $request, $perPage)
                ]);
            }

            // Normal overview list for this type
            $dataArray = $this->getDetailsOverview($user, $resolvedType, $request);

            if ($request->has('print')) {
                return response()->json(['success' => true, 'data' => $dataArray]);
            }

            $page = (int) $request->input('page', 1);
            if ($page < 1) $page = 1;
            $total = count($dataArray);
            $items = array_values(array_slice($dataArray, ($page - 1) * $perPage, $perPage));

            $paginator = new LengthAwarePaginator(
                $items, $total, $perPage, $page, [
                    'path' => Paginator::resolveCurrentPath(),
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

    /**
     * Get paginated modal / click drill-down data
     */
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

        // =====================================================================
        // CLIENT & CRAFTSMAN MODAL ORDERS (Click on badge / status)
        // =====================================================================
        if ($type === 'client_modal_orders' || $type === 'craftsman_modal_orders') {
            $targetCode = $request->input('target_code') 
                ?? $request->input('code') 
                ?? $request->input('bp_code') 
                ?? $request->input('craftsman_code') 
                ?? $request->input('craftman_code') 
                ?? $request->input('client_code'); 
            $status = $request->input('order_status') ?? $request->input('status'); 
            $orderType = strtolower($request->input('order_type', 'wo')); 

            if ($orderType === 'wo') {
                $query = WorkOrder::with('craftsman');

                if ($type === 'client_modal_orders') {
                    if ($targetCode) $query->where('bp_code', $targetCode);
                    if ($myBuyerCode) $query->where('bp_code', $myBuyerCode);
                } else {
                    if ($targetCode) $query->where('allocated_craftsman_bp_code', $targetCode);
                    if ($myCraftsmanCode) $query->where('allocated_craftsman_bp_code', $myCraftsmanCode);
                    if ($myBuyerCode) $query->where('bp_code', $myBuyerCode);
                }

                if ($status === 'new' || $status === 'allocated') {
                    $query->where(function($q) { 
                        $q->whereIn('craftsman_status', ['new', 'allocated'])
                          ->orWhereNull('craftsman_status'); 
                    });
                } elseif ($status === 'in_process') {
                    $query->where('craftsman_status', 'in_process');
                } elseif ($status === 'for_approval') {
                    $query->where('status', 'for_approval');
                } elseif ($status === 'completed') {
                    $query->where(function($q) { 
                        $q->where('craftsman_status', 'completed')
                          ->orWhere('status', 'completed'); 
                    });
                } elseif ($status === 'rejected') {
                    $query->where('craftsman_status', 'rejected');
                } elseif ($status === 'overdue') {
                    $query->where('due_date', '<', now())
                          ->where(function($q) { 
                              $q->whereNull('status')->orWhere('status', '!=', 'completed'); 
                          });
                }

                $query->orderBy('created_at', 'desc');

                if ($request->has('print')) return $query->get();
                $paginator = $query->paginate($perPage);
                $paginator->appends($request->query());
                $paginator->getCollection()->transform(function($wo) {
                    $w = floatval($wo->weight_to ?: $wo->weight_from);
                    $isWoOverdue = (method_exists($wo, 'isOverdue') && $wo->isOverdue());
                    $dueDateFormatted = $wo->due_date ? Carbon::parse($wo->due_date)->format('Y-m-d') : '-';
                    $overdueDays = 0;
                    if ($isWoOverdue && $wo->due_date) {
                        $overdueDays = Carbon::now()->startOfDay()->diffInDays(Carbon::parse($wo->due_date)->startOfDay());
                    }

                    return [
                        'id' => $wo->id,
                        'number' => $wo->work_order_number,
                        'product_name' => $wo->product_name ?? '-',
                        'qty' => $wo->quantity,
                        'weight' => $w,
                        'bp_code' => $wo->bp_code,
                        'business_name' => $wo->customer_name,
                        'status' => $wo->status,
                        'craftsman_status' => $wo->craftsman_status,
                        'due_date' => $dueDateFormatted,
                        'craftsman_code' => $wo->allocated_craftsman_bp_code ?? 'N/A',
                        'craftsman_name' => $wo->craftsman ? ($wo->craftsman->name ?? $wo->craftsman->business_name) : 'N/A',
                        'overdue_days' => $overdueDays
                    ];
                });
                return $paginator;
            } else {
                $query = PurchaseOrder::with('craftsman');

                if ($type === 'client_modal_orders') {
                    $emptyPaginator = new LengthAwarePaginator([], 0, $perPage);
                    $emptyPaginator->appends($request->query());
                    return $emptyPaginator;
                } else {
                    if ($targetCode) $query->where('allocated_craftsman_code', $targetCode);
                    if ($myCraftsmanCode) $query->where('allocated_craftsman_code', $myCraftsmanCode);
                }

                if ($status === 'new' || $status === 'allocated') {
                    $query->where(function($q) { 
                        $q->whereIn('craftsman_status', ['new', 'allocated'])
                          ->orWhereNull('craftsman_status'); 
                    });
                } elseif ($status === 'in_process') {
                    $query->where('craftsman_status', 'in_process');
                } elseif ($status === 'for_approval') {
                    $query->where('status', 'for_approval');
                } elseif ($status === 'completed') {
                    $query->where(function($q) { 
                        $q->where('craftsman_status', 'completed')
                          ->orWhere('status', 'completed'); 
                    });
                } elseif ($status === 'rejected') {
                    $query->where('craftsman_status', 'rejected');
                } elseif ($status === 'overdue') {
                    $query->where('due_date', '<', now())->where('status', '!=', 'completed');
                }

                $query->orderBy('created_at', 'desc');

                if ($request->has('print')) return $query->get();
                $paginator = $query->paginate($perPage);
                $paginator->appends($request->query());
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
                        'id' => $po->id,
                        'number' => $po->purchase_order_code,
                        'qty' => $poQty,
                        'weight' => $poWeight,
                        'bp_code' => $po->bp_code ?? '-',
                        'business_name' => '-',
                        'status' => $po->status,
                        'craftsman_status' => $po->craftsman_status,
                        'due_date' => $po->due_date ? Carbon::parse($po->due_date)->format('Y-m-d') : '-',
                        'craftsman_code' => $po->allocated_craftsman_code ?? 'N/A',
                        'craftsman_name' => $po->craftsman ? ($po->craftsman->name ?? $po->craftsman->business_name) : 'N/A',
                        'overdue_days' => ($isPoOverdue && $po->due_date) ? intval(Carbon::parse($po->due_date)->diffInDays(now())) : 0
                    ];
                });
                return $paginator;
            }
        }

        // =====================================================================
        // CATEGORY DESIGNS & CATEGORY FAVORITES (Click on category)
        // =====================================================================
        if ($type === 'category_modal_designs' || $type === 'category_modal_favorites') {
            $catName = $request->input('category_name') ?? $request->input('category') ?? $request->input('name');
            $catId = $request->input('category_id');
            $targetCode = $request->input('target_code') ?? $request->input('code') ?? $request->input('bp_code') ?? $request->input('craftsman_code') ?? $request->input('craftman_code');

            if ($type === 'category_modal_designs') {
                $query = Product::with('category')->whereIn('design_status', ['Accepted', 'accepted']);

                if ($catId) {
                    $query->where('product_category_id', $catId);
                } elseif ($catName && $catName !== 'all') {
                    $query->whereHas('category', function($q) use ($catName) {
                        $q->where('name', $catName);
                    });
                }

                if ($targetCode) $query->where('bp_code', $targetCode);
                if ($myCraftsmanCode) $query->where('bp_code', $myCraftsmanCode);
                if ($myBuyerCode) $query->where('bp_code', $myBuyerCode);

                $query->orderBy('updated_at', 'desc');

                if ($request->has('print')) return $query->get();
                $paginator = $query->paginate($perPage);
                $paginator->appends($request->query());
                $paginator->getCollection()->transform(function($p) {
                    if (!$p) return null;
                    $imageUrl = $p->image_url;
                    if (!$imageUrl && $p->product_image) {
                        $imageUrl = filter_var($p->product_image, FILTER_VALIDATE_URL) ? $p->product_image : Storage::url($p->product_image);
                    } elseif (!$imageUrl && $p->image_path) {
                        $imageUrl = Storage::url($p->image_path);
                    } elseif (!$imageUrl && $p->image) {
                        $imageUrl = filter_var($p->image, FILTER_VALIDATE_URL) ? $p->image : Storage::url($p->image);
                    }
                    return [
                        'id' => $p->id,
                        'design_code' => $p->design_code ?? 'N/A',
                        'name' => $p->product_name ?? $p->design_name ?? $p->name ?? 'N/A',
                        'category_name' => $p->category->name ?? 'Uncategorized',
                        'category_id' => $p->product_category_id,
                        'weight_from' => floatval($p->weight_from),
                        'weight_to' => floatval($p->weight_to),
                        'image_url' => $imageUrl
                    ];
                });
                return $paginator;
            } else {
                $userType = $request->input('target_user_type') ?? $request->input('user_type', 'craftsman'); 
                $query = Favorite::with(['product.category', 'user'])->where('user_type', $userType);

                if ($catId) {
                    $query->whereHas('product', function($q) use ($catId) {
                        $q->where('product_category_id', $catId);
                    });
                } elseif ($catName && $catName !== 'all') {
                    $query->whereHas('product.category', function($q) use ($catName) {
                        $q->where('name', $catName);
                    });
                }

                if ($userType == 'craftsman') {
                    if ($myCraftsmanCode) {
                        $targetId = Craftman::where('craftman_code', $myCraftsmanCode)->value('id');
                        $query->where('user_id', $targetId);
                    } elseif ($targetCode) {
                        $targetId = Craftman::where('craftman_code', $targetCode)->value('id');
                        if ($targetId) $query->where('user_id', $targetId);
                    } elseif ($request->has('user_id')) {
                        $query->where('user_id', $request->input('user_id'));
                    }
                } elseif ($userType == 'buyer') {
                    if ($myBuyerCode) {
                        $targetId = Buyer::where('bp_code', $myBuyerCode)->value('id');
                        $query->where('user_id', $targetId);
                    } elseif ($targetCode) {
                        $targetId = Buyer::where('bp_code', $targetCode)->value('id');
                        if ($targetId) $query->where('user_id', $targetId);
                    } elseif ($request->has('user_id')) {
                        $query->where('user_id', $request->input('user_id'));
                    }
                }

                $query->latest();

                if ($request->has('print')) return $query->get();
                $paginator = $query->paginate($perPage);
                $paginator->appends($request->query());
                $paginator->getCollection()->transform(function($fav) {
                    $p = $fav->product;
                    if (!$p) return null;
                    $imageUrl = $p->image_url;
                    if (!$imageUrl && $p->product_image) {
                        $imageUrl = filter_var($p->product_image, FILTER_VALIDATE_URL) ? $p->product_image : Storage::url($p->product_image);
                    } elseif (!$imageUrl && $p->image_path) {
                        $imageUrl = Storage::url($p->image_path);
                    } elseif (!$imageUrl && $p->image) {
                        $imageUrl = filter_var($p->image, FILTER_VALIDATE_URL) ? $p->image : Storage::url($p->image);
                    }
                    return [
                        'id' => $fav->id,
                        'product_id' => $p->id,
                        'design_code' => $p->design_code ?? 'N/A',
                        'name' => $p->product_name ?? $p->design_name ?? $p->name ?? 'N/A',
                        'category_name' => $p->category->name ?? 'Uncategorized',
                        'category_id' => $p->product_category_id,
                        'weight_from' => floatval($p->weight_from),
                        'weight_to' => floatval($p->weight_to),
                        'image_url' => $imageUrl
                    ];
                });
                return $paginator;
            }
        }

        $emptyPaginator = new LengthAwarePaginator([], 0, $perPage);
        $emptyPaginator->appends($request->query());
        return $emptyPaginator;
    }

    /**
     * Build overview dataset for a specific overview type
     */
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
                    'craftsman_code' => $code,
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
                        'id' => $wo->id,
                        'number' => $wo->work_order_number,
                        'qty' => $wo->quantity,
                        'weight' => $w,
                        'bp_code' => $wo->bp_code,
                        'business_name' => $wo->customer_name,
                        'status' => $wo->status,
                        'craftsman_status' => $wo->craftsman_status,
                        'craftsman_code' => $wo->allocated_craftsman_bp_code ?? 'N/A',
                        'craftsman_name' => $wo->craftsman ? ($wo->craftsman->name ?? $wo->craftsman->business_name) : 'N/A',
                        'due_date' => $wo->due_date ? Carbon::parse($wo->due_date)->format('Y-m-d') : '-',
                        'overdue_days' => $isWoOverdue ? intval(Carbon::parse($wo->due_date)->diffInDays(now())) : 0
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
                        'id' => $po->id,
                        'number' => $po->purchase_order_code,
                        'qty' => $poQty,
                        'weight' => $poWeight,
                        'bp_code' => '-',
                        'business_name' => '-',
                        'status' => $po->status,
                        'craftsman_status' => $po->craftsman_status,
                        'due_date' => $po->due_date ? Carbon::parse($po->due_date)->format('Y-m-d') : '-',
                        'craftsman_code' => $po->allocated_craftsman_code ?? 'N/A',
                        'craftsman_name' => $c->name ?? $c->business_name ?? 'N/A',
                        'overdue_days' => $isPoOverdue ? intval(Carbon::parse($po->due_date)->diffInDays(now())) : 0
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
                        'code' => $code,
                        'bp_code' => $code,
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
                    'id' => $wo->id,
                    'number' => $wo->work_order_number,
                    'qty' => $wo->quantity,
                    'weight' => $w,
                    'bp_code' => $wo->bp_code,
                    'business_name' => $wo->customer_name,
                    'status' => $wo->status,
                    'craftsman_status' => $wo->craftsman_status,
                    'due_date' => $wo->due_date ? Carbon::parse($wo->due_date)->format('Y-m-d') : '-',
                    'craftsman_code' => $wo->allocated_craftsman_bp_code ?? 'N/A',
                    'craftsman_name' => $wo->craftsman ? ($wo->craftsman->name ?? $wo->craftsman->business_name) : 'N/A',
                    'overdue_days' => ($isWoOverdue && $wo->due_date) ? Carbon::now()->startOfDay()->diffInDays(Carbon::parse($wo->due_date)->startOfDay()) : 0
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

            $clientCollection = collect($clientStats);
            $cStatus = $request->input('status', 'all');
            if ($cStatus === 'new') $clientCollection = $clientCollection->filter(fn($s) => ($s['new']['count'] ?? 0) > 0);
            elseif ($cStatus === 'in_process') $clientCollection = $clientCollection->filter(fn($s) => ($s['in_process']['count'] ?? 0) > 0);
            elseif ($cStatus === 'for_approval') $clientCollection = $clientCollection->filter(fn($s) => ($s['for_approval']['count'] ?? 0) > 0);
            elseif ($cStatus === 'overdue') $clientCollection = $clientCollection->filter(fn($s) => ($s['overdue']['count'] ?? 0) > 0);
            elseif ($cStatus === 'completed') $clientCollection = $clientCollection->filter(fn($s) => ($s['completed']['count'] ?? 0) > 0);
            elseif ($cStatus === 'rejected') $clientCollection = $clientCollection->filter(fn($s) => ($s['rejected']['count'] ?? 0) > 0);

            $sortBy = $request->input('sort_by', 'orders');
            $sortOrder = $request->input('sort_order', 'desc');
            if ($sortOrder === 'desc') $clientCollection = $clientCollection->sortByDesc($sortBy);
            else $clientCollection = $clientCollection->sortBy($sortBy);

            return array_values($clientCollection->all());
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
                $catId = $p->product_category_id;
                if (!isset($overallDesignStats[$catName])) {
                    $overallDesignStats[$catName] = [
                        'category' => $catName,
                        'category_id' => $catId,
                        'count' => 0,
                        'products' => []
                    ];
                }
                $overallDesignStats[$catName]['count']++;
                
                $imageUrl = $p->image_url;
                if (!$imageUrl && $p->product_image) {
                    $imageUrl = filter_var($p->product_image, FILTER_VALIDATE_URL) ? $p->product_image : Storage::url($p->product_image);
                } elseif (!$imageUrl && $p->image_path) {
                    $imageUrl = Storage::url($p->image_path);
                } elseif (!$imageUrl && $p->image) {
                    $imageUrl = filter_var($p->image, FILTER_VALIDATE_URL) ? $p->image : Storage::url($p->image);
                }

                $overallDesignStats[$catName]['products'][] = [
                    'id' => $p->id,
                    'design_code' => $p->design_code ?? 'N/A',
                    'name' => $p->product_name ?? $p->design_name ?? $p->name ?? 'N/A',
                    'category_name' => $catName,
                    'category_id' => $catId,
                    'weight_from' => floatval($p->weight_from),
                    'weight_to' => floatval($p->weight_to),
                    'image_url' => $imageUrl
                ];
            }
            
            $formattedOverall = [];
            foreach ($overallDesignStats as $cat => $stats) {
                $formattedOverall[] = [
                    'category' => $cat,
                    'category_id' => $stats['category_id'] ?? null,
                    'count' => $stats['count'],
                    'products' => $stats['products']
                ];
            }
            usort($formattedOverall, fn($a, $b) => $b['count'] <=> $a['count']);
            return $formattedOverall;
        }

        // ==========================================
        // CRAFTSMAN FAVORITES
        // ==========================================
        if ($type === 'craftsman_favorites') {
            if ($myBuyerCode) return []; // Buyers don't see craftsman favorites
            
            $favoritesQuery = Favorite::with(['product.category', 'user'])
                ->where('user_type', 'craftsman');
                
            if ($myCraftsmanCode) {
                $craftsmanUserId = Craftman::where('craftman_code', $myCraftsmanCode)->value('id');
                $favoritesQuery->where('user_id', $craftsmanUserId);
            } elseif ($request->has('target_code') || $request->has('craftsman_code')) {
                $cCode = $request->input('target_code') ?? $request->input('craftsman_code');
                $craftsmanUserId = Craftman::where('craftman_code', $cCode)->value('id');
                if ($craftsmanUserId) $favoritesQuery->where('user_id', $craftsmanUserId);
            } elseif ($request->has('user_id')) {
                $favoritesQuery->where('user_id', $request->input('user_id'));
            }
            
            $favorites = $favoritesQuery->get();
            $craftsmanFavoritesStats = [];

            foreach ($favorites as $fav) {
                if (!$fav->product || !$fav->product->category) continue;
                $catName = $fav->product->category->name;
                $p = $fav->product;

                if ($fav->user) {
                    $key = $fav->user->craftman_code ?? ('user_' . $fav->user_id);
                    if (!isset($craftsmanFavoritesStats[$key])) {
                        $craftsmanFavoritesStats[$key] = [
                            'name' => $fav->user->name ?? $fav->user->business_name ?? 'Unknown',
                            'code' => $fav->user->craftman_code ?? null,
                            'craftsman_code' => $fav->user->craftman_code ?? null,
                            'user_id' => $fav->user_id,
                            'total_favorites' => 0,
                            'categories' => []
                        ];
                    }
                    if (!isset($craftsmanFavoritesStats[$key]['categories'][$catName])) {
                        $craftsmanFavoritesStats[$key]['categories'][$catName] = [
                            'category' => $catName,
                            'category_id' => $p->product_category_id,
                            'count' => 0,
                            'products' => []
                        ];
                    }
                    $craftsmanFavoritesStats[$key]['total_favorites']++;
                    $craftsmanFavoritesStats[$key]['categories'][$catName]['count']++;

                    $imageUrl = $p->image_url;
                    if (!$imageUrl && $p->product_image) {
                        $imageUrl = filter_var($p->product_image, FILTER_VALIDATE_URL) ? $p->product_image : Storage::url($p->product_image);
                    } elseif (!$imageUrl && $p->image_path) {
                        $imageUrl = Storage::url($p->image_path);
                    } elseif (!$imageUrl && $p->image) {
                        $imageUrl = filter_var($p->image, FILTER_VALIDATE_URL) ? $p->image : Storage::url($p->image);
                    }

                    $craftsmanFavoritesStats[$key]['categories'][$catName]['products'][] = [
                        'id' => $p->id,
                        'favorite_id' => $fav->id,
                        'design_code' => $p->design_code ?? 'N/A',
                        'name' => $p->product_name ?? $p->design_name ?? $p->name ?? 'N/A',
                        'category_name' => $catName,
                        'category_id' => $p->product_category_id,
                        'weight_from' => floatval($p->weight_from),
                        'weight_to' => floatval($p->weight_to),
                        'image_url' => $imageUrl
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
            
            $favoritesQuery = Favorite::with(['product.category', 'user'])
                ->where('user_type', 'buyer');
                
            if ($myBuyerCode) {
                $buyerUserId = Buyer::where('bp_code', $myBuyerCode)->value('id');
                $favoritesQuery->where('user_id', $buyerUserId);
            } elseif ($request->has('target_code') || $request->has('bp_code')) {
                $bCode = $request->input('target_code') ?? $request->input('bp_code');
                $buyerUserId = Buyer::where('bp_code', $bCode)->value('id');
                if ($buyerUserId) $favoritesQuery->where('user_id', $buyerUserId);
            } elseif ($request->has('user_id')) {
                $favoritesQuery->where('user_id', $request->input('user_id'));
            }

            $favorites = $favoritesQuery->get();
            $buyerFavoritesStats = [];

            foreach ($favorites as $fav) {
                if (!$fav->product || !$fav->product->category) continue;
                $catName = $fav->product->category->name;
                $p = $fav->product;

                if ($fav->user) {
                    $key = $fav->user->bp_code ?? ('user_' . $fav->user_id);
                    if (!isset($buyerFavoritesStats[$key])) {
                        $buyerFavoritesStats[$key] = [
                            'name' => $fav->user->name ?? $fav->user->business_name ?? 'Unknown',
                            'code' => $fav->user->bp_code ?? null,
                            'bp_code' => $fav->user->bp_code ?? null,
                            'user_id' => $fav->user_id,
                            'total_favorites' => 0,
                            'categories' => []
                        ];
                    }
                    if (!isset($buyerFavoritesStats[$key]['categories'][$catName])) {
                        $buyerFavoritesStats[$key]['categories'][$catName] = [
                            'category' => $catName,
                            'category_id' => $p->product_category_id,
                            'count' => 0,
                            'products' => []
                        ];
                    }
                    $buyerFavoritesStats[$key]['total_favorites']++;
                    $buyerFavoritesStats[$key]['categories'][$catName]['count']++;

                    $imageUrl = $p->image_url;
                    if (!$imageUrl && $p->product_image) {
                        $imageUrl = filter_var($p->product_image, FILTER_VALIDATE_URL) ? $p->product_image : Storage::url($p->product_image);
                    } elseif (!$imageUrl && $p->image_path) {
                        $imageUrl = Storage::url($p->image_path);
                    } elseif (!$imageUrl && $p->image) {
                        $imageUrl = filter_var($p->image, FILTER_VALIDATE_URL) ? $p->image : Storage::url($p->image);
                    }

                    $buyerFavoritesStats[$key]['categories'][$catName]['products'][] = [
                        'id' => $p->id,
                        'favorite_id' => $fav->id,
                        'design_code' => $p->design_code ?? 'N/A',
                        'name' => $p->product_name ?? $p->design_name ?? $p->name ?? 'N/A',
                        'category_name' => $catName,
                        'category_id' => $p->product_category_id,
                        'weight_from' => floatval($p->weight_from),
                        'weight_to' => floatval($p->weight_to),
                        'image_url' => $imageUrl
                    ];
                }
            }
            return array_values($buyerFavoritesStats);
        }

        return [];
    }
}
