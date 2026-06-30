<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Exports\AdminExport;
use App\Http\Controllers\Controller;
use App\Models\ProcessOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    /**
     * Display a listing of the admins.
     */
    public function index(Request $request)
    {
        $query = ProcessOwner::where('role', 'admin');

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('user_code', 'LIKE', "%$searchTerm%")
                    ->orWhere('bp_code', 'LIKE', "%$searchTerm%")
                    ->orWhere('full_name', 'LIKE', "%$searchTerm%")
                    ->orWhere('email_id', 'LIKE', "%$searchTerm%")
                    ->orWhere('mobile_no', 'LIKE', "%$searchTerm%")
                    ->orWhere('city', 'LIKE', "%$searchTerm%")
                    ->orWhere('state', 'LIKE', "%$searchTerm%")
                    ->orWhere('country', 'LIKE', "%$searchTerm%");
            });
        }

        // Sorting functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSortColumns = ['user_code', 'bp_code', 'full_name', 'email_id', 'mobile_no', 'status', 'city', 'state', 'country', 'created_at', 'updated_at'];
        $allowedDirections = ['asc', 'desc'];

        // Validate and sanitize sort parameters
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        if (!in_array(strtolower($sortOrder), $allowedDirections)) {
            $sortOrder = 'desc';
        }

        $query->orderBy($sortBy, strtolower($sortOrder));

        // Filter functionality
        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }

        $admins = $query->get();

        // Export functionality
        if ($request->has('export')) {
            return Excel::download(new AdminExport(), 'admins_' . date('Y-m-d_H-i-s') . '.xlsx');
        }

        // In your Controller index method
        if ($request->filled('erp_type')) {
            $query->where('erp_type', $request->erp_type); // Assuming column name is erp_type
        }

        return view('super-admin.admin.index', compact('admins'));
    }

    /**
     * Show the form for creating a new admin.
     */
    public function create()
    {
        $categories = \App\Models\AdminCategory::where('status', 1)->get();
        return view('super-admin.admin.create', compact('categories'));
    }

    /**
     * Store a newly created admin in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email_id' => 'required|email|max:255|unique:process_owners,email_id',
            'mobile_no' => 'required|string|max:15|unique:process_owners,mobile_no',
            'password' => 'required|string|min:8|confirmed',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'aadhar_number' => 'nullable|string|max:20',
            'user_code' => 'required|string|unique:process_owners,user_code',
            'dear' => 'nullable|string|unique:process_owners,dear',
            'category' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $admin = ProcessOwner::create([
            'user_code' => $request->user_code,
            'dear' => $request->dear,
            'bp_code' => $request->bp_code,
            'full_name' => $request->full_name,
            'email_id' => $request->email_id,
            'mobile_no' => $request->mobile_no,
            'password' => Hash::make($request->password),
            'password_plain' => $request->password,
            'status' => $request->status,
            'dob' => $request->dob,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'pincode' => $request->pincode,
            'aadhar_number' => $request->aadhar_number,
            'role' => 'admin', // Set role as admin
            'category' => $request->category,
            'permissions' => json_encode($request->permissions ?? []),
        ]);

        return redirect()->route('super-admin.admin.index')
            ->with('success', 'Admin created successfully with code: ' . $request->user_code);
    }

    /**
     * Display the specified admin.
     */
    public function show(ProcessOwner $admin)
    {
        return view('super-admin.admin.show', compact('admin'));
    }

    /**
     * Show the form for editing the specified admin.
     */
    public function edit(ProcessOwner $admin)
    {
        $categories = \App\Models\AdminCategory::where('status', 1)->get();
        return view('super-admin.admin.edit', compact('admin', 'categories'));
    }

    /**
     * Update the specified admin in storage.
     */
    public function update(Request $request, ProcessOwner $admin)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email_id' => 'required|email|max:255|unique:process_owners,email_id,' . $admin->id,
            'mobile_no' => 'required|string|max:15|unique:process_owners,mobile_no,' . $admin->id,
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'aadhar_number' => 'nullable|string|max:20',
            'dear' => 'nullable|string|unique:process_owners,dear,' . $admin->id,
            'category' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $updateData = [
            'bp_code' => $request->bp_code,
            'dear' => $request->dear,
            'full_name' => $request->full_name,
            'email_id' => $request->email_id,
            'mobile_no' => $request->mobile_no,
            'status' => $request->status,
            'dob' => $request->dob,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'pincode' => $request->pincode,
            'aadhar_number' => $request->aadhar_number,
            'category' => $request->category,
        ];

        // Only update password if provided
        if ($request->password) {
            $validator = Validator::make($request->only('password'), [
                'password' => 'string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $updateData['password'] = Hash::make($request->password);
            $updateData['password_plain'] = $request->password;
        }

        // Add permissions to update data if provided
        if ($request->has('permissions')) {
            $updateData['permissions'] = json_encode($request->permissions);
        }

        $admin->update($updateData);

        return redirect()->route('super-admin.admin.index')
            ->with('success', 'Admin updated successfully!');
    }

    /**
     * Remove the specified admin from storage.
     */
    public function destroy(ProcessOwner $admin)
    {
        $admin->delete();

        return redirect()->route('super-admin.admin.index')
            ->with('success', 'Admin deleted successfully!');
    }

    /**
     * Generate user code for admins (AD0001, AD0002, etc.)
     */
    private function generateUserCode()
    {
        $latestAdmin = ProcessOwner::where('role', 'admin')
            ->where('user_code', 'like', 'AD%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$latestAdmin) {
            return 'AD0001';
        }

        $latestCode = $latestAdmin->user_code;
        $number = intval(substr($latestCode, 2)) + 1;
        return 'AD' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function printSelected(Request $request)
    {
        $ids = $request->input('selected_admins', []);
        $admins = ProcessOwner::whereIn('id', $ids)->get();
        return view('super-admin.admin.print-selected', compact('admins'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:admin_categories,name',
        ]);

        $category = \App\Models\AdminCategory::create([
            'name' => strtolower($request->name),
            'status' => 1
        ]);

        return response()->json([
            'success' => true,
            'category' => $category
        ]);
    }
}
