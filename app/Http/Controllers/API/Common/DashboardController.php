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

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics based on user role
     */
    public function getDashboardStats(Request $request)
    {
        $user = $request->user();

        // SuperAdmin/Admin - Global statistics
        if (in_array($user->role ?? '', ['super_admin', 'admin'])) {
            return $this->getSuperAdminStats();
        }

        // Check if user has dashboard permission
        if (method_exists($user, 'hasPermission') && !$user->hasPermission('dashboard')) {
            // Check for specific dashboard permissions if 'dashboard' is not present
            // Some roles might have specific dashboard access implicitly
        }

        // Craftsman statistics
        if ($user instanceof Craftman || ($user->role ?? '') === 'craftsman') {
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
     * SuperAdmin dashboard statistics - All counts globally
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
                'totalWorkOrders' => WorkOrder::count(),
                'totalProducts' => Product::whereNotNull('bp_code')->notFromFrozenAccounts()->count(),
                'totalDesigns' => Product::whereNotNull('bp_code')->where('design_status', 'Accepted')->notFromFrozenAccounts()->count(),
                'totalCatalogues' => Product::whereNotNull('bp_code')->whereNotNull('design_code')->where('design_status', 'Accepted')->notFromFrozenAccounts()->count(),
                'totalPurchaseOrders' => PurchaseOrder::count(),
                'totalRepairs' => Repair::count(),
                'totalUsers' => User::count(),
                'totalKeyUsers' => KeyUser::count(),
                'totalStockOrders' => StockOrder::count(),
                'brand_logo_url' => null,

                // Work Order status breakdown
                'newOrders' => WorkOrder::where('status', 'new')->count(),
                'allocatedOrders' => WorkOrder::where('status', 'allocated')->whereNotIn('craftsman_status', ['in_process', 'rejected', 'completed'])->count(),
                'inProcessOrders' => WorkOrder::where('craftsman_status', 'in_process')->count(),
                'forApprovalOrders' => WorkOrder::where('status', 'for_approval')->count(),
                'completedOrders' => WorkOrder::where('status', 'completed')->count(),
                'rejectedOrders' => WorkOrder::where('status', '!=', 'new')->where('craftsman_status', 'rejected')->count(),

                // Purchase Order status breakdown
                'pendingPurchaseOrders' => PurchaseOrder::where('status', 'pending')->count(),
                'approvedPurchaseOrders' => PurchaseOrder::where('status', 'approved')->count(),
                'rejectedPurchaseOrders' => PurchaseOrder::where('status', 'rejected')->count(),

                // Repair status breakdown
                'pendingRepairs' => Repair::where('status', 'Pending')->count(),
                'acceptedRepairs' => Repair::where('status', 'Accepted')->count(),
                'allocatedRepairs' => Repair::where('status', 'Allocated')->count(),
                'completedRepairs' => Repair::whereIn('status', ['Completed', 'Buyer_Accepted'])->count(),

                // Stock Order status breakdown
                'pendingStockOrders' => StockOrder::where('status', 'Pending')->count(),
                'allocatedStockOrders' => StockOrder::where('status', 'Allocated')->count(),
                'completedStockOrders' => StockOrder::where('status', 'Completed')->count(),
            ]
        ]);
    }

    /**
     * Craftsman dashboard statistics
     */
    private function getCraftsmanStats($user)
    {
        $craftsmanCode = $user->craftman_code;

        return response()->json([
            'success' => true,
            'data' => [
                'role' => 'craftsman',
                'permissions' => $user->permissions ?? [],
                'brand_logo_url' => !empty($user->brand_logo) ? asset('storage/' . $user->brand_logo) : null,
                'totalWorkOrders' => WorkOrder::where('allocated_craftsman_bp_code', $craftsmanCode)->count(),
                'totalPurchaseOrders' => PurchaseOrder::where('allocated_craftsman_code', $craftsmanCode)->count(),
                'totalRepairs' => Repair::where('allocated_craftsman_code', $craftsmanCode)->count(),
                'totalProducts' => Product::where('bp_code', $craftsmanCode)->count(),
                'totalDesigns' => Product::where('bp_code', $craftsmanCode)->where('design_status', 'Accepted')->count(),
                'totalCatalogues' => Product::where('bp_code', $craftsmanCode)->where('design_status', 'Accepted')->whereNotNull('design_code')->count(),
                'totalStockOrders' => StockOrder::where('craftsman_id', $user->id)->count(),

                // Work Order status breakdown
                'newOrders' => WorkOrder::where('allocated_craftsman_bp_code', $craftsmanCode)->where('status', 'new')->count(),
                'allocatedOrders' => WorkOrder::where('allocated_craftsman_bp_code', $craftsmanCode)->where('status', 'allocated')->whereNotIn('craftsman_status', ['in_process', 'rejected', 'completed'])->count(),
                'inProcessOrders' => WorkOrder::where('allocated_craftsman_bp_code', $craftsmanCode)->where('craftsman_status', 'in_process')->count(),
                'completedOrders' => WorkOrder::where('allocated_craftsman_bp_code', $craftsmanCode)->where('status', 'completed')->count(),
                'rejectedOrders' => WorkOrder::where('allocated_craftsman_bp_code', $craftsmanCode)->where('status', '!=', 'new')->where('craftsman_status', 'rejected')->count(),

                // Purchase Order status breakdown
                'pendingPurchaseOrders' => PurchaseOrder::where('allocated_craftsman_code', $craftsmanCode)->where('status', 'pending')->count(),
                'approvedPurchaseOrders' => PurchaseOrder::where('allocated_craftsman_code', $craftsmanCode)->where('status', 'approved')->count(),

                // Repair status breakdown
                'pendingRepairs' => Repair::where('allocated_craftsman_code', $craftsmanCode)->where('craftsman_status', 'Pending')->count(),
                'completedRepairs' => Repair::where('allocated_craftsman_code', $craftsmanCode)->where('craftsman_status', 'Completed')->count(),
            ]
        ]);
    }

    /**
     * Buyer dashboard statistics
     */
    private function getBuyerStats($user)
    {
        $bpCode = $user->bp_code;

        return response()->json([
            'success' => true,
            'data' => [
                'role' => 'buyer',
                'permissions' => $user->permissions ?? [],
                'brand_logo_url' => !empty($user->brand_logo) ? asset('storage/' . $user->brand_logo) : null,
                'totalProducts' => Product::where('bp_code', $bpCode)->count(),
                'totalDesigns' => Product::where('bp_code', $bpCode)->where('design_status', 'Accepted')->count(),
                'totalCatalogues' => Product::where('bp_code', $bpCode)->where('design_status', 'Accepted')->whereNotNull('design_code')->count(),
                'totalWorkOrders' => WorkOrder::where('bp_code', $bpCode)->count(),
                'totalRepairs' => Repair::where('buyer_id', $user->id)->count(),
                'totalStockOrders' => StockOrder::where('buyer_id', $user->id)->count(),

                // Work Order status breakdown
                'newOrders' => WorkOrder::where('bp_code', $bpCode)->where('status', 'new')->count(),
                'allocatedOrders' => WorkOrder::where('bp_code', $bpCode)->where('status', 'allocated')->whereNotIn('craftsman_status', ['in_process', 'rejected', 'completed'])->count(),
                'inProcessOrders' => WorkOrder::where('bp_code', $bpCode)->where('craftsman_status', 'in_process')->count(),
                'completedOrders' => WorkOrder::where('bp_code', $bpCode)->where('status', 'completed')->count(),
                'rejectedOrders' => WorkOrder::where('bp_code', $bpCode)->where('status', '!=', 'new')->where('craftsman_status', 'rejected')->count(),

                // Repair status breakdown
                'pendingRepairs' => Repair::where('buyer_id', $user->id)->where('craftsman_status', 'Pending')->count(),
                'completedRepairs' => Repair::where('buyer_id', $user->id)->where('craftsman_status', 'Completed')->count(),
            ]
        ]);
    }

    /**
     * Key User dashboard statistics
     */
    private function getKeyUserStats($user)
    {
        $userCode = $user->user_code;

        return response()->json([
            'success' => true,
            'data' => [
                'role' => 'key_user',
                'permissions' => $user->permissions ?? [],
                'brand_logo_url' => !empty($user->brand_logo) ? asset('storage/' . $user->brand_logo) : null,
                'totalWorkOrders' => WorkOrder::where('creator_user_code', $userCode)->count(),
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

        return response()->json([
            'success' => true,
            'data' => [
                'role' => 'user',
                'permissions' => $user->permissions ?? [],
                'brand_logo_url' => !empty($user->brand_logo) ? asset('storage/' . $user->brand_logo) : null,
                'totalWorkOrders' => WorkOrder::where('creator_user_code', $userCode)->count(),
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
