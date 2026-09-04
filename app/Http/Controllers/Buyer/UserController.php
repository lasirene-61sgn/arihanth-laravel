<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $buyer = Auth::guard('buyer')->user();
        
        // 1. Security Check (ensure buyer active)
        if ($buyer->is_frozen) {
             return redirect()->route('buyer.dashboard')->with('error', 'Account is frozen.');
        }

        // 2. Start Query - Only get users for this buyer's BP code
        $query = User::where('bp_code', $buyer->bp_code);

        // 3. Handle QUICK SEARCH
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%") // Note: User model uses 'email', KeyUser uses 'email_id' often, but User model has both in fillable? let's check User model again. 
                  // User model has 'email' and 'email_id'. Standard Laravel uses email. Let's check User model dump.
                  // User model fillable has 'email' and 'email_id'. Unique check usually on email.
                  ->orWhere('user_code', 'like', "%{$search}%")
                  ->orWhere('mobile_no', 'like', "%{$search}%");
            });
        }

        // 4. Handle ADVANCED FILTERS
        if ($request->filled('filter_name')) {
            $query->where('full_name', 'like', '%' . $request->filter_name . '%');
        }
        if ($request->filled('filter_code')) {
            $query->where('user_code', 'like', '%' . $request->filter_code . '%');
        }
        if ($request->filled('filter_email')) {
            $query->where('email', 'like', '%' . $request->filter_email . '%');
        }
        if ($request->filled('filter_mobile')) {
            $query->where('mobile_no', 'like', '%' . $request->filter_mobile . '%');
        }

        // 5. Handle SORTING
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        $query->orderBy($sort, $direction);

        // 6. Final Execution
        $users = $query->paginate(10);
        
        return view('buyer.user.index', compact('users'));
    }

    public function create()
    {
        return view('buyer.user.create');
    }

    public function store(Request $request)
    {
        $currentBuyer = Auth::guard('buyer')->user();
        
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile_no' => 'required|string|unique:users,mobile_no',
            'password' => 'required|string|min:8|confirmed',
            'permissions' => 'array',
            'permissions.*' => Rule::in(User::getAllPermissions()),
        ]);

        $user = User::create([
            'user_code' => User::generateUserCode(),
            'bp_code' => $currentBuyer->bp_code,
            'name' => $request->full_name, // User model often uses name for auth
            'full_name' => $request->full_name,
            'email' => $request->email,
            'email_id' => $request->email, // Syncing both if schema requires
            'mobile_no' => $request->mobile_no,
            'password' => bcrypt($request->password),
            'password_plain' => $request->password,
            'permissions' => $request->permissions ?? [],
            'status' => 1,
            'is_frozen' => false,
            'created_by' => $currentBuyer->id, // Tracking who created it (ID might refer to User table, but here it's Buyer... might be nullable or polymorphic. sticking to simple for now)
        ]);

        return redirect()->route('buyer.user-management.index')
                        ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $buyer = Auth::guard('buyer')->user();
        
        if ($user->bp_code !== $buyer->bp_code) {
            return redirect()->route('buyer.user-management.index')->with('error', 'Access denied.');
        }
        
        return view('buyer.user.show', compact('user'));
    }

    public function edit(User $user)
    {
        $buyer = Auth::guard('buyer')->user();
        
        if ($user->bp_code !== $buyer->bp_code) {
            return redirect()->route('buyer.user-management.index')->with('error', 'Access denied.');
        }
        
        return view('buyer.user.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $buyer = Auth::guard('buyer')->user();
        
        if ($user->bp_code !== $buyer->bp_code) {
            return redirect()->route('buyer.user-management.index')->with('error', 'Access denied.');
        }
        
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile_no' => 'required|string|unique:users,mobile_no,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'permissions' => 'array',
            'permissions.*' => Rule::in(User::getAllPermissions()),
        ]);

        $user->update([
            'name' => $request->full_name,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'email_id' => $request->email,
            'mobile_no' => $request->mobile_no,
            'password' => $request->password ? bcrypt($request->password) : $user->password,
            'password_plain' => $request->password ? $request->password : $user->password_plain,
            'permissions' => $request->permissions ?? $user->permissions,
        ]);

        return redirect()->route('buyer.user-management.index')
                        ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $buyer = Auth::guard('buyer')->user();
        
        if ($user->bp_code !== $buyer->bp_code) {
            return redirect()->route('buyer.user-management.index')->with('error', 'Access denied.');
        }
        
        $user->delete();

        return redirect()->route('buyer.user-management.index')
                        ->with('success', 'User deleted successfully.');
    }
}
