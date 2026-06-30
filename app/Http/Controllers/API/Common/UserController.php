<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Buyer;
use App\Models\KeyUser;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Get scoping bp_code based on user role
     */
    private function getScopeBpCode($user)
    {
        $role = $user->role ?? null;
        if ($role === 'super_admin' || $role === 'admin') {
            return null; // Global access
        }

        // Buyers and KeyUsers have bp_code
        return $user->bp_code;
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role ?? null;

        // SuperAdmin, Admin, Buyer, and KeyUser can all list/manage users (scoped to their BP)
        $isAuthorized = ($role === 'super_admin' || $role === 'admin' || $role === 'buyer' || $role === 'key_user' ||
            $user instanceof Buyer || $user instanceof KeyUser);

        if (!$isAuthorized) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // ── Sort ──
        $sortBy    = $request->get('sort_by', 'id');
        $sortOrder = strtolower($request->get('sort')? : $request->get('sort_order', 'asc'));

        $allowedSortColumns = [
            'id',
            'user_code',
            'full_name',
            'email',
            'mobile_no',
            'pincode',
            'state',
            'city',
            'status',
            'is_frozen',
            'created_at',
        ];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        $bpCode = $this->getScopeBpCode($user);
        $query = User::with('buyer');

        if ($bpCode) {
            $query->where('bp_code', $bpCode);
        }
        if ($request->filled('bp_code')) {
            $query->where('bp_code', $request->bp_code);
        }
        if ($request->filled('user_code')) {
            $query->where('user_code', $request->user_code);
        }
        if ($request->filled('mobile_no')) {
            $query->where('mobile_no', $request->mobile_no);
        }
        if ($request->filled('email_id')) {
            $query->where('email_id', $request->email_id);
        }
        if ($request->filled('full_name')) {
            $query->where('full_name', $request->full_name);
        }

        // ── Search ──
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('user_code', 'like', "%{$search}%");
            });
        }

        // ── Filters ──
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_frozen')) {
            $query->where('is_frozen', $request->is_frozen);
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
            $endUsers = $query->get();

            $exportData = $endUsers->map(function ($endUser) {
                return [
                    'User Code' => $endUser->user_code,
                    'Full Name' => $endUser->full_name,
                    'Email'     => $endUser->email,
                    'Mobile No' => $endUser->mobile_no,
                    'BP Code'   => $endUser->bp_code . ($endUser->buyer ? ' ' . $endUser->buyer->business_name : ''),
                    'City'      => $endUser->city,
                    'State'     => $endUser->state,
                    'Status'    => $endUser->status == 1 ? 'Active' : 'Inactive',
                    'Frozen'    => $endUser->is_frozen ? 'Yes' : 'No',
                    'Created At' => $endUser->created_at ? $endUser->created_at->format('Y-m-d') : '',
                ];
            });

            $filename = 'users_' . now()->format('Y-m-d_H-i-s') . '.csv';
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
            $endUsers = $query->get();

            return response()->json([
                'success' => true,
                'data'    => $endUsers,
            ]);
        }

        // ── Paginated list ──
        return response()->json([
            'success' => true,
            'data' => $query->paginate($request->get('per_page', 10))
        ]);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $role = $user->role ?? null;

        $isAuthorized = ($role === 'super_admin' || $role === 'admin' || $role === 'buyer' || $role === 'key_user' ||
            $user instanceof Buyer || $user instanceof KeyUser);

        if (!$isAuthorized) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $bpCode = $this->getScopeBpCode($user);

        // If Admin is creating, they must provide a bp_code
        if (is_null($bpCode) && !$request->filled('bp_code')) {
            return response()->json(['message' => 'Business Partner (bp_code) is required for admin'], 422);
        }

        $finalBpCode = $bpCode ?? $request->bp_code;

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile_no' => 'required|string|unique:users,mobile_no',
            'password' => 'required|string|min:8',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aadhar_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'permissions' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'user_code' => User::generateUserCode(),
            'bp_code' => $finalBpCode,
            'name' => $request->full_name,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'email_id' => $request->email, // syncing email/email_id as per existing pattern
            'mobile_no' => $request->mobile_no,
            'password' => bcrypt($request->password),
            'password_plain' => $request->password,
            'pincode' => $request->pincode,
            'state' => $request->state,
            'aadhar_number' => $request->aadhar_number,
            'city' => $request->city,
            'permissions' => $request->input('permissions', ['product', 'design', 'catalogue', 'work_order']),
            'status' => 1,
            'is_frozen' => false,
            'created_by' => $user->id,
            'creator_type' => $user->role ?? 'unknown'
        ];

        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('users/profiles', 'public');
        }

        if ($request->hasFile('aadhar_photo')) {
            $data['aadhar_photo'] = $request->file('aadhar_photo')->store('users/aadhar', 'public');
        }

        $newUser = User::create($data);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $newUser
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $bpCode = $this->getScopeBpCode($user);

        $query = User::with('buyer');
        if ($bpCode) {
            $query->where('bp_code', $bpCode);
        }

        $endUser = $query->find($id);
        if (!$endUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $endUser
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $bpCode = $this->getScopeBpCode($user);

        $query = User::query();
        if ($bpCode) {
            $query->where('bp_code', $bpCode);
        }

        $endUser = $query->find($id);
        if (!$endUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $endUser->id,
            'mobile_no' => 'required|string|unique:users,mobile_no,' . $endUser->id,
            'password' => 'nullable|string|min:8',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aadhar_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'permissions' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = [
            'name' => $request->full_name,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'email_id' => $request->email,
            'mobile_no' => $request->mobile_no,
            'password' => $request->password ? bcrypt($request->password) : $endUser->password,
            'password_plain' => $request->password ?? $endUser->password_plain,
            'pincode' => $request->pincode,
            'state' => $request->state,
            'aadhar_number' => $request->aadhar_number,
            'city' => $request->city,
            'permissions' => $request->input('permissions', ['product', 'design', 'catalogue', 'work_order']),
            'is_frozen' => $request->is_frozen ?? $endUser->is_frozen,
            'status' => $request->status ?? $endUser->status,
        ];

        if ($request->hasFile('profile_picture')) {
            $updateData['profile_picture'] = $request->file('profile_picture')->store('users/profiles', 'public');
        }

        if ($request->hasFile('aadhar_photo')) {
            $updateData['aadhar_photo'] = $request->file('aadhar_photo')->store('users/aadhar', 'public');
        }

        $endUser->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $endUser
        ]);
    }

    /**
     * Remove the specified user
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $bpCode = $this->getScopeBpCode($user);

        $query = User::query();
        if ($bpCode) {
            $query->where('bp_code', $bpCode);
        }

        $endUser = $query->find($id);
        if (!$endUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $endUser->delete();
        return response()->json(['success' => true, 'message' => 'User deleted successfully']);
    }

    /**
     * Generate PDF for selected users
     */
    public function generatePdf(Request $request)
    {
        $user = $request->user();
        $bpCode = $this->getScopeBpCode($user);

        $query = User::query();
        if ($bpCode) {
            $query->where('bp_code', $bpCode);
        }

        // ── Selected IDs (for PDF/print selected) ──
        if ($request->filled('ids')) {
            $ids = $request->ids;
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }
            if (is_array($ids)) {
                $query->whereIn('id', $ids);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'No IDs provided'], 400);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No users found'], 404);
        }

        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'sans-serif');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(view('api.common.users.generate-pdf', compact('users'))->render());
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = count($users) === 1
                ? "User_" . $users->first()->user_code . ".pdf"
                : "Users_Report_" . now()->format('Ymd_His') . ".pdf";

            return response($dompdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Access-Control-Expose-Headers', 'Content-Disposition');
        } catch (\Exception $e) {
            Log::error('User PDF Generation Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate PDF. ' . $e->getMessage()], 500);
        }
    }
}
