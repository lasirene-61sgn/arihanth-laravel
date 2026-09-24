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

        // If no specific type is requested, return an overview WITH PAGINATION
        if (!$type) {
            $data = [
                'work_orders' => $getWorkOrders()->paginate($perPage),
                'purchase_orders' => $getPurchaseOrders()->paginate($perPage)
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

        return response()->json(['success' => false, 'message' => 'Invalid type requested'], 400);
    }
}
