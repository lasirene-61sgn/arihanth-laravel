<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Buyer;
use App\Models\Craftman;
use App\Models\KeyUser;
use App\Models\User;
use App\Models\ProcessOwner;
use Illuminate\Http\Request;

class UserCredentialController extends Controller
{
    /**
     * Display a listing of all users and their credentials.
     */
    /**
     * Display a listing of all users and their credentials.
     */
    public function index(Request $request)
    {
        $allUsers = collect();

        // 1. Admins
        $admins = ProcessOwner::where('role', 'admin')->get()->map(function ($item) {
            return (object) [
                'id' => $item->id,
                'name' => $item->full_name,
                'code' => $item->user_code,
                'bp_code' => $item->bp_code ?? 'N/A',
                'role' => 'Admin',
                'password' => $item->password_plain ?? 'Not Captured',
                'permissions' => $item->permissions ?? []
            ];
        });
        $allUsers = $allUsers->concat($admins);

        // 2. Buyers
        $buyers = Buyer::all()->map(function ($item) {
            return (object) [
                'id' => $item->id,
                'name' => $item->name,
                'code' => 'N/A',
                'bp_code' => $item->bp_code,
                'business_name' => $item->business_name,
                'city' => $item->city,
                'role' => 'Buyer',
                'password' => $item->password_plain ?? 'Not Captured',
                'permissions' => $item->permissions ?? []
            ];
        });
        $allUsers = $allUsers->concat($buyers);

        // 3. Craftsmen
        $craftsmen = Craftman::all()->map(function ($item) {
            return (object) [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->craftman_code,
                'bp_code' => 'N/A',
                'role' => 'Craftsman',
                'business_name' => $item->business_name,
                'city' => $item->city,
                'password' => $item->password_plain ?? 'Not Captured',
                'permissions' => $item->permissions ?? []
            ];
        });
        $allUsers = $allUsers->concat($craftsmen);

        // 4. Craftsman Staff
        $craftsmanStaffs = \App\Models\CraftsmanStaff::with('craftsman')->get()->map(function ($item) {
            return (object) [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->staff_code,
                'bp_code' => $item->craftsman ? $item->craftsman->craftman_code : 'N/A', // Using BP code to show Craftsman code
                'role' => 'Craftsman Staff',
                'business_name' => $item->craftsman ? $item->craftsman->name : 'N/A', // Using business name to show Craftsman Name
                'city' => 'N/A',
                'password' => $item->password_plain ?? 'Not Captured',
                'permissions' => $item->permissions ?? []
            ];
        });
        $allUsers = $allUsers->concat($craftsmanStaffs);

        // 5. Key Users
        $keyUsers = KeyUser::all()->map(function ($item) {
            return (object) [
                'id' => $item->id,
                'name' => $item->full_name,
                'code' => $item->user_code,
                'bp_code' => $item->bp_code,
                'role' => 'Key User',
                'password' => $item->password_plain ?? 'Not Captured',
                'permissions' => $item->permissions ?? []
            ];
        });
        $allUsers = $allUsers->concat($keyUsers);

        // 6. Users
        $regularUsers = User::all()->map(function ($item) {
            return (object) [
                'id' => $item->id,
                'name' => $item->full_name,
                'code' => $item->user_code,
                'bp_code' => $item->bp_code,
                'role' => 'User',
                'password' => $item->password_plain ?? 'Not Captured',
                'permissions' => $item->permissions ?? []
            ];
        });
        $allUsers = $allUsers->concat($regularUsers);

        // Filter: Role
        if ($request->filled('role')) {
            $roleFilter = $request->role;
            $allUsers = $allUsers->filter(fn($u) => $u->role === $roleFilter);
        }

        // Filter: Search
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $allUsers = $allUsers->filter(function ($user) use ($search) {
                return str_contains(strtolower($user->name), $search) ||
                    str_contains(strtolower($user->code), $search) ||
                    str_contains(strtolower($user->bp_code), $search) ||
                    str_contains(strtolower($user->role), $search) ||
                    (isset($user->business_name) && str_contains(strtolower($user->business_name), $search));
            });
        }

        // Manual Pagination
        $perPage = 15;
        $page = $request->get('page', 1);
        $pagedData = $allUsers->slice(($page - 1) * $perPage, $perPage)->values();
        
        $users = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedData,
            $allUsers->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('super-admin.user-credentials.index', compact('users'));
    }

    /**
     * Show detailed user credentials and security info.
     */
    public function show($role, $id)
    {
        $user = null;
        $roleName = str_replace('-', ' ', $role);

        switch (strtolower($role)) {
            case 'admin':
                $user = ProcessOwner::find($id);
                $user->role_display = 'Admin';
                break;
            case 'buyer':
                $user = Buyer::find($id);
                $user->role_display = 'Buyer';
                break;
            case 'craftsman':
                $user = Craftman::find($id);
                $user->role_display = 'Craftsman';
                break;
            case 'craftsman-staff':
                $user = \App\Models\CraftsmanStaff::with('craftsman')->find($id);
                if ($user) {
                    $user->role_display = 'Craftsman Staff';
                    $user->bp_code = $user->craftsman ? $user->craftsman->craftman_code : 'N/A'; // Provide BP code like variable
                }
                break;
            case 'key-user':
                $user = KeyUser::find($id);
                $user->role_display = 'Key User';
                break;
            case 'user':
                $user = User::find($id);
                $user->role_display = 'User';
                break;
        }

        if (!$user) {
            return redirect()->route('super-admin.user-credentials.index')->with('error', 'User not found.');
        }

        // Standardize some fields for the view
        $user->display_name = $user->full_name ?? $user->name;
        $user->display_code = $user->user_code ?? $user->craftman_code ?? $user->staff_code ?? 'N/A';
        $user->display_bp_code = $user->bp_code ?? 'N/A';

        // Fetch login history
        $loginLogs = \App\Models\LoginLog::where('authenticatable_type', get_class($user))
                        ->where('authenticatable_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->limit(20)
                        ->get();

        return view('super-admin.user-credentials.show', compact('user', 'loginLogs'));
    }
}
