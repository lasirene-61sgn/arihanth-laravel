<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Exports\AdminUserExport;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    

public function index(Request $request)
{
    $query = User::with(['createdBy', 'buyer']);

    // --- SEARCH & FILTERS ---
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('full_name', 'like', "%{$search}%")
              ->orWhere('user_code', 'like', "%{$search}%")
              ->orWhere('email_id', 'like', "%{$search}%");
        });
    }

    if ($request->filled('filter_user_code')) {
        $query->where('user_code', 'like', '%' . $request->filter_user_code . '%');
    }
    if ($request->filled('filter_name')) {
        $query->where('full_name', 'like', '%' . $request->filter_name . '%');
    }
    if ($request->filled('filter_email')) {
        $query->where('email_id', 'like', '%' . $request->filter_email . '%');
    }
    if ($request->filled('filter_mobile')) {
        $query->where('mobile_no', 'like', '%' . $request->filter_mobile . '%');
    }
    if ($request->filled('filter_bp_code')) {
        $query->whereHas('buyer', function($q) {
            $q->where('bp_code', 'like', '%' . request('filter_bp_code') . '%');
        });
    }

    // --- SORTING ---
    $sort = $request->get('sort', 'latest');
    if ($sort == 'name_asc') $query->orderBy('full_name', 'asc');
    elseif ($sort == 'name_desc') $query->orderBy('full_name', 'desc');
    else $query->latest();

    $users = $query->paginate(15)->withQueryString();

    return view('admin.user.index', compact('users'));
}



    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $buyers = \App\Models\Buyer::all();
        return view('admin.user.create', compact('buyers'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bp_code' => 'required|string|exists:buyers,bp_code',
            'full_name' => 'required|string|max:255',
            'email_id' => 'required|email|unique:users,email_id',
            'mobile_no' => 'required|string|max:15',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'required|in:active,inactive',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'aadhar_number' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Generate user code
        $userCode = User::generateUserCode();

        $user = User::create([
            'user_code' => $userCode,
            'bp_code' => $request->bp_code,
            'full_name' => $request->full_name,
            'name' => $request->full_name,
            'email_id' => $request->email_id,
            'email' => $request->email_id,
            'mobile_no' => $request->mobile_no,
            'password' => Hash::make($request->password),
            'status' => 'active', // Always create as active
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'pincode' => $request->pincode,
            'aadhar_number' => $request->aadhar_number,
            'permissions' => $request->permissions ?? [],
            'created_by' => auth()->guard('admin')->id(), // Set the creator
        ]);

        return redirect()->route('admin.user.index')
            ->with('success', 'User created successfully with code: ' . $userCode);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return view('admin.user.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $buyers = \App\Models\Buyer::all();
        return view('admin.user.edit', compact('user', 'buyers'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'bp_code' => 'required|string|exists:buyers,bp_code',
            'full_name' => 'required|string|max:255',
            'email_id' => 'required|email|unique:users,email_id,' . $user->id,
            'mobile_no' => 'required|string|max:15',
            'status' => 'required|in:active,inactive',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'aadhar_number' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user->update([
            'bp_code' => $request->bp_code,
            'full_name' => $request->full_name,
            'name' => $request->full_name,
            'email_id' => $request->email_id,
            'email' => $request->email_id,
            'mobile_no' => $request->mobile_no,
            'status' => $request->status,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'pincode' => $request->pincode,
            'aadhar_number' => $request->aadhar_number,
            'permissions' => $request->permissions ?? [],
        ]);

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.user.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.user.index')
            ->with('success', 'User deleted successfully!');
    }

    public function export(Request $request) 
{
    return Excel::download(new AdminUserExport($request), 'AdminUserExport_' . now()->format('d-m-Y') . '.xlsx');
}

    public function printSelected(Request $request)
    {
        $ids = $request->input('selected_users', []);
        $users = User::whereIn('id', $ids)->get();
        return view('admin.user.print-selected', compact('users'));
    }
}