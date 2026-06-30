<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\KeyUser;
use App\Models\Buyer;
use Illuminate\Validation\Rule;
use App\Exports\BuyerKeyUserExport;
use Maatwebsite\Excel\Facades\Excel;

class KeyUserController extends Controller
{
    public function index(Request $request)
{
    $buyer = Auth::guard('buyer')->user();
    
    // 1. Security Check
    if (!$buyer->hasPermission('key_user')) {
        return redirect()->route('buyer.dashboard')->with('error', 'Access denied.');
    }

    // 2. Start Query - Only get key users for this buyer's BP code
    $query = KeyUser::with('buyer')->where('bp_code', $buyer->bp_code);

    // 3. Handle QUICK SEARCH (General Box)
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('full_name', 'like', "%{$search}%")
              ->orWhere('email_id', 'like', "%{$search}%")
              ->orWhere('user_code', 'like', "%{$search}%")
              ->orWhere('mobile_no', 'like', "%{$search}%");
        });
    }

    // 4. Handle ADVANCED FILTERS (Detailed Dropdown)
    if ($request->filled('filter_name')) {
        $query->where('full_name', 'like', '%' . $request->filter_name . '%');
    }
    if ($request->filled('filter_code')) {
        $query->where('user_code', 'like', '%' . $request->filter_code . '%');
    }
    if ($request->filled('filter_email')) {
        $query->where('email_id', 'like', '%' . $request->filter_email . '%');
    }
    if ($request->filled('filter_mobile')) {
        $query->where('mobile_no', 'like', '%' . $request->filter_mobile . '%');
    }
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // 5. Handle SORTING (Rearrange Table)
    $sort = $request->get('sort', 'created_at');
    $direction = $request->get('direction', 'desc');
    $query->orderBy($sort, $direction);

    // 6. Final Execution
    $keyUsers = $query->paginate(10);
    
    return view('buyer.key-user.index', compact('keyUsers'));
}

    public function create()
    {
        $buyer = Auth::guard('buyer')->user();
        
        // Check if buyer has key_user permission
        if (!$buyer->hasPermission('key_user')) {
            return redirect()->route('buyer.dashboard')->with('error', 'Access denied.');
        }
        
        $buyerModel = Auth::guard('buyer')->user();
        $buyers = collect([$buyerModel]); // Only allow the current buyer's BP code
        return view('buyer.key-user.create', compact('buyers'));
    }

    public function store(Request $request)
    {
        $buyer = Auth::guard('buyer')->user();
        
        // Check if buyer has key_user permission
        if (!$buyer->hasPermission('key_user')) {
            return redirect()->route('buyer.dashboard')->with('error', 'Access denied.');
        }

        // Get the current buyer to validate BP code
        $currentBuyer = Auth::guard('buyer')->user();
        
        $request->validate([
            'bp_code' => 'required|string|in:' . $currentBuyer->bp_code, // Only allow current buyer's BP code
            'full_name' => 'required|string|max:255',
            'email_id' => 'required|email|unique:key_users,email_id',
            'mobile_no' => 'required|string|unique:key_users,mobile_no',
            'password' => 'required|string|min:8|confirmed',
            'permissions' => 'array',
            'permissions.*' => Rule::in(KeyUser::getAllPermissions()),
        ]);

        $keyUser = KeyUser::create([
            'user_code' => KeyUser::generateUserCode(),
            'bp_code' => $currentBuyer->bp_code, // Force to current buyer's BP code
            'full_name' => $request->full_name,
            'email_id' => $request->email_id,
            'mobile_no' => $request->mobile_no,
            'password' => bcrypt($request->password),
            'permissions' => $request->permissions ?? [],
            'status' => 1, // 1 for active, 0 for inactive
        ]);

        return redirect()->route('buyer.key-user-management.index')
                        ->with('success', 'Key User created successfully.');
    }

    public function show(KeyUser $keyUser)
    {
        $buyer = Auth::guard('buyer')->user();
        
        // Check if buyer has key_user permission
        if (!$buyer->hasPermission('key_user')) {
            return redirect()->route('buyer.dashboard')->with('error', 'Access denied.');
        }
        
        // Ensure the key user belongs to the current buyer
        if ($keyUser->bp_code !== $buyer->bp_code) {
            return redirect()->route('buyer.key-user-management.index')->with('error', 'Access denied. This key user does not belong to your organization.');
        }
        
        return view('buyer.key-user.show', compact('keyUser'));
    }

    public function edit(KeyUser $keyUser)
    {
        $buyer = Auth::guard('buyer')->user();
        
        // Check if buyer has key_user permission
        if (!$buyer->hasPermission('key_user')) {
            return redirect()->route('buyer.dashboard')->with('error', 'Access denied.');
        }
        
        // Ensure the key user belongs to the current buyer
        if ($keyUser->bp_code !== $buyer->bp_code) {
            return redirect()->route('buyer.key-user-management.index')->with('error', 'Access denied. This key user does not belong to your organization.');
        }
        
        $buyerModel = Auth::guard('buyer')->user();
        $buyers = collect([$buyerModel]); // Only allow the current buyer's BP code
        return view('buyer.key-user.edit', compact('keyUser', 'buyers'));
    }

    public function update(Request $request, KeyUser $keyUser)
    {
        $buyer = Auth::guard('buyer')->user();
        
        // Check if buyer has key_user permission
        if (!$buyer->hasPermission('key_user')) {
            return redirect()->route('buyer.dashboard')->with('error', 'Access denied.');
        }

        // Get the current buyer to validate BP code
        $currentBuyer = Auth::guard('buyer')->user();
        
        $request->validate([
            'bp_code' => 'required|string|in:' . $currentBuyer->bp_code, // Only allow current buyer's BP code
            'full_name' => 'required|string|max:255',
            'email_id' => 'required|email|unique:key_users,email_id,' . $keyUser->id,
            'mobile_no' => 'required|string|unique:key_users,mobile_no,' . $keyUser->id,
            'password' => 'nullable|string|min:8|confirmed',
            'permissions' => 'array',
            'permissions.*' => Rule::in(KeyUser::getAllPermissions()),
        ]);

        $keyUser->update([
            'bp_code' => $currentBuyer->bp_code, // Force to current buyer's BP code
            'full_name' => $request->full_name,
            'email_id' => $request->email_id,
            'mobile_no' => $request->mobile_no,
            'password' => $request->password ? bcrypt($request->password) : $keyUser->password,
            'permissions' => $request->permissions ?? $keyUser->permissions,
            // 'status' => $request->status ?? $keyUser->status,
        ]);

        return redirect()->route('buyer.key-user-management.index')
                        ->with('success', 'Key User updated successfully.');
    }

    public function destroy(KeyUser $keyUser)
    {
        $buyer = Auth::guard('buyer')->user();
        
        // Check if buyer has key_user permission
        if (!$buyer->hasPermission('key_user')) {
            return redirect()->route('buyer.dashboard')->with('error', 'Access denied.');
        }
        
        // Ensure the key user belongs to the current buyer
        if ($keyUser->bp_code !== $buyer->bp_code) {
            return redirect()->route('buyer.key-user-management.index')->with('error', 'Access denied. This key user does not belong to your organization.');
        }
        
        $keyUser->delete();

        return redirect()->route('buyer.key-user-management.index')
                        ->with('success', 'Key User deleted successfully.');
    }

    public function export(Request $request)
{
    return Excel::download(new BuyerKeyUserExport($request), 'Key_Users_' . now()->format('d-m-Y') . '.xlsx');
}
}