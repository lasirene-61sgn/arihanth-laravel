<?php

namespace App\Http\Controllers\KeyUser;

use App\Http\Controllers\Controller;
use App\Models\KeyUser;
use App\Models\Buyer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form for key users.
     */
    public function showLoginForm()
    {
        return view('key-user.login');
    }

    /**
     * Handle login for key users and buyers.
     */
    public function login(Request $request)
    {
        $request->validate([
            'user_code' => 'required|string',
            'password' => 'required|string',
        ]);

        $userCode = $request->user_code;
        $password = $request->password;

        // First, try to authenticate as a key user
        $credentials = [
            'user_code' => $userCode,
            'password' => $password
        ];

        if (Auth::guard('key_user')->attempt($credentials)) {
            // Check if account is frozen
            $keyUser = Auth::guard('key_user')->user();
            if ($keyUser && $keyUser->is_frozen) {
                Auth::guard('key_user')->logout();
                throw ValidationException::withMessages([
                    'user_code' => 'Your account has been frozen. Please contact the Super Admin.',
                ]);
            }
            
            $request->session()->regenerate();
            // Debug: Log successful key user login
            Log::info('Key User Login Success - User Code: ' . $userCode);
            return redirect()->intended(route('key-user.dashboard'));
        }

        // If key user authentication fails, try to authenticate as a buyer
        // Buyers use their BP code as the login identifier
        $buyer = Buyer::where('bp_code', $userCode)->first();

        if ($buyer && Hash::check($password, $buyer->password)) {
            // Check if account is frozen
            if ($buyer->is_frozen) {
                throw ValidationException::withMessages([
                    'user_code' => 'Your account has been frozen. Please contact the Super Admin.',
                ]);
            }
            
            Auth::guard('buyer')->login($buyer);
            $request->session()->regenerate();
            // Debug: Log successful buyer login
            Log::info('Buyer Login Success - BP Code: ' . $userCode);
            return redirect()->intended(route('key-user.dashboard'));
        }

        throw ValidationException::withMessages([
            'user_code' => [trans('auth.failed')],
        ]);
    }

    /**
     * Show the key user dashboard.
     */
    public function dashboard()
{
    $keyUser = Auth::guard('key_user')->user();
    $buyer   = Auth::guard('buyer')->user();
    
    $currentUser = $keyUser ?? $buyer;

    if (!$currentUser) {
        return redirect()->route('key-user.login');
    }

    $bpCode = $currentUser->bp_code;
    $today  = \Carbon\Carbon::today();

    // Standard Counts
    $productsCount   = \App\Models\Product::where('bp_code', $bpCode)->count();
    $designsCount    = \App\Models\Product::where('bp_code', $bpCode)->count();
    $cataloguesCount = \App\Models\Product::where('bp_code', $bpCode)
        ->where('design_status', 'Accepted')
        ->whereNotNull('design_code')
        ->count();
    $usersCount      = \App\Models\User::where('bp_code', $bpCode)->count();

    // Work Orders for this BP Code
    $allWorkOrders    = \App\Models\WorkOrder::where('bp_code', $bpCode)->get();
    $workOrdersCount  = $allWorkOrders->count();

    // 1. Completed
    $woCompletedItems = $allWorkOrders->filter(function ($wo) {
        return strtolower($wo->craftsman_status ?? '') === 'completed';
    });

    // 2. Overdue (Not completed, and past due_date OR explicitly flagged Overdue)
    $woOverdueItems = $allWorkOrders->filter(function ($wo) use ($today) {
        if (strtolower($wo->craftsman_status ?? '') === 'completed') {
            return false;
        }
        if (strtolower($wo->craftsman_status ?? '') === 'overdue') {
            return true;
        }
        return !empty($wo->due_date) && \Carbon\Carbon::parse($wo->due_date)->startOfDay()->lt($today);
    });

    $overdueIds   = $woOverdueItems->pluck('id')->toArray();
    $completedIds = $woCompletedItems->pluck('id')->toArray();
    $excludedIds  = array_merge($overdueIds, $completedIds);

    // 3. In Process (Active non-overdue items)
    $woInProcessItems = $allWorkOrders->filter(function ($wo) use ($excludedIds) {
        return !in_array($wo->id, $excludedIds) && strtolower($wo->craftsman_status ?? '') === 'in process';
    });

    // 4. Allocated (Assigned to production, not started, not overdue)
    $woAllocatedItems = $allWorkOrders->filter(function ($wo) use ($excludedIds) {
        return !in_array($wo->id, $excludedIds) 
            && strtolower($wo->craftsman_status ?? '') !== 'in process'
            && !empty($wo->allocated_craftsman_bp_code);
    });

    // 5. New Orders (Unallocated new status, not overdue)
    $woNewItems = $allWorkOrders->filter(function ($wo) use ($excludedIds) {
        return !in_array($wo->id, $excludedIds) 
            && empty($wo->allocated_craftsman_bp_code)
            && (strtolower($wo->status ?? '') === 'new' || empty($wo->craftsman_status));
    });

    // Analytics Counts & Weights
    $woNewCount        = $woNewItems->count();
    $woAllocatedCount  = $woAllocatedItems->count();
    $woInProcessCount  = $woInProcessItems->count();
    $woCompletedCount  = $woCompletedItems->count();
    $woOverdueCount    = $woOverdueItems->count();

    $woNewWeight       = $woNewItems->sum('weight_to');
    $woAllocatedWeight = $woAllocatedItems->sum('weight_to');
    $woInProcessWeight = $woInProcessItems->sum('weight_to');
    $woCompletedWeight = $woCompletedItems->sum('weight_to');
    $woOverdueWeight   = $woOverdueItems->sum('weight_to');

    // Sanitized Work Orders (No Craftsman Data Included)
    $modalWorkOrders = $allWorkOrders->map(function ($wo) use ($today, $woNewItems, $woAllocatedItems, $woInProcessItems, $woCompletedItems, $woOverdueItems) {
        $categoryBucket = 'other';
        $daysOverdue = 0;

        if ($woCompletedItems->contains('id', $wo->id)) {
            $categoryBucket = 'completed';
        } elseif ($woOverdueItems->contains('id', $wo->id)) {
            $categoryBucket = 'overdue';
            if (!empty($wo->due_date)) {
                $dueDate = \Carbon\Carbon::parse($wo->due_date)->startOfDay();
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
            'due_date'     => $wo->due_date ? \Carbon\Carbon::parse($wo->due_date)->format('d M, Y') : 'N/A',
            'days_overdue' => $daysOverdue,
            'qty'          => $wo->quantity ?? $wo->qty ?? 1,
            'weight_from'  => number_format((float) ($wo->weight_from ?? 0), 3),
            'weight_to'    => number_format((float) ($wo->weight_to ?? 0), 3),
            'status_label' => $wo->craftsman_status ?: ucfirst($wo->status ?? 'New'),
        ];
    });

    return view('key-user.dashboard', [
        'keyUser'           => $currentUser,
        'productsCount'     => $productsCount,
        'designsCount'      => $designsCount,
        'cataloguesCount'   => $cataloguesCount,
        'workOrdersCount'   => $workOrdersCount,
        'usersCount'        => $usersCount,
        'woNewCount'        => $woNewCount,
        'woAllocatedCount'  => $woAllocatedCount,
        'woInProcessCount'  => $woInProcessCount,
        'woCompletedCount'  => $woCompletedCount,
        'woOverdueCount'    => $woOverdueCount,
        'woNewWeight'       => $woNewWeight,
        'woAllocatedWeight' => $woAllocatedWeight,
        'woInProcessWeight' => $woInProcessWeight,
        'woCompletedWeight' => $woCompletedWeight,
        'woOverdueWeight'   => $woOverdueWeight,
        'modalWorkOrders'   => $modalWorkOrders,
    ]);
}

    /**
     * Log out the key user or buyer.
     */
    public function logout(Request $request)
    {
        // Logout from both guards to ensure clean session
        Auth::guard('key_user')->logout();
        Auth::guard('buyer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('key-user.login');
    }

    /**
     * Show the key user finance page.
     */
    public function finance()
    {
        return view('key-user.finance.index');
    }
}