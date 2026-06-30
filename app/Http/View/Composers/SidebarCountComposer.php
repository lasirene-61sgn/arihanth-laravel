<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\Buyer;
use App\Models\Craftman;
use App\Models\ProcessOwner;
use App\Models\KeyUser;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\StockOrder;
use App\Models\Repair;
use App\Models\RegistrationRequest;
use Illuminate\Support\Facades\Auth;

class SidebarCountComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view)
    {
        $counts = [
            'buyersCount' => 0,
            'craftsmenCount' => 0,
            'adminsCount' => 0,
            'keyUsersCount' => 0,
            'usersCount' => 0,
            'workOrdersCount' => 0,
            'purchaseOrdersCount' => 0,
            'cataloguesCount' => 0,
            'productsCount' => 0,
            'designsCount' => 0,
            'kycPendingCount' => 0,
            'stockOrdersCount' => 0,
            'repairsCount' => 0,
            'pendingRegistrationsCount' => 0,
        ];

        if (Auth::guard('super_admin')->check() || Auth::guard('admin')->check()) {
            // Global counts for Admin/Super Admin
            $counts = [
                'buyersCount' => Buyer::count(),
                'craftsmenCount' => Craftman::count(),
                'adminsCount' => ProcessOwner::where('role', 'admin')->count(),
                'keyUsersCount' => KeyUser::count(),
                'usersCount' => User::count(),
                
                'workOrdersCount' => WorkOrder::where('status', 'new')->count(),
                'purchaseOrdersCount' => PurchaseOrder::where('status', 'created')
                                    ->whereNull('allocated_craftsman_code')
                                    ->count(),
                'cataloguesCount' => Product::where('design_status', 'Accepted')
                                    ->whereNotNull('design_code')
                                    ->whereNotNull('type')
                                    ->where('type', '!=', '')
                                    ->notFromFrozenAccounts()
                                    ->count(),
                'productsCount' => Product::notFromFrozenAccounts()
                                    ->whereNotNull('type')
                                    ->count(),
                'designsCount' => Product::notFromFrozenAccounts()
                                    ->whereNotNull('type')
                                    ->where('design_status', 'Pending')
                                    ->count(),
                'kycPendingCount' => 0,
                'stockOrdersCount' => StockOrder::where('status', 'Pending')->count(),
                'repairsCount' => Repair::whereIn('status', ['Pending', 'Accepted'])->count(),
                'pendingRegistrationsCount' => RegistrationRequest::where('status', 'Pending')->count(),
            ];
        } elseif (Auth::guard('buyer')->check()) {
            $buyer = Auth::guard('buyer')->user();
            $bpCode = $buyer->bp_code;
            
            $counts = [
                'keyUsersCount' => KeyUser::where('bp_code', $bpCode)->count(),
                'usersCount' => User::where('bp_code', $bpCode)->count(),
                'workOrdersCount' => WorkOrder::where('bp_code', $bpCode)->count(),
                'cataloguesCount' => Product::where('bp_code', $bpCode)
                                    ->where('design_status', 'Accepted')
                                    ->whereNotNull('design_code')
                                    ->count(),
                'productsCount' => Product::where('bp_code', $bpCode)->count(),
                'designsCount' => Product::where('bp_code', $bpCode)->count(), // Designs created by this buyer
                'purchaseOrdersCount' => 0, // Buyers don't usually see POs in this context
                'stockOrdersCount' => StockOrder::where('buyer_id', $buyer->id)->count(),
            ];
        } elseif (Auth::guard('key_user')->check()) {
            $keyUser = Auth::guard('key_user')->user();
            $keyUserId = $keyUser->id;
            $bpCode = $keyUser->bp_code;

            $counts = [
                'usersCount' => User::where('bp_code', $bpCode)->count(),
                'workOrdersCount' => WorkOrder::where('bp_code', $bpCode)->count(),
                // Only count products/designs/catalogues that this key user personally created
                'cataloguesCount' => Product::where('created_by', $keyUserId)
                                    ->where('design_status', 'Accepted')
                                    ->whereNotNull('design_code')
                                    ->count(),
                'productsCount' => Product::where('created_by', $keyUserId)->count(),
                'designsCount' => Product::where('created_by', $keyUserId)->count(),
            ];
        } elseif (Auth::guard('craftsman')->check()) {
            $craftsman = Auth::guard('craftsman')->user();
            $craftsmanCode = $craftsman->craftman_code;

            $counts = [
                'workOrdersCount' => WorkOrder::where('allocated_craftsman_bp_code', $craftsmanCode)->count(),
                'purchaseOrdersCount' => PurchaseOrder::where('allocated_craftsman_code', $craftsmanCode)->count(),
                // Craftsman products are stored with bp_code = craftman_code
                'cataloguesCount' => Product::where('bp_code', $craftsmanCode)
                                    ->where('design_status', 'Accepted')
                                    ->whereNotNull('design_code')
                                    ->count(),
                'productsCount' => Product::where('bp_code', $craftsmanCode)->count(),
                'designsCount' => Product::where('bp_code', $craftsmanCode)->count(),
                'stockOrdersCount' => \App\Models\StockOrder::whereHas('items', function($query) use ($craftsman) {
                    $query->where('craftsman_id', $craftsman->id)
                          ->whereIn('status', ['Pending', 'Accepted']);
                })->count(),
            ];
        } elseif (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            $userId = $user->id;
            $bpCode = $user->bp_code;

            $counts = [
                'workOrdersCount' => WorkOrder::where('bp_code', $bpCode)->count(),
                // Only count products/designs/catalogues that this user personally created
                'cataloguesCount' => Product::where('created_by', $userId)
                                    ->where('design_status', 'Accepted')
                                    ->whereNotNull('design_code')
                                    ->count(),
                'productsCount' => Product::where('created_by', $userId)->count(),
                'designsCount' => Product::where('created_by', $userId)->count(),
            ];
        }

        $latestMeetings = collect();

        if (Auth::guard('super_admin')->check()) {
            $user = Auth::guard('super_admin')->user();
            $latestMeetings = \App\Models\Meeting::where('host_id', $user->id)
                ->where('host_type', get_class($user))
                ->with(['participant'])
                ->latest()
                ->take(2)
                ->get();
        } elseif (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            $latestMeetings = \App\Models\Meeting::where('host_id', $user->id)
                ->where('host_type', get_class($user))
                ->with(['participant'])
                ->latest()
                ->take(2)
                ->get();
        } elseif (Auth::guard('buyer')->check()) {
            $user = Auth::guard('buyer')->user();
            $latestMeetings = \App\Models\Meeting::where('participant_id', $user->id)
                ->where('participant_type', get_class($user))
                ->with(['host'])
                ->latest()
                ->take(2)
                ->get();
        } elseif (Auth::guard('craftsman')->check()) {
            $user = Auth::guard('craftsman')->user();
            $latestMeetings = \App\Models\Meeting::where('participant_id', $user->id)
                ->where('participant_type', get_class($user))
                ->with(['host'])
                ->latest()
                ->take(2)
                ->get();
        }

        $view->with('sidebarCounts', $counts);
        $view->with('latestMeetings', $latestMeetings);
    }
}
