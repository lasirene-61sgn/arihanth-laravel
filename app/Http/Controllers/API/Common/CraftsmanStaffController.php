<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\CraftsmanStaff;
use App\Models\Craftman;
use Illuminate\Support\Facades\Log;

class CraftsmanStaffController extends Controller
{
    /**
     * Get scoping craftsman_id based on user role
     */
    private function getScopeCraftsmanId($user)
    {
        $role = $user->role ?? null;
        if ($role === 'super_admin' || $role === 'admin') {
            return null; // Global access
        }

        if ($role === 'craftsman' || $user instanceof Craftman) {
            return $user->id;
        }

        return -1; // Unauthorized by default for others
    }

    /**
     * Display a listing of craftsman staff
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role ?? null;

        $craftsmanId = $this->getScopeCraftsmanId($user);

        if ($craftsmanId === -1) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // ── Sort ──
        $sortBy    = $request->get('sort_by', 'id');
        $sortOrder = strtolower($request->get('sort') ?: $request->get('sort_order', 'asc'));

        $allowedSortColumns = [
            'id',
            'staff_code',
            'name',
            'email',
            'mobile',
            'is_active',
            'created_at',
        ];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        $query = CraftsmanStaff::with('craftsman');

        if ($craftsmanId) {
            $query->where('craftsman_id', $craftsmanId);
        }

        // ── Search ──
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('staff_code', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        // ── Filters ──
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        if ($request->filled('craftsman_id')) {
            $query->where('craftsman_id', $request->craftsman_id);
        }
        if ($request->filled('staff_code')) {
            $query->where('staff_code', $request->staff_code);
        }
        if ($request->filled('mobile')) {
            $query->where('mobile', $request->mobile);
        }
        if ($request->filled('email')) {
            $query->where('email', $request->email);
        }
        if ($request->filled('name')) {
            $query->where('name', $request->name);
        }
        // ── Selected IDs (for print/export selected) ──
        if ($request->filled('ids')) {
            $ids = $request->ids;
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }
            if (is_array($ids)) {
                $query->whereIn('id', $ids);
            }
        }

        $query->orderBy($sortBy, $sortOrder);

        // ── Export (CSV download) ──
        if ($request->has('export')) {
            $staffs = $query->get();

            $exportData = $staffs->map(function ($staff) {
                return [
                    'Staff Code' => $staff->staff_code,
                    'Name' => $staff->name,
                    'Email' => $staff->email,
                    'Mobile No' => $staff->mobile,
                    'Craftsman' => $staff->craftsman ? $staff->craftsman->name : '',
                    'Status' => $staff->is_active == 1 ? 'Active' : 'Inactive',
                    'Created At' => $staff->created_at ? $staff->created_at->format('Y-m-d') : '',
                ];
            });

            $filename = 'craftsman_staff_' . now()->format('Y-m-d_H-i-s') . '.csv';
            $headers  = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            return response()->stream(function () use ($exportData) {
                $file = fopen('php://output', 'w');
                if ($exportData->isNotEmpty()) {
                    fputcsv($file, array_keys($exportData->first()));
                    foreach ($exportData as $row) {
                        fputcsv($file, $row);
                    }
                }
                fclose($file);
            }, 200, $headers);
        }

        // ── Print (full data, no pagination) ──
        if ($request->has('print')) {
            $staffs = $query->get();

            return response()->json([
                'success' => true,
                'data'    => $staffs,
            ]);
        }

        // ── Paginated list ──
        return response()->json([
            'success' => true,
            'data' => $query->paginate($request->get('per_page', 10))
        ]);
    }

    /**
     * Store a newly created craftsman staff
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $craftsmanId = $this->getScopeCraftsmanId($user);

        if ($craftsmanId === -1) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // If Admin is creating, they must provide a craftsman_id
        if (is_null($craftsmanId) && !$request->filled('craftsman_id')) {
            return response()->json(['message' => 'Craftsman ID is required for admin'], 422);
        }

        $finalCraftsmanId = $craftsmanId ?? $request->craftsman_id;

        $validator = Validator::make($request->all(), [
            'staff_code' => 'required|string|unique:craftsman_staff,staff_code',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:craftsman_staff,email',
            'mobile' => 'required|string|unique:craftsman_staff,mobile',
            'password' => 'required|string|min:8',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aadhar_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'permissions' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'craftsman_id' => $finalCraftsmanId,
            'staff_code' => $request->staff_code,
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'password' => bcrypt($request->password),
            'password_plain' => $request->password,
            'aadhar_number' => $request->aadhar_number,
            'permissions' => $request->input('permissions', []),
            'is_active' => $request->input('is_active', 1),
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('craftsman_staff/profiles', 'public');
        }

        if ($request->hasFile('aadhar_image')) {
            $data['aadhar_image'] = $request->file('aadhar_image')->store('craftsman_staff/aadhar', 'public');
        }

        $staff = CraftsmanStaff::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Craftsman Staff created successfully',
            'data' => $staff
        ], 201);
    }

    /**
     * Display the specified craftsman staff
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $craftsmanId = $this->getScopeCraftsmanId($user);

        $query = CraftsmanStaff::with('craftsman');
        if ($craftsmanId && $craftsmanId !== -1) {
            $query->where('craftsman_id', $craftsmanId);
        } else if ($craftsmanId === -1) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $staff = $query->find($id);
        if (!$staff) {
            return response()->json(['message' => 'Craftsman Staff not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $staff
        ]);
    }

    /**
     * Update the specified craftsman staff
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $craftsmanId = $this->getScopeCraftsmanId($user);

        if ($craftsmanId === -1) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $query = CraftsmanStaff::query();
        if ($craftsmanId) {
            $query->where('craftsman_id', $craftsmanId);
        }

        $staff = $query->find($id);
        if (!$staff) {
            return response()->json(['message' => 'Craftsman Staff not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'staff_code' => 'required|string|unique:craftsman_staff,staff_code,' . $staff->id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:craftsman_staff,email,' . $staff->id,
            'mobile' => 'required|string|unique:craftsman_staff,mobile,' . $staff->id,
            'password' => 'nullable|string|min:8',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aadhar_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'permissions' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = [
            'staff_code' => $request->staff_code,
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'aadhar_number' => $request->aadhar_number,
        ];

        if ($request->has('password') && !empty($request->password)) {
            $updateData['password'] = bcrypt($request->password);
            $updateData['password_plain'] = $request->password;
        }

        if ($request->has('permissions')) {
            $updateData['permissions'] = $request->permissions;
        }

        if ($request->has('is_active')) {
            $updateData['is_active'] = $request->is_active;
        }

        if ($request->hasFile('image')) {
            $updateData['image'] = $request->file('image')->store('craftsman_staff/profiles', 'public');
        }

        if ($request->hasFile('aadhar_image')) {
            $updateData['aadhar_image'] = $request->file('aadhar_image')->store('craftsman_staff/aadhar', 'public');
        }

        $staff->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Craftsman Staff updated successfully',
            'data' => $staff
        ]);
    }

    /**
     * Remove the specified craftsman staff
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $craftsmanId = $this->getScopeCraftsmanId($user);

        if ($craftsmanId === -1) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $query = CraftsmanStaff::query();
        if ($craftsmanId) {
            $query->where('craftsman_id', $craftsmanId);
        }

        $staff = $query->find($id);
        if (!$staff) {
            return response()->json(['message' => 'Craftsman Staff not found'], 404);
        }

        $staff->delete();
        return response()->json(['success' => true, 'message' => 'Craftsman Staff deleted successfully']);
    }
}
