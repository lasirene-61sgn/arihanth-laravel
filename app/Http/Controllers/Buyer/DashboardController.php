<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Buyer;

class DashboardController extends Controller
{
    public function index()
    {
        $buyer = Auth::guard('buyer')->user();
        
        // Check if buyer has key_user permission
        // Check if buyer has key_user permission
        $canManageKeyUsers = $buyer->hasPermission('key_user');
        
        $bpCode = $buyer->bp_code;

        $productsCount = \App\Models\Product::where('bp_code', $bpCode)->count();

        // Count all accepted designs from all sources (matching DesignController logic)
        $designsCount = \App\Models\Product::whereNotNull('design_code')
            ->where('design_status', 'Accepted')
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->notFromFrozenAccounts()
            ->count();

        // Count buyer's own accepted designs (matching CatalogueController logic)
        $cataloguesCount = \App\Models\Product::where('bp_code', $bpCode)
            ->where('design_status', 'Accepted')
            ->whereNotNull('design_code')
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->notFromFrozenAccounts()
            ->where(function($q) {
                $q->whereNull('design_view_unlocked_until')
                  ->orWhere('design_view_unlocked_until', '>=', now());
            })
            ->count();

        $workOrdersCount = \App\Models\WorkOrder::where('bp_code', $bpCode)->count();
        $woInProcessCount = \App\Models\WorkOrder::where('bp_code', $bpCode)->where('craftsman_status', 'In Process')->count();
        $woOverdueCount = \App\Models\WorkOrder::where('bp_code', $bpCode)->where('craftsman_status', 'Overdue')->count();
        $woCompletedCount = \App\Models\WorkOrder::where('bp_code', $bpCode)->where('craftsman_status', 'Completed')->count();

        // Weights
        $woNewWeight = \App\Models\WorkOrder::where('bp_code', $bpCode)->where('status', 'new')->sum('weight_to');
        $woInProcessWeight = \App\Models\WorkOrder::where('bp_code', $bpCode)->where('craftsman_status', 'In Process')->sum('weight_to');
        $woOverdueWeight = \App\Models\WorkOrder::where('bp_code', $bpCode)->where('craftsman_status', 'Overdue')->sum('weight_to');

        $usersCount = \App\Models\User::where('bp_code', $bpCode)->count();
        $keyUsersCount = \App\Models\KeyUser::where('bp_code', $bpCode)->count();

        // Craftsman-wise Progress Analytics for Modal
        $craftsmanStats = [];
        $buyerWorkOrders = \App\Models\WorkOrder::where('bp_code', $bpCode)->get();

        $craftsmanCodes = $buyerWorkOrders->pluck('allocated_craftsman_bp_code')
            ->filter()
            ->unique();

        $craftsmen = \App\Models\Craftman::whereIn('craftman_code', $craftsmanCodes)->get();

        foreach ($craftsmen as $craftman) {
            $code = $craftman->craftman_code;
            
            // WA stats
            $waItems = $buyerWorkOrders->where('allocated_craftsman_bp_code', $code);
            $waProcess = $waItems->where('craftsman_status', 'In Process');
            $waOverdue = $waItems->where('craftsman_status', 'Overdue');

            $craftsmanStats[$code] = [
                'name' => $craftman->business_name ?: $craftman->name,
                'wa' => [
                    'process' => ['count' => $waProcess->count(), 'weight' => $waProcess->sum('weight_to')],
                    'overdue' => ['count' => $waOverdue->count(), 'weight' => $waOverdue->sum('weight_to')],
                ],
                'po' => [
                    'process' => ['count' => 0, 'weight' => 0],
                    'overdue' => ['count' => 0, 'weight' => 0],
                ]
            ];
        }
        
        return view('buyer.dashboard', compact(
            'buyer', 
            'canManageKeyUsers',
            'productsCount',
            'designsCount',
            'cataloguesCount',
            'workOrdersCount',
            'woInProcessCount',
            'woOverdueCount',
            'woCompletedCount',
            'woNewWeight',
            'woInProcessWeight',
            'woOverdueWeight',
            'usersCount',
            'keyUsersCount',
            'craftsmanStats'
        ));
    }
    
    public function finance()
    {
        return view('buyer.finance.index');
    }
}