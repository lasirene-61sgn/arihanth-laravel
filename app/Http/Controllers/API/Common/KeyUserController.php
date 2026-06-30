<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\KeyUser;
use App\Models\Buyer;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;

class KeyUserController extends Controller
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
     * Display a listing of key users
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role ?? null;

        $isAuthorized = ($role === 'super_admin' || $role === 'admin' || $role === 'buyer' || $user instanceof Buyer);

        if (!$isAuthorized) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // ── Sort ──
        $sortBy    = $request->get('sort_by', 'id');
        $sortOrder = strtolower($request->get('sort') ?:$request->get('sort_order', 'asc'));
            

        $allowedSortColumns = [
            'id',
            'user_code',
            'full_name',
            'email_id',
            'mobile_no',
            'pincode',
            'state',
            'city',
            'status',
            'created_at',
        ];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        $bpCode = $this->getScopeBpCode($user);
        $query = KeyUser::with('buyer');

        if ($bpCode) {
            $query->where('bp_code', $bpCode);
        }

        // ── Search ──
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email_id', 'like', "%{$search}%")
                    ->orWhere('user_code', 'like', "%{$search}%");
            });
        }

        // ── Filters ──
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
            $keyUsers = $query->get();

            $exportData = $keyUsers->map(function ($keyUser) {
                return [
                    'User Code' => $keyUser->user_code,
                    'Full Name' => $keyUser->full_name,
                    'Email'     => $keyUser->email_id,
                    'Mobile No' => $keyUser->mobile_no,
                    'BP Code'   => $keyUser->bp_code . ($keyUser->buyer ? ' ' . $keyUser->buyer->business_name : ''),
                    'City'      => $keyUser->city,
                    'State'     => $keyUser->state,
                    'Status'    => $keyUser->status == 1 ? 'Active' : 'Inactive',
                    'Created At' => $keyUser->created_at ? $keyUser->created_at->format('Y-m-d') : '',
                ];
            });

            $filename = 'key_users_' . now()->format('Y-m-d_H-i-s') . '.csv';
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
            $keyUsers = $query->get();

            return response()->json([
                'success' => true,
                'data'    => $keyUsers,
            ]);
        }

        // ── Paginated list ──
        return response()->json([
            'success' => true,
            'data' => $query->paginate($request->get('per_page', 10))
        ]);
    }

    /**
     * Store a newly created key user
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $role = $user->role ?? null;

        $isAuthorized = ($role === 'super_admin' || $role === 'admin' || $role === 'buyer' || $user instanceof Buyer);

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
            'email_id' => 'required|email|unique:key_users,email_id',
            'mobile_no' => 'required|string|unique:key_users,mobile_no',
            'password' => 'required|string|min:8',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aadhar_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'permissions' => 'array',
            'permissions.*' => Rule::in(KeyUser::getAllPermissions()),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'user_code' => KeyUser::generateUserCode(),
            'bp_code' => $finalBpCode,
            'full_name' => $request->full_name,
            'email_id' => $request->email_id,
            'mobile_no' => $request->mobile_no,
            'password' => bcrypt($request->password),
            'password_plain' => $request->password,
            'pincode' => $request->pincode,
            'state' => $request->state,
            'aadhar_number' => $request->aadhar_number,
            'city' => $request->city,
            'permissions' => $request->input('permissions', ['product', 'design', 'catalogue', 'user_management', 'work_order']),
            'status' => 1,
        ];

        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('key_users/profiles', 'public');
        }

        if ($request->hasFile('aadhar_photo')) {
            $data['aadhar_photo'] = $request->file('aadhar_photo')->store('key_users/aadhar', 'public');
        }

        $keyUser = KeyUser::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Key User created successfully',
            'data' => $keyUser
        ], 201);
    }

    /**
     * Display the specified key user
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $bpCode = $this->getScopeBpCode($user);

        $query = KeyUser::with('buyer');
        if ($bpCode) {
            $query->where('bp_code', $bpCode);
        }

        $keyUser = $query->find($id);
        if (!$keyUser) {
            return response()->json(['message' => 'Key User not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $keyUser
        ]);
    }

    /**
     * Update the specified key user
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $bpCode = $this->getScopeBpCode($user);

        $query = KeyUser::query();
        if ($bpCode) {
            $query->where('bp_code', $bpCode);
        }

        $keyUser = $query->find($id);
        if (!$keyUser) {
            return response()->json(['message' => 'Key User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email_id' => 'required|email|unique:key_users,email_id,' . $keyUser->id,
            'mobile_no' => 'required|string|unique:key_users,mobile_no,' . $keyUser->id,
            'password' => 'nullable|string|min:8',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aadhar_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'permissions' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = [
            'full_name' => $request->full_name,
            'email_id' => $request->email_id,
            'mobile_no' => $request->mobile_no,
            'password' => $request->password ? bcrypt($request->password) : $keyUser->password,
            'password_plain' => $request->password ?? $keyUser->password_plain,
            'pincode' => $request->pincode,
            'state' => $request->state,
            'aadhar_number' => $request->aadhar_number,
            'city' => $request->city,
            'permissions' => $request->input('permissions', ['product', 'design', 'catalogue', 'user_management', 'work_order']),
            'status' => $request->status ?? $keyUser->status,
        ];

        if ($request->hasFile('profile_picture')) {
            $updateData['profile_picture'] = $request->file('profile_picture')->store('key_users/profiles', 'public');
        }

        if ($request->hasFile('aadhar_photo')) {
            $updateData['aadhar_photo'] = $request->file('aadhar_photo')->store('key_users/aadhar', 'public');
        }

        $keyUser->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Key User updated successfully',
            'data' => $keyUser
        ]);
    }

    /**
     * Remove the specified key user
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $bpCode = $this->getScopeBpCode($user);

        $query = KeyUser::query();
        if ($bpCode) {
            $query->where('bp_code', $bpCode);
        }

        $keyUser = $query->find($id);
        if (!$keyUser) {
            return response()->json(['message' => 'Key User not found'], 404);
        }

        $keyUser->delete();
        return response()->json(['success' => true, 'message' => 'Key User deleted successfully']);
    }

    /**
     * Generate PDF for selected key users
     */
    public function generatePdf(Request $request)
    {
        $user = $request->user();
        $bpCode = $this->getScopeBpCode($user);

        $query = KeyUser::query();
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

        $keyUsers = $query->get();

        if ($keyUsers->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No key users found'], 404);
        }

        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'sans-serif');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(view('api.common.key-users.generate-pdf', compact('keyUsers'))->render());
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = count($keyUsers) === 1
                ? "Key_User_" . $keyUsers->first()->user_code . ".pdf"
                : "Key_Users_Report_" . now()->format('Ymd_His') . ".pdf";

            return response($dompdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Access-Control-Expose-Headers', 'Content-Disposition');
        } catch (\Exception $e) {
            Log::error('Key User PDF Generation Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate PDF. ' . $e->getMessage()], 500);
        }
    }
}
