<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Buyer;
use App\Models\Product;
use App\Models\WorkOrder;
use App\Models\User;
use App\Models\KeyUser;
use App\Models\Favorite;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index()
    {
        $buyer = Auth::guard('buyer')->user();
        
        $canManageKeyUsers = $buyer->hasPermission('key_user');
        $bpCode = $buyer->bp_code;

        $productsCount = Product::where('bp_code', $bpCode)->count();

        // ----------------------------------------------------
        // Buyer Accepted Designs & Categories
        // Includes all accepted designs without dropping null/empty types
        // ----------------------------------------------------
        $favoritesMap = Favorite::where('user_type', 'buyer')
            ->where('user_id', $buyer->id)
            ->get()
            ->keyBy('product_id');

        $acceptedDesignsQuery = Product::where('bp_code', $bpCode)
            ->whereNotNull('design_code')
            ->where('design_status', 'Accepted')
            ->notFromFrozenAccounts();

        $designsCount = (clone $acceptedDesignsQuery)->count();
        $buyerDesigns = (clone $acceptedDesignsQuery)->get();

        $categoryDesignsModal = $buyerDesigns->map(function ($item) use ($favoritesMap) {
            $fav = $favoritesMap->get($item->id);
            $designName = ($fav && !empty($fav->design_name)) ? $fav->design_name : ($item->product_name ?? $item->design_name ?? $item->name ?? 'N/A');

            // Robust category fallback logic
            $categoryName = trim(
                $item->category->name 
                ?? $item->category_name 
                ?? $item->category 
                ?? $item->type 
                ?? 'General'
            );

            if (empty($categoryName)) {
                $categoryName = 'General';
            }

            $imageUrl = null;
            if (!empty($item->image_path)) {
                $imageUrl = Storage::url($item->image_path);
            } elseif (!empty($item->image)) {
                $imageUrl = filter_var($item->image, FILTER_VALIDATE_URL) ? $item->image : Storage::url($item->image);
            }

            return [
                'id'          => $item->id,
                'category'    => $categoryName,
                'design_code' => $item->design_code ?? 'N/A',
                'design_name' => $designName,
                'weight_from' => number_format((float)($item->weight_from ?? 0), 3),
                'weight_to'   => number_format((float)($item->weight_to ?? 0), 3),
                'image_url'   => $imageUrl,
            ];
        });

        // Group into category cards with aggregated counts and max weights
        $designCategories = $categoryDesignsModal->groupBy('category')->map(function ($items, $catName) {
            return [
                'category'    => $catName,
                'count'       => $items->count(),
                'weight_from' => $items->sum(fn($i) => (float) str_replace(',', '', $i['weight_from'])),
                'weight_to'   => $items->sum(fn($i) => (float) str_replace(',', '', $i['weight_to'])),
            ];
        })->values();

        // Catalogues count
        $cataloguesCount = Product::where('bp_code', $bpCode)
            ->where('design_status', 'Accepted')
            ->whereNotNull('design_code')
            ->notFromFrozenAccounts()
            ->where(function ($q) {
                $q->whereNull('design_view_unlocked_until')
                  ->orWhere('design_view_unlocked_until', '>=', now());
            })
            ->count();

        $today = Carbon::today();

        // Fetch Work Orders for this Buyer
        $buyerWorkOrders = WorkOrder::where('bp_code', $bpCode)->get();

        // 1. Completed
        $woCompletedItems = $buyerWorkOrders->filter(function ($wo) {
            return strtolower($wo->craftsman_status ?? '') === 'completed';
        });

        // 2. Overdue
        $woOverdueItems = $buyerWorkOrders->filter(function ($wo) use ($today) {
            if (strtolower($wo->craftsman_status ?? '') === 'completed') {
                return false;
            }
            if (strtolower($wo->craftsman_status ?? '') === 'overdue') {
                return true;
            }
            return !empty($wo->due_date) && Carbon::parse($wo->due_date)->startOfDay()->lt($today);
        });

        $overdueIds   = $woOverdueItems->pluck('id')->toArray();
        $completedIds = $woCompletedItems->pluck('id')->toArray();
        $excludedIds  = array_merge($overdueIds, $completedIds);

        // 3. In Process
        $woInProcessItems = $buyerWorkOrders->filter(function ($wo) use ($excludedIds) {
            return !in_array($wo->id, $excludedIds) && strtolower($wo->craftsman_status ?? '') === 'in process';
        });

        // 4. Allocated
        $woAllocatedItems = $buyerWorkOrders->filter(function ($wo) use ($excludedIds) {
            return !in_array($wo->id, $excludedIds) 
                && strtolower($wo->craftsman_status ?? '') !== 'in process'
                && !empty($wo->allocated_craftsman_bp_code);
        });

        // 5. New Orders
        $woNewItems = $buyerWorkOrders->filter(function ($wo) use ($excludedIds) {
            return !in_array($wo->id, $excludedIds) 
                && empty($wo->allocated_craftsman_bp_code)
                && (strtolower($wo->status ?? '') === 'new' || empty($wo->craftsman_status));
        });

        // Counts
        $workOrdersCount   = $buyerWorkOrders->count();
        $woNewCount        = $woNewItems->count();
        $woAllocatedCount  = $woAllocatedItems->count();
        $woInProcessCount  = $woInProcessItems->count();
        $woCompletedCount  = $woCompletedItems->count();
        $woOverdueCount    = $woOverdueItems->count();

        // Weights
        $woNewWeight       = $woNewItems->sum('weight_to');
        $woAllocatedWeight = $woAllocatedItems->sum('weight_to');
        $woInProcessWeight = $woInProcessItems->sum('weight_to');
        $woCompletedWeight = $woCompletedItems->sum('weight_to');
        $woOverdueWeight   = $woOverdueItems->sum('weight_to');

        $usersCount    = User::where('bp_code', $bpCode)->count();
        $keyUsersCount = KeyUser::where('bp_code', $bpCode)->count();

        // Prepare Work Orders for Modal View
        $modalWorkOrders = $buyerWorkOrders->map(function ($wo) use ($today, $woNewItems, $woAllocatedItems, $woInProcessItems, $woCompletedItems, $woOverdueItems) {
            $categoryBucket = 'other';
            $daysOverdue = 0;

            if ($woCompletedItems->contains('id', $wo->id)) {
                $categoryBucket = 'completed';
            } elseif ($woOverdueItems->contains('id', $wo->id)) {
                $categoryBucket = 'overdue';
                if (!empty($wo->due_date)) {
                    $dueDate = Carbon::parse($wo->due_date)->startOfDay();
                    $daysOverdue = max(1, (int) $dueDate->diffInDays($today));
                } else {
                    $daysOverdue = 1;
                }
            } elseif ($woInProcessItems->contains('id', $wo->id)) {
                $categoryBucket = 'in_process';
            } elseif ($woAllocatedItems->contains('id', $wo->id)) {
                $categoryBucket = 'allocated';
            } elseif ($woNewItems->contains('id', $wo->id)) {
                $categoryBucket = 'new';
            }

            return [
                'id'           => $wo->id,
                'category'     => $categoryBucket,
                'wo_number'    => $wo->work_order_number ?? $wo->order_number ?? ('WO-' . str_pad($wo->id, 5, '0', STR_PAD_LEFT)),
                'due_date'     => $wo->due_date ? Carbon::parse($wo->due_date)->format('d M, Y') : 'N/A',
                'days_overdue' => $daysOverdue,
                'qty'          => $wo->quantity ?? $wo->qty ?? 1,
                'weight_from'  => number_format((float) ($wo->weight_from ?? 0), 3),
                'weight_to'    => number_format((float) ($wo->weight_to ?? 0), 3),
                'status_label' => $wo->craftsman_status ?: ucfirst($wo->status ?? 'New'),
            ];
        });

        return view('buyer.dashboard', compact(
            'buyer', 
            'canManageKeyUsers',
            'productsCount',
            'designsCount',
            'designCategories',
            'categoryDesignsModal',
            'cataloguesCount',
            'workOrdersCount',
            'woNewCount',
            'woAllocatedCount',
            'woInProcessCount',
            'woOverdueCount',
            'woCompletedCount',
            'woNewWeight',
            'woAllocatedWeight',
            'woInProcessWeight',
            'woOverdueWeight',
            'woCompletedWeight',
            'usersCount',
            'keyUsersCount',
            'modalWorkOrders'
        ));
    }

    public function finance()
    {
        return view('buyer.finance.index');
    }
}