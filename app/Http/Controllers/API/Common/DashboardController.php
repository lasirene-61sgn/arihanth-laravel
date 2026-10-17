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
use App\Models\Repair;
use App\Models\StockOrder;
use App\Models\CraftsmanStaff;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics based on user role
     */
    public function getDashboardStats(Request $request)
    {
        $user = $request->user();

        // SuperAdmin/Admin - Global statistics
        if (in_array($user->role ?? '', ['super_admin', 'admin', 'process_owner']) || $user instanceof \App\Models\ProcessOwner) {
            return $this->getSuperAdminStats();
        }

        // Check if user has dashboard permission or at least one relevant module permission
        $hasAccess = false;
        
        if ($user instanceof Buyer || $user instanceof Craftman || in_array($user->role ?? '', ['buyer', 'craftsman'])) {
            $hasAccess = true; // These roles intrinsically have access to work orders/products
        } elseif (method_exists($user, 'hasPermission')) {
            if ($user->hasPermission('dashboard')) {
                $hasAccess = true;
            } else {
                $allowedModules = [
                    'work_order', 'wo_view',
                    'purchase_order', 'po_view',
                    'design', 'design_view',
                    'catalogue', 'catalogue_view',
                    'product', 'product_view'
                ];
                foreach ($allowedModules as $module) {
                    if ($user->hasPermission($module)) {
                        $hasAccess = true;
                        break;
                    }
                }
            }
        }
        
        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden - You do not have permission to view the dashboard'
            ], 403);
        }

        // Craftsman statistics
        if ($user instanceof Craftman || $user instanceof CraftsmanStaff || in_array($user->role ?? '', ['craftsman', 'craftsman_staff'])) {
            return $this->getCraftsmanStats($user);
        }

        // Key User statistics
        if ($user instanceof KeyUser || ($user->role ?? '') === 'key_user') {
            return $this->getKeyUserStats($user);
        }

        // User (End User) statistics
        if ($user instanceof User) {
            return $this->getUserStats($user);
        }

        // Buyer statistics
        if ($user instanceof Buyer || ($user->role ?? '') === 'buyer') {
            return $this->getBuyerStats($user);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 403);
    }

    /**
     * SuperAdmin dashboard statistics - All counts globally (New orders for the 4 order types)
     */
    private function getSuperAdminStats()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'role' => 'admin',
                'permissions' => ['all'],
                'totalBusinessPartners' => Buyer::count() + Craftman::count(),
                'totalBuyers' => Buyer::count(),
                'totalCraftsmen' => Craftman::count(),
                'totalWorkOrders' => WorkOrder::where('status', 'new')->count(),
                'totalProducts' => Product::whereNotNull('bp_code')->notFromFrozenAccounts()->count(),
                'totalDesigns' => Product::whereNotNull('bp_code')->where('design_status', 'Accepted')->notFromFrozenAccounts()->count(),
                'totalCatalogues' => Product::whereNotNull('bp_code')->whereNotNull('design_code')->where('design_status', 'Accepted')->notFromFrozenAccounts()->count(),
                'totalPurchaseOrders' => PurchaseOrder::whereIn('status', ['created', 'pending'])->whereNull('allocated_craftsman_code')->count(),
                'totalRepairs' => Repair::whereIn('status', ['pending', 'Pending'])->count(),
                'totalUsers' => User::count(),
                'totalKeyUsers' => KeyUser::count(),
                'totalStockOrders' => StockOrder::whereIn('status', ['pending', 'Pending'])->count(),
                'brand_logo_url' => null,

                // Work Order status breakdown
                'newOrders' => WorkOrder::where('status', 'new')->count(),
                'allocatedOrders' => WorkOrder::where('status', 'allocated')->whereNotIn('craftsman_status', ['in_process', 'rejected', 'completed'])->count(),
                'inProcessOrders' => WorkOrder::where('craftsman_status', 'in_process')->count(),
                'forApprovalOrders' => WorkOrder::where('status', 'for_approval')->count(),
                'completedOrders' => WorkOrder::where('status', 'completed')->count(),
                'rejectedOrders' => WorkOrder::where('status', '!=', 'new')->where('craftsman_status', 'rejected')->count(),

                // Purchase Order status breakdown
                'pendingPurchaseOrders' => PurchaseOrder::whereIn('status', ['created', 'pending'])->whereNull('allocated_craftsman_code')->count(),
                'approvedPurchaseOrders' => PurchaseOrder::where('status', 'approved')->count(),
                'rejectedPurchaseOrders' => PurchaseOrder::where('status', 'rejected')->count(),

                // Repair status breakdown
                'pendingRepairs' => Repair::whereIn('status', ['pending', 'Pending'])->count(),
                'acceptedRepairs' => Repair::whereIn('status', ['accepted', 'Accepted'])->count(),
                'allocatedRepairs' => Repair::whereIn('status', ['allocated', 'Allocated'])->count(),
                'completedRepairs' => Repair::whereIn('status', ['Completed', 'completed', 'Buyer_Accepted', 'buyer_accepted'])->count(),

                // Stock Order status breakdown
                'pendingStockOrders' => StockOrder::whereIn('status', ['pending', 'Pending'])->count(),
                'allocatedStockOrders' => StockOrder::whereIn('status', ['allocated', 'Allocated'])->count(),
                'completedStockOrders' => StockOrder::whereIn('status', ['completed', 'Completed'])->count(),
            ]
        ]);
    }

    /**
     * Craftsman dashboard statistics - Allocated orders only for the 4 order types
     */
    private function getCraftsmanStats($user)
    {
        $craftsmanCode = $user instanceof CraftsmanStaff ? ($user->craftsman->craftman_code ?? null) : $user->craftman_code;
        $craftsmanId = $user instanceof CraftsmanStaff ? ($user->craftsman_id ?? ($user->craftsman->id ?? $user->id)) : $user->id;

        return response()->json([
            'success' => true,
            'data' => [
                'role' => $user instanceof CraftsmanStaff ? 'craftsman_staff' : 'craftsman',
                'permissions' => array_values(array_unique(array_merge(['work_order', 'global_search'], is_array($p = ($user instanceof CraftsmanStaff ? $user->getMappedPermissionsArray() : ($user->permissions ?? []))) ? $p : (is_string($p) ? (json_decode($p, true) ?? []) : [])))),
                'brand_logo_url' => !empty($user->brand_logo) ? asset('storage/' . $user->brand_logo) : null,
                'totalWorkOrders' => WorkOrder::where('allocated_craftsman_bp_code', $craftsmanCode)->where('status', 'allocated')->whereNotIn('craftsman_status', ['in_process', 'rejected', 'completed'])->count(),
                'totalPurchaseOrders' => PurchaseOrder::where('allocated_craftsman_code', $craftsmanCode)->where(function ($q) {
                    $q->where('craftsman_status', 'allocated')
                      ->orWhere(function ($sub) {
                          $sub->where('status', 'created')->whereNotNull('allocated_craftsman_code');
                      });
                })->whereNotIn('craftsman_status', ['in_process', 'rejected', 'completed'])->count(),
                'totalRepairs' => Repair::where(function ($q) use ($craftsmanCode) {
                    $q->where('craftman_code', $craftsmanCode)->orWhere('allocated_craftsman_code', $craftsmanCode);
                })->whereIn('status', ['allocated', 'Allocated'])->where(function ($q) {
                    $q->whereNull('craftsman_status')->orWhereIn('craftsman_status', ['pending_acceptance', 'allocated']);
                })->count(),
                'totalProducts' => Product::where('bp_code', $craftsmanCode)->count(),
                'totalDesigns' => Product::where('bp_code', $craftsmanCode)->where('design_status', 'Accepted')->count(),
                'totalCatalogues' => Product::where('bp_code', $craftsmanCode)->where('design_status', 'Accepted')->whereNotNull('design_code')->count(),
                'totalStockOrders' => StockOrder::where('craftsman_id', $craftsmanId)->whereIn('status', ['Allocated', 'allocated'])->count(),

                // Work Order status breakdown
                'newOrders' => WorkOrder::where('allocated_craftsman_bp_code', $craftsmanCode)->where('status', 'new')->count(),
                'allocatedOrders' => WorkOrder::where('allocated_craftsman_bp_code', $craftsmanCode)->where('status', 'allocated')->whereNotIn('craftsman_status', ['in_process', 'rejected', 'completed'])->count(),
                'inProcessOrders' => WorkOrder::where('allocated_craftsman_bp_code', $craftsmanCode)->where('craftsman_status', 'in_process')->count(),
                'completedOrders' => WorkOrder::where('allocated_craftsman_bp_code', $craftsmanCode)->where('status', 'completed')->count(),
                'rejectedOrders' => WorkOrder::where('allocated_craftsman_bp_code', $craftsmanCode)->where('status', '!=', 'new')->where('craftsman_status', 'rejected')->count(),

                // Purchase Order status breakdown
                'pendingPurchaseOrders' => PurchaseOrder::where('allocated_craftsman_code', $craftsmanCode)->where(function ($q) {
                    $q->where('craftsman_status', 'allocated')
                      ->orWhere('status', 'created');
                })->count(),
                'approvedPurchaseOrders' => PurchaseOrder::where('allocated_craftsman_code', $craftsmanCode)->whereIn('status', ['approved', 'in_process', 'completed'])->count(),

                // Repair status breakdown
                'pendingRepairs' => Repair::where(function ($q) use ($craftsmanCode) {
                    $q->where('craftman_code', $craftsmanCode)->orWhere('allocated_craftsman_code', $craftsmanCode);
                })->where(function ($q) {
                    $q->whereNull('craftsman_status')->orWhereIn('craftsman_status', ['Pending', 'pending', 'pending_acceptance', 'allocated']);
                })->count(),
                'completedRepairs' => Repair::where(function ($q) use ($craftsmanCode) {
                    $q->where('craftman_code', $craftsmanCode)->orWhere('allocated_craftsman_code', $craftsmanCode);
                })->where(function ($q) {
                    $q->whereIn('craftsman_status', ['Completed', 'completed', 'pending_approval', 'approved_by_superadmin'])->orWhereIn('status', ['Completed', 'completed']);
                })->count(),
            ]
        ]);
    }

    /**
     * Buyer dashboard statistics - New orders only for the 4 order types
     */
    private function getBuyerStats($user)
    {
        $bpCode = $user->bp_code;
        $buyerId = $user instanceof Buyer ? $user->id : (Buyer::where('bp_code', $bpCode)->value('id') ?? $user->id);

        $rawPerms = $user->permissions ?? [];
        $perms = is_string($rawPerms) ? (json_decode($rawPerms, true) ?? []) : (is_array($rawPerms) ? $rawPerms : []);
        $permissions = array_values(array_unique(array_merge(['work_order', 'global_search'], $perms)));

        return response()->json([
            'success' => true,
            'data' => [
                'role' => 'buyer',
                'permissions' => $permissions,
                'brand_logo_url' => !empty($user->brand_logo) ? asset('storage/' . $user->brand_logo) : null,
                'totalProducts' => Product::where('bp_code', $bpCode)->count(),
                'totalDesigns' => Product::where('bp_code', $bpCode)->where('design_status', 'Accepted')->count(),
                'totalCatalogues' => Product::where('bp_code', $bpCode)->where('design_status', 'Accepted')->whereNotNull('design_code')->count(),
                'totalWorkOrders' => WorkOrder::where('bp_code', $bpCode)->where('status', 'new')->count(),
                'totalPurchaseOrders' => PurchaseOrder::where('creator_type', 'buyer')->where('created_by', $user->id)->whereIn('status', ['created', 'pending'])->count(),
                'totalRepairs' => Repair::where('buyer_id', $buyerId)->whereIn('status', ['pending', 'Pending'])->count(),
                'totalStockOrders' => StockOrder::where('buyer_id', $buyerId)->whereIn('status', ['pending', 'Pending'])->count(),

                // Work Order status breakdown
                'newOrders' => WorkOrder::where('bp_code', $bpCode)->where('status', 'new')->count(),
                'allocatedOrders' => WorkOrder::where('bp_code', $bpCode)->where('status', 'allocated')->whereNotIn('craftsman_status', ['in_process', 'rejected', 'completed'])->count(),
                'inProcessOrders' => WorkOrder::where('bp_code', $bpCode)->where('craftsman_status', 'in_process')->count(),
                'completedOrders' => WorkOrder::where('bp_code', $bpCode)->where('status', 'completed')->count(),
                'rejectedOrders' => WorkOrder::where('bp_code', $bpCode)->where('status', '!=', 'new')->where('craftsman_status', 'rejected')->count(),

                // Repair status breakdown
                'pendingRepairs' => Repair::where('buyer_id', $buyerId)->where(function ($q) {
                    $q->whereIn('status', ['pending', 'Pending'])
                      ->orWhereIn('craftsman_status', ['Pending', 'pending']);
                })->count(),
                'completedRepairs' => Repair::where('buyer_id', $buyerId)->where(function ($q) {
                    $q->whereIn('status', ['Completed', 'completed'])
                      ->orWhereIn('craftsman_status', ['Completed', 'completed']);
                })->count(),
            ]
        ]);
    }

    /**
     * Key User dashboard statistics
     */
    private function getKeyUserStats($user)
    {
        $userCode = $user->user_code;
        $bpCode = $user->bp_code;
        $buyerId = Buyer::where('bp_code', $bpCode)->value('id') ?? $user->id;

        $rawPerms = $user->permissions ?? [];
        $perms = is_string($rawPerms) ? (json_decode($rawPerms, true) ?? []) : (is_array($rawPerms) ? $rawPerms : []);
        $permissions = array_values(array_unique(array_merge(['work_order', 'global_search'], $perms)));

        return response()->json([
            'success' => true,
            'data' => [
                'role' => 'key_user',
                'permissions' => $permissions,
                'brand_logo_url' => !empty($user->brand_logo) ? asset('storage/' . $user->brand_logo) : null,
                'totalWorkOrders' => WorkOrder::where('creator_user_code', $userCode)->where('status', 'new')->count(),
                'totalPurchaseOrders' => PurchaseOrder::where('creator_type', 'key_user')->where('created_by', $user->id)->whereIn('status', ['created', 'pending'])->count(),
                'totalRepairs' => Repair::where('buyer_id', $buyerId)->whereIn('status', ['pending', 'Pending'])->count(),
                'totalStockOrders' => StockOrder::where('buyer_id', $buyerId)->whereIn('status', ['pending', 'Pending'])->count(),
                'totalProducts' => Product::whereNotNull('bp_code')->notFromFrozenAccounts()->count(),
                'totalDesigns' => Product::whereNotNull('bp_code')->where('design_status', 'Accepted')->notFromFrozenAccounts()->count(),
                'totalCatalogues' => Product::whereNotNull('bp_code')->whereNotNull('design_code')->where('design_status', 'Accepted')->notFromFrozenAccounts()->count(),

                // Work Order status breakdown
                'newOrders' => WorkOrder::where('creator_user_code', $userCode)->where('status', 'new')->count(),
                'allocatedOrders' => WorkOrder::where('creator_user_code', $userCode)->where('status', 'allocated')->whereNotIn('craftsman_status', ['in_process', 'rejected', 'completed'])->count(),
                'inProcessOrders' => WorkOrder::where('creator_user_code', $userCode)->where('craftsman_status', 'in_process')->count(),
                'completedOrders' => WorkOrder::where('creator_user_code', $userCode)->where('status', 'completed')->count(),
                'rejectedOrders' => WorkOrder::where('creator_user_code', $userCode)->where('status', '!=', 'new')->where('craftsman_status', 'rejected')->count(),
            ]
        ]);
    }

    /**
     * User dashboard statistics
     */
    private function getUserStats($user)
    {
        $userCode = $user->user_code;
        $bpCode = $user->bp_code;
        $buyerId = Buyer::where('bp_code', $bpCode)->value('id') ?? $user->id;

        $rawPerms = $user->permissions ?? [];
        $perms = is_string($rawPerms) ? (json_decode($rawPerms, true) ?? []) : (is_array($rawPerms) ? $rawPerms : []);
        $permissions = array_values(array_unique(array_merge(['work_order', 'global_search'], $perms)));

        return response()->json([
            'success' => true,
            'data' => [
                'role' => 'user',
                'permissions' => $permissions,
                'brand_logo_url' => !empty($user->brand_logo) ? asset('storage/' . $user->brand_logo) : null,
                'totalWorkOrders' => WorkOrder::where('creator_user_code', $userCode)->where('status', 'new')->count(),
                'totalPurchaseOrders' => PurchaseOrder::where('creator_type', 'user')->where('created_by', $user->id)->whereIn('status', ['created', 'pending'])->count(),
                'totalRepairs' => Repair::where('buyer_id', $buyerId)->whereIn('status', ['pending', 'Pending'])->count(),
                'totalStockOrders' => StockOrder::where('buyer_id', $buyerId)->whereIn('status', ['pending', 'Pending'])->count(),
                'totalProducts' => Product::whereNotNull('bp_code')->notFromFrozenAccounts()->count(),
                'totalDesigns' => Product::whereNotNull('bp_code')->where('design_status', 'Accepted')->notFromFrozenAccounts()->count(),
                'totalCatalogues' => Product::whereNotNull('bp_code')->whereNotNull('design_code')->where('design_status', 'Accepted')->notFromFrozenAccounts()->count(),

                // Work Order status breakdown
                'newOrders' => WorkOrder::where('creator_user_code', $userCode)->where('status', 'new')->count(),
                'allocatedOrders' => WorkOrder::where('creator_user_code', $userCode)->where('status', 'allocated')->whereNotIn('craftsman_status', ['in_process', 'rejected', 'completed'])->count(),
                'inProcessOrders' => WorkOrder::where('creator_user_code', $userCode)->where('craftsman_status', 'in_process')->count(),
                'completedOrders' => WorkOrder::where('creator_user_code', $userCode)->where('status', 'completed')->count(),
                'rejectedOrders' => WorkOrder::where('creator_user_code', $userCode)->where('status', '!=', 'new')->where('craftsman_status', 'rejected')->count(),
            ]
        ]);
    }
}
