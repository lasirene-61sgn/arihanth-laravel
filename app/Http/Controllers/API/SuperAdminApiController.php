<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\ProcessOwner;
use App\Models\Buyer;
use App\Models\Craftman;
use App\Models\User;
use App\Models\KeyUser;
use App\Models\Product;
use App\Models\Design;
use App\Models\Catalogue;
use App\Models\WorkOrder;
use App\Models\PurchaseOrder;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use App\Models\ProductImage;
use App\Models\WorkOrderImage;
use App\Services\ImageWatermarkService;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\WorkOrderImport;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Notifications\WorkOrderAllocated;
use Dompdf\Dompdf;
use Dompdf\Options;

class SuperAdminApiController extends Controller
{
    /**
     * Super Admin Login API
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_or_user_code' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if input is email or user code for super admins only
        $superAdmin = ProcessOwner::where('role', 'super_admin')
            ->where(function ($query) use ($request) {
                $query->where('email_id', $request->email_or_user_code)
                    ->orWhere('user_code', $request->email_or_user_code);
            })
            ->first();

        if (!$superAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.'
            ], 401);
        }

        // Verify password
        if (!Hash::check($request->password, $superAdmin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.'
            ], 401);
        }

        // Create token using HasApiTokens trait
        $tokenResult = $superAdmin->createToken('super_admin_token');
        $fullToken = $tokenResult->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $superAdmin,
                'token' => $fullToken
            ]
        ]);
    }

    /**
     * Super Admin Logout API
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get Dashboard Statistics
     */
    public function getDashboardStats()
    {
        $buyersCount = Buyer::count();
        $craftsmenCount = Craftman::count();
        $productsCount = Product::count();
        $designsCount = Product::where('design_status', 'Accepted')->count();
        $workOrdersCount = WorkOrder::count();
        $purchaseOrdersCount = PurchaseOrder::count();
        $usersCount = User::count();
        $keyUsersCount = KeyUser::count();
        $cataloguesCount = Product::where('design_status', 'Accepted')->whereNotNull('design_code')->count();
        $productsWithDesignsCount = Product::whereHas('designs')->count();
        $adminsCount = ProcessOwner::where('role', 'admin')->count();
        $kycPendingCount = 0;

        // Calculate finance total
        $purchaseOrders = PurchaseOrder::all();
        $financeTotal = 0;

        foreach ($purchaseOrders as $po) {
            if ($po->items) {
                $items = is_string($po->items) ? json_decode($po->items, true) : $po->items;
                if (is_array($items)) {
                    foreach ($items as $item) {
                        if (isset($item['quantity']) && isset($item['rate'])) {
                            $financeTotal += ($item['quantity'] * $item['rate']);
                        }
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'totalBusinessPartners' => $buyersCount + $craftsmenCount,
                'totalBuyers' => $buyersCount,
                'totalCraftsmen' => $craftsmenCount,
                'pendingKycCount' => $kycPendingCount,
                'totalAdmins' => $adminsCount,
                'totalKeyUsers' => $keyUsersCount,
                'totalUsers' => $usersCount,
                'totalWorkOrders' => $workOrdersCount,
                'totalProducts' => $productsCount,
                'totalDesigns' => $designsCount,
                'totalCatalogues' => $cataloguesCount,
                'totalPurchaseOrders' => $purchaseOrdersCount,
                'financeTotal' => $financeTotal
            ]
        ]);
    }

    // ==================== ADMIN MANAGEMENT APIs ====================

    /**
     * Get all admins
     */
    // public function getAdmins()
    // {
    //     $admins = ProcessOwner::where('role', 'admin')->get();
    //     return response()->json([
    //         'success' => true,
    //         'data' => $admins
    //     ]);
    // }

    /**
     * Get a specific admin
     */
    public function getAdmin(ProcessOwner $admin)
    {
        if ($admin->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'User is not an admin'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $admin
        ]);
    }

    /**
     * Create new admin
     */
    public function createAdmin(Request $request)
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
            'status' => 'nullable|integer|in:0,1',
            'permissions' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
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
            'role' => 'admin',
            'permissions' => json_encode($request->permissions ?? []),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Admin created successfully',
            'data' => [
                'admin' => $admin,
                'user_code' => $request->user_code
            ]
        ], 201);
    }

    /**
     * Update admin
     */
    public function updateAdmin(Request $request, ProcessOwner $admin)
    {
        if ($admin->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'User is not an admin'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'full_name' => 'sometimes|required|string|max:255',
            'email_id' => 'sometimes|required|email|max:255|unique:process_owners,email_id,' . $admin->id,
            'mobile_no' => 'sometimes|required|string|max:15|unique:process_owners,mobile_no,' . $admin->id,
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'aadhar_number' => 'nullable|string|max:20',
            'permissions' => 'nullable|array',
            'status' => 'nullable|integer|in:0,1',
            'dear' => 'nullable|string|unique:process_owners,dear,' . $admin->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
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
        ];

        // Only update password if provided
        if ($request->password) {
            $passwordValidator = Validator::make($request->only('password'), [
                'password' => 'string|min:8|confirmed',
            ]);

            if ($passwordValidator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password validation failed',
                    'errors' => $passwordValidator->errors()
                ], 422);
            }

            $updateData['password'] = Hash::make($request->password);
            $updateData['password_plain'] = $request->password;
        }

        // Add permissions to update data if provided
        if ($request->has('permissions') || $request->has('craftsman_creation') || $request->has('edit_workorder')) {
            $permissions = (array) $request->get('permissions', $admin->permissions ?? []);

            if ($request->has('craftsman_creation')) {
                if ($request->boolean('craftsman_creation')) {
                    if (!in_array('craftsman_creation', $permissions)) $permissions[] = 'craftsman_creation';
                } else {
                    $permissions = array_diff($permissions, ['craftsman_creation']);
                }
            }

            if ($request->has('edit_workorder')) {
                if ($request->boolean('edit_workorder')) {
                    if (!in_array('edit_workorder', $permissions)) $permissions[] = 'edit_workorder';
                } else {
                    $permissions = array_diff($permissions, ['edit_workorder']);
                }
            }

            $updateData['permissions'] = array_values(array_unique($permissions));
        }

        $admin->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Admin updated successfully',
            'data' => $admin
        ]);
    }

    /**
     * Delete admin
     */
    public function deleteAdmin(ProcessOwner $admin)
    {
        if ($admin->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'User is not an admin'
            ], 400);
        }

        $admin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Admin deleted successfully'
        ]);
    }

    // ==================== BUSINESS PARTNER APIs ====================

    /**
     * Get Business Partner Overview (All Buyers and Craftsmen)
     */
    public function getBusinessPartnerOverview(Request $request)
    {
        $search = $request->get('search');

        $buyersQuery = Buyer::query();
        $craftmenQuery = Craftman::query();

        if ($search) {
            $buyersQuery->where(function ($q) use ($search) {
                $q->where('bp_code', 'LIKE', "%$search%")
                    ->orWhere('business_name', 'LIKE', "%$search%")
                    ->orWhere('name', 'LIKE', "%$search%");
            });

            $craftmenQuery->where(function ($q) use ($search) {
                $q->where('craftman_code', 'LIKE', "%$search%")
                    ->orWhere('business_name', 'LIKE', "%$search%")
                    ->orWhere('name', 'LIKE', "%$search%");
            });
        }

        $buyers = $buyersQuery->orderBy('business_name', 'asc')->get();
        $craftmen = $craftmenQuery->orderBy('business_name', 'asc')->get();

        $buyers->each(function ($buyer) {
            $buyer->brand_logo_url = !empty($buyer->brand_logo) ? asset('storage/' . $buyer->brand_logo) : null;
            $buyer->brand_logo     = $buyer->brand_logo_url;
        });
        $craftmen->each(function ($craftsman) {
            $craftsman->brand_logo_url = !empty($craftsman->brand_logo) ? asset('storage/' . $craftsman->brand_logo) : null;
            $craftsman->brand_logo     = $craftsman->brand_logo_url;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'buyers' => $buyers,
                'craftsmen' => $craftmen
            ]
        ]);
    }

    /**
     * Get all buyers
     */
    public function getBuyers(Request $request)
    {
        $query = Buyer::query();

        // ── Search ──
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('bp_code', 'LIKE', "%$searchTerm%")
                    ->orWhere('business_name', 'LIKE', "%$searchTerm%")
                    ->orWhere('name', 'LIKE', "%$searchTerm%")
                    ->orWhere('mobile', 'LIKE', "%$searchTerm%")
                    ->orWhere('email', 'LIKE', "%$searchTerm%")
                    ->orWhere('city', 'LIKE', "%$searchTerm%");
            });
        }

        // ── Filters ──
        if ($request->filled('bp_code'))       $query->where('bp_code', $request->bp_code);
        if ($request->filled('business_name')) $query->where('business_name', 'LIKE', '%' . $request->business_name . '%');
        if ($request->filled('city'))          $query->where('city', $request->city);
        if ($request->filled('state'))         $query->where('state', $request->state);
        if ($request->filled('status'))        $query->where('status', $request->status);
        if ($request->filled('is_frozen'))     $query->where('is_frozen', $request->is_frozen);

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

        // ── Sorting ──
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = strtolower($request->get('sort') ?: $request->get('sort_order', 'asc'));
        $allowedSortColumns = ['id', 'bp_code', 'business_name', 'name', 'mobile', 'email', 'city', 'state', 'created_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }
        $query->orderBy($sortBy, $sortOrder);

        // ── Export (CSV download) ──
        if ($request->has('export')) {
            $buyers = $query->with(['aadharDetails', 'panDetails', 'bankDetails'])->get();

            $exportData = $buyers->map(function ($buyer) {
                return [
                    'BP Code'       => $buyer->bp_code,
                    'Business Name' => $buyer->business_name,
                    'Name'          => $buyer->name,
                    'Mobile'        => $buyer->mobile,
                    'Email'         => $buyer->email,
                    'City'          => $buyer->city,
                    'State'         => $buyer->state,
                    'Created At'    => $buyer->created_at ? $buyer->created_at->format('Y-m-d') : '',
                ];
            });

            $filename = 'buyers_' . now()->format('Y-m-d_H-i-s') . '.csv';
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
            $buyers = $query->with(['aadharDetails', 'panDetails', 'bankDetails'])->get();
            $buyers->each(function ($buyer) {
                $buyer->brand_logo_url = !empty($buyer->brand_logo) ? asset('storage/' . $buyer->brand_logo) : null;
                $buyer->brand_logo     = $buyer->brand_logo_url;
            });

            return response()->json([
                'success' => true,
                'data'    => $buyers,
            ]);
        }

        // ── Paginated Response ──
        $perPage = $request->get('per_page', 10);
        $buyers = $query->with(['aadharDetails', 'panDetails', 'bankDetails'])->paginate($perPage);
        $buyers->getCollection()->each(function ($buyer) {
            $buyer->brand_logo_url = !empty($buyer->brand_logo) ? asset('storage/' . $buyer->brand_logo) : null;
            $buyer->brand_logo     = $buyer->brand_logo_url;
        });

        return response()->json([
            'success' => true,
            'data'    => $buyers
        ]);
    }

    /**
     * Create new buyer
     */
    public function createBuyer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dear' => 'nullable|string|unique:buyers',
            'business_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15|unique:buyers',
            'email' => 'required|email|max:255|unique:buyers',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:6|confirmed',
            // Aadhar
            'aadhar_name' => 'nullable|array|min:1',
            'aadhar_name.*' => 'required_with:aadhar_number.*,aadhar_image.*|nullable|string|max:255',
            'aadhar_number' => 'nullable|array|min:1',
            'aadhar_number.*' => 'required_with:aadhar_name.*,aadhar_image.*|nullable|string|max:20',
            // PAN
            'pan_number' => 'nullable|array|min:1',
            'pan_number.*' => 'required_with:pan_image.*|nullable|string|max:20',
            // Bank Details
            'bank_name' => 'nullable|array',
            'bank_name.*' => 'required_with:account_number.*,passbook_image.*|nullable|string|max:255',
            'account_holder_name' => 'nullable|array',
            'account_number' => 'nullable|array',
            'account_number.*' => 'required_with:bank_name.*,passbook_image.*|nullable|string|max:255',
            'ifsc_code' => 'nullable|array',
            'branch' => 'nullable|array',
            'bank_city' => 'nullable|array',
            'bank_state' => 'nullable|array',
            // File uploads
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'gst_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'bis_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'msme_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'tan_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'cin_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'brand_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'aadhar_image.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'pan_image.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'passbook_image.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'worker_name' => 'nullable|array',
            'worker_name.*' => 'nullable|string|max:255',
            'worker_number' => 'nullable|array',
            'worker_number.*' => 'nullable|string|max:20',
            'worker_image.*' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'craftsman_creation' => 'nullable|boolean',
            'edit_workorder' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate BP code
        $bpCode = Buyer::generateBpCode($request->business_name);

        // Auto-fill City and State if Pincode is provided but City/State are missing
        $city = $request->city;
        $state = $request->state;

        if ($request->filled('pincode') && (empty($city) || empty($state))) {
            $pincodeData = $this->fetchPincodeData($request->pincode);
            if ($pincodeData && isset($pincodeData['Status']) && $pincodeData['Status'] === 'Success') {
                $postOffice = $pincodeData['PostOffice'][0];
                if (empty($city)) $city = $postOffice['District'];
                if (empty($state)) $state = $postOffice['State'];
            }
        }

        $buyer = Buyer::create([
            'bp_code' => $bpCode,
            'dear' => $request->dear,
            'business_name' => $request->business_name,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'landline' => $request->landline,
            'email' => $request->email,
            'business_email' => $request->business_email,
            'refered_by' => $request->refered_by,
            'more' => $request->more,
            'door_no' => $request->door_no,
            'shop_no' => $request->shop_no,
            'complex_name' => $request->complex_name,
            'building_name' => $request->building_name,
            'street_name' => $request->street_name,
            'area' => $request->area,
            'pincode' => $request->pincode,
            'city' => $city,
            'state' => $state,
            'map_location' => $request->map_location,
            'location_guide' => $request->location_guide,
            // KYC Fields - Store only the first one for backward compatibility
            'bis_no' => $request->bis_no,
            'gst_no' => $request->gst_no,
            'msme_no' => $request->msme_no,
            'cin_no' => $request->cin_no,
            'pan_no' => (($panArr = (array) $request->pan_number) && ($firstKey = array_key_first($panArr))) ? $panArr[$firstKey] : null,
            'tan_no' => $request->tan_no,
            'aadhar_no' => (($aadharArr = (array) $request->aadhar_number) && ($firstKey = array_key_first($aadharArr))) ? $aadharArr[$firstKey] : null,
            'aadhar_name' => (($aadharNameArr = (array) $request->aadhar_name) && ($firstKey = array_key_first($aadharNameArr))) ? $aadharNameArr[$firstKey] : null,
            'bank_name' => (($bankArr = (array) $request->bank_name) && ($firstKey = array_key_first($bankArr))) ? $bankArr[$firstKey] : null,
            'account_name' => (($accNameArr = (array) $request->account_holder_name) && ($firstKey = array_key_first($accNameArr))) ? $accNameArr[$firstKey] : null,
            'account_no' => (($accNoArr = (array) $request->account_number) && ($firstKey = array_key_first($accNoArr))) ? $accNoArr[$firstKey] : null,
            'ifsc_code' => (($ifscArr = (array) $request->ifsc_code) && ($firstKey = array_key_first($ifscArr))) ? $ifscArr[$firstKey] : null,
            'branch' => (($branchArr = (array) $request->branch) && ($firstKey = array_key_first($branchArr))) ? $branchArr[$firstKey] : null,
            'bank_city' => (($cityArr = (array) $request->bank_city) && ($firstKey = array_key_first($cityArr))) ? $cityArr[$firstKey] : null,
            'bank_state' => (($stateArr = (array) $request->bank_state) && ($firstKey = array_key_first($stateArr))) ? $stateArr[$firstKey] : null,
            'note' => $request->note,
            'password' => $request->password ? bcrypt($request->password) : bcrypt('password'),
            'password_plain' => $request->password ?? 'password',
            'permissions' => array_unique(array_merge(
                (array) $request->get('permissions', []),
                $request->boolean('craftsman_creation') ? ['craftsman_creation'] : [],
                $request->boolean('edit_workorder') ? ['edit_workorder'] : []
            )),
        ]);

        // Create multiple Aadhar details records
        $aadharKeys = array_unique(array_merge(
            array_keys((array) $request->aadhar_name),
            array_keys((array) $request->aadhar_number),
            array_keys((array) $request->file('aadhar_image'))
        ));

        foreach ($aadharKeys as $i) {
            $aadharNames = (array) $request->aadhar_name;
            $aadharNumbers = (array) $request->aadhar_number;

            if (!isset($aadharNames[$i]) && !isset($aadharNumbers[$i]) && !$request->hasFile("aadhar_image.$i")) {
                continue;
            }

            $aadharData = [
                'buyer_id' => $buyer->id,
                'aadhar_name' => $aadharNames[$i] ?? null,
                'aadhar_number' => $aadharNumbers[$i] ?? null,
            ];

            // Handle Aadhar image upload
            if ($request->hasFile("aadhar_image.$i")) {
                $file = $request->file("aadhar_image.$i");
                $filename = time() . '_aadhar_' . $i . '_' . $file->getClientOriginalName();
                $file->storeAs('aadhar', $filename, 'public');
                $aadharData['aadhar_image'] = 'aadhar/' . $filename;

                // Update main table if it's the first one (index 0)
                if ($i === 0 || $i === '0' || empty($buyer->aadhar_attach)) {
                    $buyer->aadhar_attach = $aadharData['aadhar_image'];
                }
            }

            \App\Models\BuyerAadharDetail::create($aadharData);
        }

        // Create multiple PAN details records
        $panKeys = array_unique(array_merge(
            array_keys((array) $request->pan_number),
            array_keys((array) $request->file('pan_image'))
        ));

        foreach ($panKeys as $i) {
            $panNumbers = (array) $request->pan_number;

            if (!isset($panNumbers[$i]) && !$request->hasFile("pan_image.$i")) {
                continue;
            }

            $panData = [
                'buyer_id' => $buyer->id,
                'pan_number' => $panNumbers[$i] ?? null,
            ];

            // Handle PAN image upload
            if ($request->hasFile("pan_image.$i")) {
                $file = $request->file("pan_image.$i");
                $filename = time() . '_pan_' . $i . '_' . $file->getClientOriginalName();
                $file->storeAs('pan', $filename, 'public');
                $panData['pan_image'] = 'pan/' . $filename;

                // Update main table if it's the first one (index 0)
                if ($i === 0 || $i === '0' || empty($buyer->pan_attachment)) {
                    $buyer->pan_attachment = $panData['pan_image'];
                }
            }

            \App\Models\BuyerPanDetail::create($panData);
        }

        // Create multiple Bank details records
        $bankKeys = array_unique(array_merge(
            array_keys((array) $request->bank_name),
            array_keys((array) ($request->account_holder_name ?? [])),
            array_keys((array) ($request->account_number ?? [])),
            array_keys((array) $request->file('passbook_image'))
        ));

        foreach ($bankKeys as $i) {
            $bankNames = (array) $request->bank_name;
            $accountHolderNames = (array) ($request->account_holder_name ?? []);
            $accountNumbers = (array) ($request->account_number ?? []);

            if (!isset($bankNames[$i]) && !isset($accountHolderNames[$i]) && !isset($accountNumbers[$i]) && !$request->hasFile("passbook_image.$i")) {
                continue;
            }

            $bankData = [
                'buyer_id' => $buyer->id,
                'bank_name' => $bankNames[$i] ?? null,
                'account_holder_name' => $accountHolderNames[$i] ?? null,
                'account_number' => $accountNumbers[$i] ?? null,
                'ifsc_code' => $request->ifsc_code[$i] ?? null,
                'branch' => $request->branch[$i] ?? null,
                'bank_city' => $request->bank_city[$i] ?? null,
                'bank_state' => $request->bank_state[$i] ?? null,
            ];

            // Handle passbook image upload
            if ($request->hasFile("passbook_image.$i")) {
                $file = $request->file("passbook_image.$i");
                $filename = time() . '_passbook_' . $i . '_' . $file->getClientOriginalName();
                $file->storeAs('passbook', $filename, 'public');
                $bankData['passbook_image'] = 'passbook/' . $filename;

                // Update main table if it's the first one (index 0)
                if ($i === 0 || $i === '0' || empty($buyer->passbook)) {
                    $buyer->passbook = $bankData['passbook_image'];
                }
            }

            \App\Models\BuyerBankDetail::create($bankData);
        }

        // Handle file uploads
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_profile_' . $file->getClientOriginalName();
            $file->storeAs('buyers', $filename, 'public');
            $buyer->image = 'buyers/' . $filename;
        }

        if ($request->hasFile('gst_attachment')) {
            $file = $request->file('gst_attachment');
            $filename = time() . '_gst_' . $file->getClientOriginalName();
            $file->storeAs('gst', $filename, 'public');
            $buyer->gst_attachment = 'gst/' . $filename;
        }

        if ($request->hasFile('bis_attachment')) {
            $file = $request->file('bis_attachment');
            $filename = time() . '_bis_' . $file->getClientOriginalName();
            $file->storeAs('bis', $filename, 'public');
            $buyer->bis_attachment = 'bis/' . $filename;
        }

        if ($request->hasFile('msme_attachment')) {
            $file = $request->file('msme_attachment');
            $filename = time() . '_msme_' . $file->getClientOriginalName();
            $file->storeAs('msme', $filename, 'public');
            $buyer->msme_attachment = 'msme/' . $filename;
        }

        if ($request->hasFile('tan_attachment')) {
            $file = $request->file('tan_attachment');
            $filename = time() . '_tan_' . $file->getClientOriginalName();
            $file->storeAs('tan', $filename, 'public');
            $buyer->tan_attachment = 'tan/' . $filename;
        }

        if ($request->hasFile('cin_attachment')) {
            $file = $request->file('cin_attachment');
            $filename = time() . '_cin_' . $file->getClientOriginalName();
            $file->storeAs('cin', $filename, 'public');
            $buyer->cin_attachment = 'cin/' . $filename;
        }

        if ($request->hasFile('brand_logo')) {
            $file = $request->file('brand_logo');
            $filename = time() . '_logo_' . $file->getClientOriginalName();
            $file->storeAs('logos', $filename, 'public');
            $buyer->brand_logo = 'logos/' . $filename;
        }

        $buyer->save();

        // Load the created buyer with all related details
        $buyer->load(['aadharDetails', 'panDetails', 'bankDetails']);

        return response()->json([
            'success' => true,
            'message' => 'Buyer created successfully',
            'data' => [
                'buyer' => $buyer,
                'bp_code' => $bpCode
            ]
        ], 201);
    }

    /**
     * Get a specific buyer with all details
     */
    public function getBuyer(Buyer $buyer)
    {
        $buyer->load(['aadharDetails', 'panDetails', 'bankDetails']);
        $buyer->brand_logo_url = !empty($buyer->brand_logo) ? asset('storage/' . $buyer->brand_logo) : null;
        $buyer->brand_logo     = $buyer->brand_logo_url;

        return response()->json([
            'success' => true,
            'data' => $buyer
        ]);
    }

    /**
     * Update a buyer
     */
    public function updateBuyer(Request $request, Buyer $buyer)
    {
        // Check for empty request - helps detect PUT vs form-data issues
        if (empty($request->all())) {
            return response()->json([
                'success' => false,
                'message' => 'Request body is empty. If you are using form-data for an update, make sure to use POST with _method=PUT field.',
                'debug_info' => 'Ensure Content-Type is multipart/form-data and _method field is present.'
            ], 400);
        }

        // Prepare validation rules
        $rules = [
            'dear' => 'nullable|string|unique:buyers,dear,' . $buyer->id,
            'business_name' => 'sometimes|required|string|max:255',
            'name' => 'sometimes|required|string|max:255',
            'mobile' => 'sometimes|required|string|max:15',
            'email' => 'sometimes|required|email|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:6|confirmed',
            // Aadhar
            'aadhar_name' => 'sometimes|array',
            'aadhar_name.*' => 'required_with:aadhar_number.*,aadhar_image.*|nullable|string|max:255',
            'aadhar_number' => 'sometimes|array',
            'aadhar_number.*' => 'required_with:aadhar_name.*,aadhar_image.*|nullable|string|max:20',
            // PAN
            'pan_number' => 'sometimes|array',
            'pan_number.*' => 'required_with:pan_image.*|nullable|string|max:20',
            // Bank Details
            'bank_name' => 'nullable|array',
            'bank_name.*' => 'required_with:account_number.*,passbook_image.*|nullable|string|max:255',
            'account_holder_name' => 'nullable|array',
            'account_number' => 'nullable|array',
            'account_number.*' => 'required_with:bank_name.*,passbook_image.*|nullable|string|max:255',
            'ifsc_code' => 'nullable|array',
            'branch' => 'nullable|array',
            'bank_city' => 'nullable|array',
            'bank_state' => 'nullable|array',
            // File uploads
            'gst_no' => 'nullable|string|max:20|unique:buyers,gst_no,' . $buyer->id,
            'cin_no' => 'nullable|string|max:21',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'brand_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'gst_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'bis_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'msme_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'tan_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'cin_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'aadhar_image.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'pan_image.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'passbook_image.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'worker_name' => 'nullable|array',
            'worker_name.*' => 'nullable|string|max:255',
            'worker_number' => 'nullable|array',
            'worker_number.*' => 'nullable|string|max:20',
            'worker_image.*' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'craftsman_creation' => 'nullable|boolean',
            'edit_workorder' => 'nullable|boolean',
        ];

        // Only add unique validation if the values are different from current buyer
        if ($request->mobile && $request->mobile !== $buyer->mobile) {
            $rules['mobile'] .= '|unique:buyers,mobile,' . $buyer->id;
        }

        if ($request->email && $request->email !== $buyer->email) {
            $rules['email'] .= '|unique:buyers,email,' . $buyer->id;
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $updateFields = [
            'bp_code',
            'dear',
            'business_name',
            'name',
            'mobile',
            'landline',
            'email',
            'business_email',
            'refered_by',
            'more',
            'door_no',
            'shop_no',
            'complex_name',
            'building_name',
            'street_name',
            'area',
            'pincode',
            'city',
            'state',
            'map_location',
            'location_guide',
            'business_type',
            'address',
            'gst_no',
            'cin_no',
            'msme_no',
            'tan_no',
            'note'
        ];

        $updateData = [];
        foreach ($updateFields as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $request->get($field);
            }
        }

        // Header KYC mapping (first available element of arrays)
        if ($request->has('pan_number')) {
            $panArr = (array) $request->pan_number;
            $firstKey = array_key_first($panArr);
            $updateData['pan_no'] = $panArr[$firstKey] ?? null;
        }
        if ($request->has('aadhar_number')) {
            $aadharArr = (array) $request->aadhar_number;
            $firstKey = array_key_first($aadharArr);
            $updateData['aadhar_no'] = $aadharArr[$firstKey] ?? null;
        }
        if ($request->has('aadhar_name')) {
            $aadharNameArr = (array) $request->aadhar_name;
            $firstKey = array_key_first($aadharNameArr);
            $updateData['aadhar_name'] = $aadharNameArr[$firstKey] ?? null;
        }
        if ($request->has('bank_name')) {
            $bankArr = (array) $request->bank_name;
            $firstKey = array_key_first($bankArr);
            $updateData['bank_name'] = $bankArr[$firstKey] ?? null;
        }
        if ($request->has('account_holder_name')) {
            $accNameArr = (array) $request->account_holder_name;
            $firstKey = array_key_first($accNameArr);
            $updateData['account_name'] = $accNameArr[$firstKey] ?? null;
        }
        if ($request->has('account_number')) {
            $accNoArr = (array) $request->account_number;
            $firstKey = array_key_first($accNoArr);
            $updateData['account_no'] = $accNoArr[$firstKey] ?? null;
        }
        if ($request->has('ifsc_code')) {
            $ifscArr = (array) $request->ifsc_code;
            $firstKey = array_key_first($ifscArr);
            $updateData['ifsc_code'] = $ifscArr[$firstKey] ?? null;
        }
        if ($request->has('branch')) {
            $branchArr = (array) $request->branch;
            $firstKey = array_key_first($branchArr);
            $updateData['branch'] = $branchArr[$firstKey] ?? null;
        }
        if ($request->has('bank_city')) {
            $cityArr = (array) $request->bank_city;
            $firstKey = array_key_first($cityArr);
            $updateData['bank_city'] = $cityArr[$firstKey] ?? null;
        }
        if ($request->has('bank_state')) {
            $stateArr = (array) $request->bank_state;
            $firstKey = array_key_first($stateArr);
            $updateData['bank_state'] = $stateArr[$firstKey] ?? null;
        }

        if ($request->has('permissions') || $request->has('craftsman_creation') || $request->has('edit_workorder')) {
            $permissions = (array) $request->get('permissions', $buyer->permissions ?? []);

            if ($request->has('craftsman_creation')) {
                if ($request->boolean('craftsman_creation')) {
                    if (!in_array('craftsman_creation', $permissions)) $permissions[] = 'craftsman_creation';
                } else {
                    $permissions = array_diff($permissions, ['craftsman_creation']);
                }
            }

            if ($request->has('edit_workorder')) {
                if ($request->boolean('edit_workorder')) {
                    if (!in_array('edit_workorder', $permissions)) $permissions[] = 'edit_workorder';
                } else {
                    $permissions = array_diff($permissions, ['edit_workorder']);
                }
            }

            $updateData['permissions'] = array_values(array_unique($permissions));
        }

        // Auto-fill City and State if Pincode is provided but City/State are missing
        if ($request->filled('pincode') && (empty($updateData['city']) || empty($updateData['state']))) {
            $pincodeData = $this->fetchPincodeData($request->pincode);
            if ($pincodeData && isset($pincodeData['Status']) && $pincodeData['Status'] === 'Success') {
                $postOffice = $pincodeData['PostOffice'][0];
                if (empty($updateData['city'])) $updateData['city'] = $postOffice['District'];
                if (empty($updateData['state'])) $updateData['state'] = $postOffice['State'];
            }
        }

        // Only update password if provided
        if ($request->password) {
            $updateData['password'] = bcrypt($request->password);
            $updateData['password_plain'] = $request->password;
        }

        $buyer->update($updateData);

        // Delete existing related records only if new ones are provided
        if ($request->has('aadhar_name') || $request->has('aadhar_number') || $request->hasFile('aadhar_image')) {
            $buyer->aadharDetails()->delete();
        }
        if ($request->has('pan_number') || $request->hasFile('pan_image')) {
            $buyer->panDetails()->delete();
        }
        if ($request->has('bank_name') || $request->hasFile('passbook_image')) {
            $buyer->bankDetails()->delete();
        }

        // Create new multiple Aadhar details records
        if ($request->has('aadhar_name') || $request->has('aadhar_number') || $request->hasFile('aadhar_image')) {
            $aadharKeys = array_unique(array_merge(
                array_keys((array) $request->aadhar_name),
                array_keys((array) $request->aadhar_number),
                array_keys((array) $request->file('aadhar_image'))
            ));

            foreach ($aadharKeys as $i) {
                $aadharNames = (array) $request->aadhar_name;
                $aadharNumbers = (array) $request->aadhar_number;

                if (!isset($aadharNames[$i]) && !isset($aadharNumbers[$i]) && !$request->hasFile("aadhar_image.$i")) {
                    continue;
                }

                $aadharData = [
                    'buyer_id' => $buyer->id,
                    'aadhar_name' => $aadharNames[$i] ?? null,
                    'aadhar_number' => $aadharNumbers[$i] ?? null,
                ];

                // Handle Aadhar image upload
                if ($request->hasFile("aadhar_image.$i")) {
                    $file = $request->file("aadhar_image.$i");
                    $filename = time() . '_aadhar_' . $i . '_' . $file->getClientOriginalName();
                    $file->storeAs('aadhar', $filename, 'public');
                    $aadharData['aadhar_image'] = 'aadhar/' . $filename;

                    // Update main table if it's the first one (index 0) or if main column is empty
                    if ($i === 0 || $i === '0' || empty($buyer->aadhar_attach)) {
                        $buyer->aadhar_attach = $aadharData['aadhar_image'];
                    }
                }

                \App\Models\BuyerAadharDetail::create($aadharData);
            }
        }

        // Create new multiple PAN details records
        if ($request->has('pan_number') || $request->hasFile('pan_image')) {
            $panKeys = array_unique(array_merge(
                array_keys((array) $request->pan_number),
                array_keys((array) $request->file('pan_image'))
            ));

            foreach ($panKeys as $i) {
                $panNumbers = (array) $request->pan_number;

                if (!isset($panNumbers[$i]) && !$request->hasFile("pan_image.$i")) {
                    continue;
                }

                $panData = [
                    'buyer_id' => $buyer->id,
                    'pan_number' => $panNumbers[$i] ?? null,
                ];

                // Handle PAN image upload
                if ($request->hasFile("pan_image.$i")) {
                    $file = $request->file("pan_image.$i");
                    $filename = time() . '_pan_' . $i . '_' . $file->getClientOriginalName();
                    $file->storeAs('pan', $filename, 'public');
                    $panData['pan_image'] = 'pan/' . $filename;

                    // Update main table if it's the first one (index 0) or if main column is empty
                    if ($i === 0 || $i === '0' || empty($buyer->pan_attachment)) {
                        $buyer->pan_attachment = $panData['pan_image'];
                    }
                }

                \App\Models\BuyerPanDetail::create($panData);
            }
        }

        // Create new multiple Bank details records
        if ($request->has('bank_name') || $request->hasFile('passbook_image')) {
            $bankKeys = array_unique(array_merge(
                array_keys((array) $request->bank_name),
                array_keys((array) ($request->account_holder_name ?? [])),
                array_keys((array) ($request->account_number ?? [])),
                array_keys((array) $request->file('passbook_image'))
            ));

            foreach ($bankKeys as $i) {
                $bankNames = (array) $request->bank_name;
                $accountHolderNames = (array) ($request->account_holder_name ?? []);
                $accountNumbers = (array) ($request->account_number ?? []);

                if (!isset($bankNames[$i]) && !isset($accountHolderNames[$i]) && !isset($accountNumbers[$i]) && !$request->hasFile("passbook_image.$i")) {
                    continue;
                }

                $bankData = [
                    'buyer_id' => $buyer->id,
                    'bank_name' => $bankNames[$i] ?? null,
                    'account_holder_name' => $accountHolderNames[$i] ?? null,
                    'account_number' => $accountNumbers[$i] ?? null,
                    'ifsc_code' => $request->ifsc_code[$i] ?? null,
                    'branch' => $request->branch[$i] ?? null,
                    'bank_city' => $request->bank_city[$i] ?? null,
                    'bank_state' => $request->bank_state[$i] ?? null,
                ];

                // Handle passbook image upload
                if ($request->hasFile("passbook_image.$i")) {
                    $file = $request->file("passbook_image.$i");
                    $filename = time() . '_passbook_' . $i . '_' . $file->getClientOriginalName();
                    $file->storeAs('passbook', $filename, 'public');
                    $bankData['passbook_image'] = 'passbook/' . $filename;

                    // Update main table if it's the first one (index 0) or if main column is empty
                    if ($i === 0 || $i === '0' || empty($buyer->passbook)) {
                        $buyer->passbook = $bankData['passbook_image'];
                    }
                }

                \App\Models\BuyerBankDetail::create($bankData);
            }
        }

        // Handle file uploads
        if ($request->hasFile('image')) {
            // Delete old file if exists
            if ($buyer->image && Storage::exists('public/' . $buyer->image)) {
                Storage::delete('public/' . $buyer->image);
            }
            $file = $request->file('image');
            $filename = time() . '_profile_' . $file->getClientOriginalName();
            $file->storeAs('buyers', $filename, 'public');
            $buyer->image = 'buyers/' . $filename;
        }

        if ($request->hasFile('gst_attachment')) {
            if ($buyer->gst_attachment && Storage::exists('public/' . $buyer->gst_attachment)) {
                Storage::delete('public/' . $buyer->gst_attachment);
            }
            $file = $request->file('gst_attachment');
            $filename = time() . '_gst_' . $file->getClientOriginalName();
            $file->storeAs('gst', $filename, 'public');
            $buyer->gst_attachment = 'gst/' . $filename;
        }

        if ($request->hasFile('bis_attachment')) {
            if ($buyer->bis_attachment && Storage::exists('public/' . $buyer->bis_attachment)) {
                Storage::delete('public/' . $buyer->bis_attachment);
            }
            $file = $request->file('bis_attachment');
            $filename = time() . '_bis_' . $file->getClientOriginalName();
            $file->storeAs('bis', $filename, 'public');
            $buyer->bis_attachment = 'bis/' . $filename;
        }

        if ($request->hasFile('msme_attachment')) {
            if ($buyer->msme_attachment && Storage::exists('public/' . $buyer->msme_attachment)) {
                Storage::delete('public/' . $buyer->msme_attachment);
            }
            $file = $request->file('msme_attachment');
            $filename = time() . '_msme_' . $file->getClientOriginalName();
            $file->storeAs('msme', $filename, 'public');
            $buyer->msme_attachment = 'msme/' . $filename;
        }

        if ($request->hasFile('tan_attachment')) {
            if ($buyer->tan_attachment && Storage::exists('public/' . $buyer->tan_attachment)) {
                Storage::delete('public/' . $buyer->tan_attachment);
            }
            $file = $request->file('tan_attachment');
            $filename = time() . '_tan_' . $file->getClientOriginalName();
            $file->storeAs('tan', $filename, 'public');
            $buyer->tan_attachment = 'tan/' . $filename;
        }

        if ($request->hasFile('cin_attachment')) {
            if ($buyer->cin_attachment && Storage::exists('public/' . $buyer->cin_attachment)) {
                Storage::delete('public/' . $buyer->cin_attachment);
            }
            $file = $request->file('cin_attachment');
            $filename = time() . '_cin_' . $file->getClientOriginalName();
            $file->storeAs('cin', $filename, 'public');
            $buyer->cin_attachment = 'cin/' . $filename;
        }

        if ($request->hasFile('brand_logo')) {
            if ($buyer->brand_logo && Storage::exists('public/' . $buyer->brand_logo)) {
                Storage::delete('public/' . $buyer->brand_logo);
            }
            $file = $request->file('brand_logo');
            $filename = time() . '_logo_' . $file->getClientOriginalName();
            $file->storeAs('logos', $filename, 'public');
            $buyer->brand_logo = 'logos/' . $filename;
        }

        $buyer->save();

        // Load the updated buyer with all related details
        $buyer->load(['aadharDetails', 'panDetails', 'bankDetails']);

        return response()->json([
            'success' => true,
            'message' => 'Buyer updated successfully',
            'data' => $buyer
        ]);
    }

    /**
     * Delete a buyer
     */
    public function deleteBuyer(Buyer $buyer)
    {
        // Delete related records first
        $buyer->aadharDetails()->delete();
        $buyer->panDetails()->delete();
        $buyer->bankDetails()->delete();

        // Delete the buyer
        $buyer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Buyer deleted successfully'
        ]);
    }

    /**
     * Get a list of Business Partner codes and business names
     */
    public function getBpCodeList()
    {
        $buyers = Buyer::select('bp_code', 'business_name')
            ->orderBy('business_name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $buyers
        ]);
    }

    /**
     * Get a list of Craftsman codes, names and business names
     */
    public function getCraftsmanCodeList()
    {
        $craftsmen = Craftman::select('craftman_code', 'name', 'business_name')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $craftsmen
        ]);
    }

    /**
     * Get all craftsmen
     */
    public function getCraftsmen(Request $request)
    {
        $query = Craftman::query();

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('craftman_code', 'LIKE', "%$searchTerm%")
                    ->orWhere('business_name', 'LIKE', "%$searchTerm%")
                    ->orWhere('name', 'LIKE', "%$searchTerm%")
                    ->orWhere('mobile', 'LIKE', "%$searchTerm%")
                    ->orWhere('email', 'LIKE', "%$searchTerm%")
                    ->orWhere('city', 'LIKE', "%$searchTerm%");
            });
        }

        // Filtering
        if ($request->filled('ids')) {
            $query->whereIn('id', (array) $request->ids);
        }

        if ($request->filled('craftman_code') || $request->filled('craftsman_code')) {
            $code = $request->get('craftman_code') ?: $request->get('craftsman_code');
            $query->where('craftman_code', 'LIKE', "%$code%");
        }

        if ($request->filled('business_name')) {
            $query->where('business_name', 'LIKE', '%' . $request->business_name . '%');
        }

        if ($request->filled('city')) {
            $query->where('city', 'LIKE', '%' . $request->city . '%');
        }

        if ($request->filled('state')) {
            $query->where('state', 'LIKE', '%' . $request->state . '%');
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = strtolower($request->get('sort') ?: $request->get('sort_order', 'asc'));
        $query->orderBy($sortBy, $sortOrder);

        // Export (CSV) or Print (JSON) View
        if ($request->get('export') === 'true' || $request->get('print') === 'true') {
            $craftsmen = $query->with(['aadharDetails', 'panDetails', 'bankDetails', 'workers', 'workOrders'])->get();

            if ($request->get('export') === 'true') {
                $headers = [
                    "Content-type" => "text/csv",
                    "Content-Disposition" => "attachment; filename=craftsmen_export_" . date('Y-m-d') . ".csv",
                    "Pragma" => "no-cache",
                    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                    "Expires" => "0"
                ];

                $columns = ['ID', 'Craftsman Code', 'Business Name', 'Name', 'Mobile', 'Email', 'City', 'State', 'Status', 'Created At'];

                $callback = function () use ($craftsmen, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);

                    foreach ($craftsmen as $craftsman) {
                        fputcsv($file, [
                            $craftsman->id,
                            $craftsman->craftman_code,
                            $craftsman->business_name,
                            $craftsman->name,
                            $craftsman->mobile,
                            $craftsman->email,
                            $craftsman->city,
                            $craftsman->state,
                            $craftsman->status,
                            $craftsman->created_at,
                        ]);
                    }
                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            }

            $craftsmen->each(function ($craftsman) {
                $craftsman->brand_logo_url = !empty($craftsman->brand_logo) ? asset('storage/' . $craftsman->brand_logo) : null;
                $craftsman->brand_logo     = $craftsman->brand_logo_url;
            });

            return response()->json([
                'success' => true,
                'data' => $craftsmen
            ]);
        }

        // Standard Paginated View
        $perPage = $request->get('per_page', 10);
        $craftsmen = $query->paginate($perPage);

        // Load relationships for each craftsman
        $craftsmen->getCollection()->each(function ($craftsman) {
            $craftsman->load(['aadharDetails', 'panDetails', 'bankDetails', 'workers', 'workOrders']);
            $craftsman->brand_logo_url = !empty($craftsman->brand_logo) ? asset('storage/' . $craftsman->brand_logo) : null;
            $craftsman->brand_logo     = $craftsman->brand_logo_url;
        });

        return response()->json([
            'success' => true,
            'data' => $craftsmen
        ]);
    }

    /**
     * Get details for each craftsman individually (for specific view)
     */
    public function getCraftsman(Craftman $craftsman)
    {
        $craftsman->load(['aadharDetails', 'panDetails', 'bankDetails', 'workers', 'workOrders']);
        $craftsman->brand_logo_url = !empty($craftsman->brand_logo) ? asset('storage/' . $craftsman->brand_logo) : null;
        $craftsman->brand_logo     = $craftsman->brand_logo_url;

        return response()->json([
            'success' => true,
            'data' => $craftsman
        ]);
    }

    /**
     * Generate PDF for selected buyers
     */
    public function generateBuyerPdf(Request $request)
    {
        $query = Buyer::query();

        // ── Selected IDs (for PDF selected) ──
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

        $buyers = $query->get();

        if ($buyers->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No buyers found'], 404);
        }

        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'sans-serif');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(view('api.superadmin.buyers.generate-pdf', compact('buyers'))->render());
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $filename = count($buyers) === 1
                ? "Buyer_" . $buyers->first()->bp_code . ".pdf"
                : "Buyers_Report_" . now()->format('Ymd_His') . ".pdf";

            return response($dompdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Access-Control-Expose-Headers', 'Content-Disposition');
        } catch (\Exception $e) {
            Log::error('Buyer PDF Generation Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate PDF. ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate PDF for selected craftsmen
     */
    public function generateCraftsmanPdf(Request $request)
    {
        $query = Craftman::query();

        // ── Selected IDs (for PDF selected) ──
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

        $craftsmen = $query->get();

        if ($craftsmen->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No craftsmen found'], 404);
        }

        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'sans-serif');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(view('api.superadmin.craftsmen.generate-pdf', compact('craftsmen'))->render());
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $filename = count($craftsmen) === 1
                ? "Craftsman_" . $craftsmen->first()->craftman_code . ".pdf"
                : "Craftsmen_Report_" . now()->format('Ymd_His') . ".pdf";

            return response($dompdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Access-Control-Expose-Headers', 'Content-Disposition');
        } catch (\Exception $e) {
            Log::error('Craftsman PDF Generation Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate PDF. ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create new craftsman
     */
    public function createCraftsman(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'craftman_code' => 'required|string|unique:craftmen',
            'dear' => 'nullable|string|unique:craftmen',
            'business_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15|unique:craftmen',
            'email' => 'required|email|max:255|unique:craftmen',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:6|confirmed',
            // Aadhar
            'aadhar_name' => 'nullable|array|min:1',
            'aadhar_name.*' => 'required_with:aadhar_number.*,aadhar_image.*|nullable|string|max:255',
            'aadhar_number' => 'nullable|array|min:1',
            'aadhar_number.*' => 'required_with:aadhar_name.*,aadhar_image.*|nullable|string|max:20',
            // PAN
            'pan_number' => 'nullable|array|min:1',
            'pan_number.*' => 'required_with:pan_image.*|nullable|string|max:20',
            // Bank Details
            'bank_name' => 'nullable|array',
            'bank_name.*' => 'required_with:account_number.*,passbook_image.*|nullable|string|max:255',
            'account_holder_name' => 'nullable|array',
            'account_number' => 'nullable|array',
            'account_number.*' => 'required_with:bank_name.*,passbook_image.*|nullable|string|max:255',
            'ifsc_code' => 'nullable|array',
            'branch' => 'nullable|array',
            'bank_city' => 'nullable|array',
            'bank_state' => 'nullable|array',
            // File uploads
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'brand_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'gst_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'bis_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'msme_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'tan_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'cin_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'aadhar_image.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'pan_image.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'passbook_image.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'worker_name' => 'nullable|array',
            'worker_name.*' => 'required_with:worker_number.*,worker_image.*|nullable|string|max:255',
            'worker_number' => 'nullable|array',
            'worker_number.*' => 'required_with:worker_name.*,worker_image.*|nullable|string|max:20',
            'worker_image.*' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate craftsman code - REMOVED manual entry
        // $craftmanCode = Craftman::generateCraftmanCode();

        // Auto-fill City and State if Pincode is provided but City/State are missing
        $city = $request->city;
        $state = $request->state;

        if ($request->filled('pincode') && (empty($city) || empty($state))) {
            $pincodeData = $this->fetchPincodeData($request->pincode);
            if ($pincodeData && isset($pincodeData['Status']) && $pincodeData['Status'] === 'Success') {
                $postOffice = $pincodeData['PostOffice'][0];
                if (empty($city)) $city = $postOffice['District'];
                if (empty($state)) $state = $postOffice['State'];
            }
        }

        $craftsman = Craftman::create([
            'craftman_code' => $request->craftman_code,
            'dear' => $request->dear,
            'business_name' => $request->business_name,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'landline' => $request->landline,
            'email' => $request->email,
            'business_email' => $request->business_email,
            'refered_by' => $request->refered_by,
            'more' => $request->more,
            'door_no' => $request->door_no,
            'shop_no' => $request->shop_no,
            'complex_name' => $request->complex_name,
            'building_name' => $request->building_name,
            'street_name' => $request->street_name,
            'area' => $request->area,
            'pincode' => $request->pincode,
            'city' => $city,
            'state' => $state,
            'map_location' => $request->map_location,
            'location_guide' => $request->location_guide,
            // KYC Fields - Store only the first one for backward compatibility
            'bis_no' => $request->bis_no,
            'gst_no' => $request->gst_no,
            'msme_no' => $request->msme_no,
            'cin_no' => $request->cin_no,
            'pan_no' => (($panArr = (array) $request->pan_number) && ($firstKey = array_key_first($panArr))) ? $panArr[$firstKey] : null,
            'tan_no' => $request->tan_no,
            'aadhar_no' => (($aadharArr = (array) $request->aadhar_number) && ($firstKey = array_key_first($aadharArr))) ? $aadharArr[$firstKey] : null,
            'aadhar_name' => (($aadharNameArr = (array) $request->aadhar_name) && ($firstKey = array_key_first($aadharNameArr))) ? $aadharNameArr[$firstKey] : null,
            'bank_name' => (($bankArr = (array) $request->bank_name) && ($firstKey = array_key_first($bankArr))) ? $bankArr[$firstKey] : null,
            'account_name' => (($accNameArr = (array) $request->account_holder_name) && ($firstKey = array_key_first($accNameArr))) ? $accNameArr[$firstKey] : null,
            'account_no' => (($accNoArr = (array) $request->account_number) && ($firstKey = array_key_first($accNoArr))) ? $accNoArr[$firstKey] : null,
            'ifsc_code' => (($ifscArr = (array) $request->ifsc_code) && ($firstKey = array_key_first($ifscArr))) ? $ifscArr[$firstKey] : null,
            'branch' => (($branchArr = (array) $request->branch) && ($firstKey = array_key_first($branchArr))) ? $branchArr[$firstKey] : null,
            'bank_city' => (($cityArr = (array) $request->bank_city) && ($firstKey = array_key_first($cityArr))) ? $cityArr[$firstKey] : null,
            'bank_state' => (($stateArr = (array) $request->bank_state) && ($firstKey = array_key_first($stateArr))) ? $stateArr[$firstKey] : null,
            'note' => $request->note,
            'password' => $request->password ? bcrypt($request->password) : bcrypt('password'),
            'password_plain' => $request->password ?? 'password',
            'permissions' => array_unique(array_merge(
                (array) $request->get('permissions', []),
                ['dashboard'],
                $request->boolean('craftsman_creation') ? ['craftsman_creation'] : [],
                $request->boolean('edit_workorder') ? ['edit_workorder'] : []
            )),
        ]);

        // Create multiple Aadhar details records
        $aadharKeys = array_unique(array_merge(
            array_keys((array) $request->aadhar_name),
            array_keys((array) $request->aadhar_number),
            array_keys((array) $request->file('aadhar_image'))
        ));

        foreach ($aadharKeys as $i) {
            $aadharNames = (array) $request->aadhar_name;
            $aadharNumbers = (array) $request->aadhar_number;

            if (!isset($aadharNames[$i]) && !isset($aadharNumbers[$i]) && !$request->hasFile("aadhar_image.$i")) {
                continue;
            }

            $aadharData = [
                'craftman_id' => $craftsman->id,
                'aadhar_name' => $aadharNames[$i] ?? null,
                'aadhar_number' => $aadharNumbers[$i] ?? null,
            ];

            // Handle Aadhar image upload
            if ($request->hasFile("aadhar_image.$i")) {
                $file = $request->file("aadhar_image.$i");
                $filename = time() . '_aadhar_' . $i . '_' . $file->getClientOriginalName();
                $file->storeAs('aadhar', $filename, 'public');
                $aadharData['aadhar_image'] = 'aadhar/' . $filename;

                // Update main table if it's the first one (index 0) or if main column is empty
                if ($i === 0 || $i === '0' || empty($craftsman->aadhar_attach)) {
                    $craftsman->aadhar_attach = $aadharData['aadhar_image'];
                }
            }

            \App\Models\CraftmanAadharDetail::create($aadharData);
        }

        // Create multiple PAN details records
        $panKeys = array_unique(array_merge(
            array_keys((array) $request->pan_number),
            array_keys((array) $request->file('pan_image'))
        ));

        foreach ($panKeys as $i) {
            $panNumbers = (array) $request->pan_number;

            if (!isset($panNumbers[$i]) && !$request->hasFile("pan_image.$i")) {
                continue;
            }

            $panData = [
                'craftman_id' => $craftsman->id,
                'pan_number' => $panNumbers[$i] ?? null,
            ];

            // Handle PAN image upload
            if ($request->hasFile("pan_image.$i")) {
                $file = $request->file("pan_image.$i");
                $filename = time() . '_pan_' . $i . '_' . $file->getClientOriginalName();
                $file->storeAs('pan', $filename, 'public');
                $panData['pan_image'] = 'pan/' . $filename;

                // Update main table if it's the first one (index 0) or if main column is empty
                if ($i === 0 || $i === '0' || empty($craftsman->pan_attachment)) {
                    $craftsman->pan_attachment = $panData['pan_image'];
                }
            }

            \App\Models\CraftmanPanDetail::create($panData);
        }

        // Create multiple Bank details records
        $bankKeys = array_unique(array_merge(
            array_keys((array) $request->bank_name),
            array_keys((array) ($request->account_holder_name ?? [])),
            array_keys((array) ($request->account_number ?? [])),
            array_keys((array) $request->file('passbook_image'))
        ));

        foreach ($bankKeys as $i) {
            $bankNames = (array) $request->bank_name;
            $accountHolderNames = (array) ($request->account_holder_name ?? []);
            $accountNumbers = (array) ($request->account_number ?? []);

            if (!isset($bankNames[$i]) && !isset($accountHolderNames[$i]) && !isset($accountNumbers[$i]) && !$request->hasFile("passbook_image.$i")) {
                continue;
            }

            $bankData = [
                'craftman_id' => $craftsman->id,
                'bank_name' => $bankNames[$i] ?? null,
                'account_holder_name' => $accountHolderNames[$i] ?? null,
                'account_number' => $accountNumbers[$i] ?? null,
                'ifsc_code' => $request->ifsc_code[$i] ?? null,
                'branch' => $request->branch[$i] ?? null,
                'bank_city' => $request->bank_city[$i] ?? null,
                'bank_state' => $request->bank_state[$i] ?? null,
            ];

            // Handle passbook image upload
            if ($request->hasFile("passbook_image.$i")) {
                $file = $request->file("passbook_image.$i");
                $filename = time() . '_passbook_' . $i . '_' . $file->getClientOriginalName();
                $file->storeAs('passbook', $filename, 'public');
                $bankData['passbook_image'] = 'passbook/' . $filename;

                // Update main table if it's the first one (index 0) or if main column is empty
                if ($i === 0 || $i === '0' || empty($craftsman->passbook)) {
                    $craftsman->passbook = $bankData['passbook_image'];
                }
            }

            \App\Models\CraftmanBankDetail::create($bankData);
        }
        // Create Workers records
        $workerKeys = array_unique(array_merge(
            array_keys((array) $request->worker_name),
            array_keys((array) $request->worker_number),
            array_keys((array) $request->file('worker_image'))
        ));

        foreach ($workerKeys as $i) {
            $workerNames = (array) $request->worker_name;
            $workerNumbers = (array) $request->worker_number;

            if (!isset($workerNames[$i]) && !isset($workerNumbers[$i]) && !$request->hasFile("worker_image.$i")) {
                continue;
            }

            $workerData = [
                'craftman_id' => $craftsman->id,
                'worker_name' => $workerNames[$i] ?? null,
                'worker_number' => $workerNumbers[$i] ?? null,
            ];

            if ($request->hasFile("worker_image.$i")) {
                $file = $request->file("worker_image.$i");
                $filename = time() . '_worker_' . $i . '_' . $file->getClientOriginalName();
                $file->storeAs('workers', $filename, 'public');
                $workerData['worker_image'] = 'workers/' . $filename;
            }

            \App\Models\CraftmanWorker::create($workerData);
        }

        // Handle file uploads
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_profile_' . $file->getClientOriginalName();
            $file->storeAs('craftmen', $filename, 'public');
            $craftsman->image = 'craftmen/' . $filename;
        }

        if ($request->hasFile('gst_attachment')) {
            $file = $request->file('gst_attachment');
            $filename = time() . '_gst_' . $file->getClientOriginalName();
            $file->storeAs('gst', $filename, 'public');
            $craftsman->gst_attachment = 'gst/' . $filename;
        }

        if ($request->hasFile('bis_attachment')) {
            $file = $request->file('bis_attachment');
            $filename = time() . '_bis_' . $file->getClientOriginalName();
            $file->storeAs('bis', $filename, 'public');
            $craftsman->bis_attachment = 'bis/' . $filename;
        }

        if ($request->hasFile('msme_attachment')) {
            $file = $request->file('msme_attachment');
            $filename = time() . '_msme_' . $file->getClientOriginalName();
            $file->storeAs('msme', $filename, 'public');
            $craftsman->msme_attachment = 'msme/' . $filename;
        }

        if ($request->hasFile('tan_attachment')) {
            $file = $request->file('tan_attachment');
            $filename = time() . '_tan_' . $file->getClientOriginalName();
            $file->storeAs('tan', $filename, 'public');
            $craftsman->tan_attachment = 'tan/' . $filename;
        }

        if ($request->hasFile('cin_attachment')) {
            $file = $request->file('cin_attachment');
            $filename = time() . '_cin_' . $file->getClientOriginalName();
            $file->storeAs('cin', $filename, 'public');
            $craftsman->cin_attachment = 'cin/' . $filename;
        }

        if ($request->hasFile('brand_logo')) {
            $file = $request->file('brand_logo');
            $filename = time() . '_logo_' . $file->getClientOriginalName();
            $file->storeAs('logos', $filename, 'public');
            $craftsman->brand_logo = 'logos/' . $filename;
        }

        $craftsman->save();

        // Load the created craftsman with all related details
        $craftsman->load(['aadharDetails', 'panDetails', 'bankDetails', 'workers', 'workOrders']);

        return response()->json([
            'success' => true,
            'message' => 'Craftsman created successfully',
            'data' => [
                'craftsman' => $craftsman,
                'craftman_code' => $request->craftman_code
            ]
        ], 201);
    }

    /**
     * Update a craftsman
     */
    public function updateCraftsman(Request $request, Craftman $craftsman)
    {
        // Check for empty request - helps detect PUT vs form-data issues
        if (empty($request->all())) {
            return response()->json([
                'success' => false,
                'message' => 'Request body is empty. If you are using form-data for an update, make sure to use POST with _method=PUT field.',
                'debug_info' => 'Ensure Content-Type is multipart/form-data and _method field is present.'
            ], 400);
        }

        // Prepare validation rules
        $rules = [
            'craftman_code' => 'sometimes|required|string|unique:craftmen,craftman_code,' . $craftsman->id,
            'dear' => 'nullable|string|unique:craftmen,dear,' . $craftsman->id,
            'business_name' => 'sometimes|required|string|max:255',
            'name' => 'sometimes|required|string|max:255',
            'mobile' => 'sometimes|required|string|max:15',
            'email' => 'sometimes|required|email|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:6|confirmed',
            // Aadhar
            'aadhar_name' => 'sometimes|array',
            'aadhar_name.*' => 'required_with:aadhar_number.*,aadhar_image.*|nullable|string|max:255',
            'aadhar_number' => 'sometimes|array',
            'aadhar_number.*' => 'required_with:aadhar_name.*,aadhar_image.*|nullable|string|max:20',
            // PAN
            'pan_number' => 'sometimes|array',
            'pan_number.*' => 'required_with:pan_image.*|nullable|string|max:20',
            // Bank Details
            'bank_name' => 'nullable|array',
            'bank_name.*' => 'required_with:account_number.*,passbook_image.*|nullable|string|max:255',
            'account_holder_name' => 'nullable|array',
            'account_number' => 'nullable|array',
            'account_number.*' => 'required_with:bank_name.*,passbook_image.*|nullable|string|max:255',
            'ifsc_code' => 'nullable|array',
            'branch' => 'nullable|array',
            'bank_city' => 'nullable|array',
            'bank_state' => 'nullable|array',
            // File uploads
            'gst_no' => 'nullable|string|max:20|unique:craftmen,gst_no,' . $craftsman->id,
            'cin_no' => 'nullable|string|max:21',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'brand_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'gst_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'bis_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'msme_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'tan_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'cin_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'aadhar_image.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'pan_image.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'passbook_image.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'worker_name' => 'nullable|array',
            'worker_name.*' => 'required_with:worker_number.*,worker_image.*|nullable|string|max:255',
            'worker_number' => 'nullable|array',
            'worker_number.*' => 'required_with:worker_name.*,worker_image.*|nullable|string|max:20',
            'worker_image.*' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'craftsman_creation' => 'nullable|boolean',
            'edit_workorder' => 'nullable|boolean',
        ];

        // Only add unique validation if the values are different from current craftsman
        if ($request->mobile && $request->mobile !== $craftsman->mobile) {
            $rules['mobile'] .= '|unique:craftmen,mobile,' . $craftsman->id;
        }

        if ($request->email && $request->email !== $craftsman->email) {
            $rules['email'] .= '|unique:craftmen,email,' . $craftsman->id;
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $updateFields = [
            'craftman_code',
            'dear',
            'business_name',
            'name',
            'mobile',
            'landline',
            'email',
            'business_email',
            'refered_by',
            'more',
            'door_no',
            'shop_no',
            'complex_name',
            'building_name',
            'street_name',
            'area',
            'pincode',
            'city',
            'state',
            'map_location',
            'location_guide',
            'bis_no',
            'gst_no',
            'msme_no',
            'cin_no',
            'tan_no',
            'note'
        ];

        $updateData = [];
        foreach ($updateFields as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $request->get($field);
            }
        }

        // Header KYC mapping (first available element of arrays)
        if ($request->has('pan_number')) {
            $panArr = (array) $request->pan_number;
            $firstKey = array_key_first($panArr);
            $updateData['pan_no'] = $panArr[$firstKey] ?? null;
        }
        if ($request->has('aadhar_number')) {
            $aadharArr = (array) $request->aadhar_number;
            $firstKey = array_key_first($aadharArr);
            $updateData['aadhar_no'] = $aadharArr[$firstKey] ?? null;
        }
        if ($request->has('aadhar_name')) {
            $aadharNameArr = (array) $request->aadhar_name;
            $firstKey = array_key_first($aadharNameArr);
            $updateData['aadhar_name'] = $aadharNameArr[$firstKey] ?? null;
        }
        if ($request->has('bank_name')) {
            $bankArr = (array) $request->bank_name;
            $firstKey = array_key_first($bankArr);
            $updateData['bank_name'] = $bankArr[$firstKey] ?? null;
        }
        if ($request->has('account_holder_name')) {
            $accNameArr = (array) $request->account_holder_name;
            $firstKey = array_key_first($accNameArr);
            $updateData['account_name'] = $accNameArr[$firstKey] ?? null;
        }
        if ($request->has('account_number')) {
            $accNoArr = (array) $request->account_number;
            $firstKey = array_key_first($accNoArr);
            $updateData['account_no'] = $accNoArr[$firstKey] ?? null;
        }
        if ($request->has('ifsc_code')) {
            $ifscArr = (array) $request->ifsc_code;
            $firstKey = array_key_first($ifscArr);
            $updateData['ifsc_code'] = $ifscArr[$firstKey] ?? null;
        }
        if ($request->has('branch')) {
            $branchArr = (array) $request->branch;
            $firstKey = array_key_first($branchArr);
            $updateData['branch'] = $branchArr[$firstKey] ?? null;
        }
        if ($request->has('bank_city')) {
            $cityArr = (array) $request->bank_city;
            $firstKey = array_key_first($cityArr);
            $updateData['bank_city'] = $cityArr[$firstKey] ?? null;
        }
        if ($request->has('bank_state')) {
            $stateArr = (array) $request->bank_state;
            $firstKey = array_key_first($stateArr);
            $updateData['bank_state'] = $stateArr[$firstKey] ?? null;
        }

        if ($request->has('permissions') || $request->has('craftsman_creation') || $request->has('edit_workorder')) {
            $permissions = (array) $request->get('permissions', $craftsman->permissions ?? []);

            if ($request->has('craftsman_creation')) {
                if ($request->boolean('craftsman_creation')) {
                    if (!in_array('craftsman_creation', $permissions)) $permissions[] = 'craftsman_creation';
                } else {
                    $permissions = array_diff($permissions, ['craftsman_creation']);
                }
            }

            if ($request->has('edit_workorder')) {
                if ($request->boolean('edit_workorder')) {
                    if (!in_array('edit_workorder', $permissions)) $permissions[] = 'edit_workorder';
                } else {
                    $permissions = array_diff($permissions, ['edit_workorder']);
                }
            }

            $permissions[] = 'dashboard';
            $updateData['permissions'] = array_values(array_unique($permissions));
        }


        // Auto-fill City and State if Pincode is provided but City/State are missing
        if ($request->filled('pincode') && (empty($updateData['city']) || empty($updateData['state']))) {
            $pincodeData = $this->fetchPincodeData($request->pincode);
            if ($pincodeData && isset($pincodeData['Status']) && $pincodeData['Status'] === 'Success') {
                $postOffice = $pincodeData['PostOffice'][0];
                if (empty($updateData['city'])) $updateData['city'] = $postOffice['District'];
                if (empty($updateData['state'])) $updateData['state'] = $postOffice['State'];
            }
        }

        // Only update password if provided
        if ($request->password) {
            $updateData['password'] = bcrypt($request->password);
            $updateData['password_plain'] = $request->password;
        }

        $craftsman->update($updateData);

        // Delete existing related records only if new ones are provided
        if ($request->has('aadhar_name') || $request->has('aadhar_number') || $request->hasFile('aadhar_image')) {
            $craftsman->aadharDetails()->delete();
        }
        if ($request->has('pan_number') || $request->hasFile('pan_image')) {
            $craftsman->panDetails()->delete();
        }
        if ($request->has('bank_name') || $request->hasFile('passbook_image')) {
            $craftsman->bankDetails()->delete();
        }
        if ($request->has('worker_name') || $request->has('worker_number') || $request->hasFile('worker_image')) {
            $craftsman->workers()->delete();
        }

        // Create new multiple Aadhar details records
        if ($request->has('aadhar_name') || $request->has('aadhar_number') || $request->hasFile('aadhar_image')) {
            $aadharKeys = array_unique(array_merge(
                array_keys((array) $request->aadhar_name),
                array_keys((array) $request->aadhar_number),
                array_keys((array) $request->file('aadhar_image'))
            ));

            foreach ($aadharKeys as $i) {
                $aadharNames = (array) $request->aadhar_name;
                $aadharNumbers = (array) $request->aadhar_number;

                if (!isset($aadharNames[$i]) && !isset($aadharNumbers[$i]) && !$request->hasFile("aadhar_image.$i")) {
                    continue;
                }

                $aadharData = [
                    'craftman_id' => $craftsman->id,
                    'aadhar_name' => $aadharNames[$i] ?? null,
                    'aadhar_number' => $aadharNumbers[$i] ?? null,
                ];

                // Handle Aadhar image upload
                if ($request->hasFile("aadhar_image.$i")) {
                    $file = $request->file("aadhar_image.$i");
                    $filename = time() . '_aadhar_' . $i . '_' . $file->getClientOriginalName();
                    $file->storeAs('aadhar', $filename, 'public');
                    $aadharData['aadhar_image'] = 'aadhar/' . $filename;

                    // Update main table if it's the first one (index 0) or if main column is empty
                    if ($i === 0 || $i === '0' || empty($craftsman->aadhar_attach)) {
                        $craftsman->aadhar_attach = $aadharData['aadhar_image'];
                    }
                }

                \App\Models\CraftmanAadharDetail::create($aadharData);
            }
        }

        // Create new multiple PAN details records
        if ($request->has('pan_number') || $request->hasFile('pan_image')) {
            $panKeys = array_unique(array_merge(
                array_keys((array) $request->pan_number),
                array_keys((array) $request->file('pan_image'))
            ));

            foreach ($panKeys as $i) {
                $panNumbers = (array) $request->pan_number;

                if (!isset($panNumbers[$i]) && !$request->hasFile("pan_image.$i")) {
                    continue;
                }

                $panData = [
                    'craftman_id' => $craftsman->id,
                    'pan_number' => $panNumbers[$i] ?? null,
                ];

                // Handle PAN image upload
                if ($request->hasFile("pan_image.$i")) {
                    $file = $request->file("pan_image.$i");
                    $filename = time() . '_pan_' . $i . '_' . $file->getClientOriginalName();
                    $file->storeAs('pan', $filename, 'public');
                    $panData['pan_image'] = 'pan/' . $filename;

                    // Update main table if it's the first one (index 0) or if main column is empty
                    if ($i === 0 || $i === '0' || empty($craftsman->pan_attachment)) {
                        $craftsman->pan_attachment = $panData['pan_image'];
                    }
                }

                \App\Models\CraftmanPanDetail::create($panData);
            }
        }

        // Create new multiple Bank details records
        if ($request->has('bank_name') || $request->hasFile('passbook_image')) {
            $bankKeys = array_unique(array_merge(
                array_keys((array) $request->bank_name),
                array_keys((array) ($request->account_holder_name ?? [])),
                array_keys((array) ($request->account_number ?? [])),
                array_keys((array) $request->file('passbook_image'))
            ));

            foreach ($bankKeys as $i) {
                $bankNames = (array) $request->bank_name;
                $accountHolderNames = (array) ($request->account_holder_name ?? []);
                $accountNumbers = (array) ($request->account_number ?? []);

                if (!isset($bankNames[$i]) && !isset($accountHolderNames[$i]) && !isset($accountNumbers[$i]) && !$request->hasFile("passbook_image.$i")) {
                    continue;
                }

                $bankData = [
                    'craftman_id' => $craftsman->id,
                    'bank_name' => $bankNames[$i] ?? null,
                    'account_holder_name' => $accountHolderNames[$i] ?? null,
                    'account_number' => $accountNumbers[$i] ?? null,
                    'ifsc_code' => $request->ifsc_code[$i] ?? null,
                    'branch' => $request->branch[$i] ?? null,
                    'bank_city' => $request->bank_city[$i] ?? null,
                    'bank_state' => $request->bank_state[$i] ?? null,
                ];

                // Handle passbook image upload
                if ($request->hasFile("passbook_image.$i")) {
                    $file = $request->file("passbook_image.$i");
                    $filename = time() . '_passbook_' . $i . '_' . $file->getClientOriginalName();
                    $file->storeAs('passbook', $filename, 'public');
                    $bankData['passbook_image'] = 'passbook/' . $filename;

                    // Update main table if it's the first one (index 0) or if main column is empty
                    if ($i === 0 || $i === '0' || empty($craftsman->passbook)) {
                        $craftsman->passbook = $bankData['passbook_image'];
                    }
                }

                \App\Models\CraftmanBankDetail::create($bankData);
            }
        }

        // Create Workers records
        $workerKeys = array_unique(array_merge(
            array_keys((array) $request->worker_name),
            array_keys((array) $request->worker_number),
            array_keys((array) $request->file('worker_image'))
        ));

        foreach ($workerKeys as $i) {
            $workerNames = (array) $request->worker_name;
            $workerNumbers = (array) $request->worker_number;

            if (!isset($workerNames[$i]) && !isset($workerNumbers[$i]) && !$request->hasFile("worker_image.$i")) {
                continue;
            }

            $workerData = [
                'craftman_id' => $craftsman->id,
                'worker_name' => $workerNames[$i] ?? null,
                'worker_number' => $workerNumbers[$i] ?? null,
            ];

            if ($request->hasFile("worker_image.$i")) {
                $file = $request->file("worker_image.$i");
                $filename = time() . '_worker_' . $i . '_' . $file->getClientOriginalName();
                $file->storeAs('workers', $filename, 'public');
                $workerData['worker_image'] = 'workers/' . $filename;
            }

            \App\Models\CraftmanWorker::create($workerData);
        }

        // Handle file uploads
        if ($request->hasFile('image')) {
            // Delete old file if exists
            if ($craftsman->image && Storage::exists('public/' . $craftsman->image)) {
                Storage::delete('public/' . $craftsman->image);
            }
            $file = $request->file('image');
            $filename = time() . '_profile_' . $file->getClientOriginalName();
            $file->storeAs('craftmen', $filename, 'public');
            $craftsman->image = 'craftmen/' . $filename;
        }

        if ($request->hasFile('gst_attachment')) {
            if ($craftsman->gst_attachment && Storage::exists('public/' . $craftsman->gst_attachment)) {
                Storage::delete('public/' . $craftsman->gst_attachment);
            }
            $file = $request->file('gst_attachment');
            $filename = time() . '_gst_' . $file->getClientOriginalName();
            $file->storeAs('gst', $filename, 'public');
            $craftsman->gst_attachment = 'gst/' . $filename;
        }

        if ($request->hasFile('bis_attachment')) {
            if ($craftsman->bis_attachment && Storage::exists('public/' . $craftsman->bis_attachment)) {
                Storage::delete('public/' . $craftsman->bis_attachment);
            }
            $file = $request->file('bis_attachment');
            $filename = time() . '_bis_' . $file->getClientOriginalName();
            $file->storeAs('bis', $filename, 'public');
            $craftsman->bis_attachment = 'bis/' . $filename;
        }

        if ($request->hasFile('msme_attachment')) {
            if ($craftsman->msme_attachment && Storage::exists('public/' . $craftsman->msme_attachment)) {
                Storage::delete('public/' . $craftsman->msme_attachment);
            }
            $file = $request->file('msme_attachment');
            $filename = time() . '_msme_' . $file->getClientOriginalName();
            $file->storeAs('msme', $filename, 'public');
            $craftsman->msme_attachment = 'msme/' . $filename;
        }

        if ($request->hasFile('tan_attachment')) {
            if ($craftsman->tan_attachment && Storage::exists('public/' . $craftsman->tan_attachment)) {
                Storage::delete('public/' . $craftsman->tan_attachment);
            }
            $file = $request->file('tan_attachment');
            $filename = time() . '_tan_' . $file->getClientOriginalName();
            $file->storeAs('tan', $filename, 'public');
            $craftsman->tan_attachment = 'tan/' . $filename;
        }

        if ($request->hasFile('cin_attachment')) {
            if ($craftsman->cin_attachment && Storage::exists('public/' . $craftsman->cin_attachment)) {
                Storage::delete('public/' . $craftsman->cin_attachment);
            }
            $file = $request->file('cin_attachment');
            $filename = time() . '_cin_' . $file->getClientOriginalName();
            $file->storeAs('cin', $filename, 'public');
            $craftsman->cin_attachment = 'cin/' . $filename;
        }

        if ($request->hasFile('brand_logo')) {
            if ($craftsman->brand_logo && Storage::exists('public/' . $craftsman->brand_logo)) {
                Storage::delete('public/' . $craftsman->brand_logo);
            }
            $file = $request->file('brand_logo');
            $filename = time() . '_logo_' . $file->getClientOriginalName();
            $file->storeAs('logos', $filename, 'public');
            $craftsman->brand_logo = 'logos/' . $filename;
        }

        $craftsman->save();

        // Load the updated craftsman with all related details
        $craftsman->load(['aadharDetails', 'panDetails', 'bankDetails', 'workers', 'workOrders']);

        return response()->json([
            'success' => true,
            'message' => 'Craftsman updated successfully',
            'data' => $craftsman
        ]);
    }

    /**
     * Delete a craftsman
     */
    public function deleteCraftsman(Craftman $craftsman)
    {
        // Delete related records first
        $craftsman->aadharDetails()->delete();
        $craftsman->panDetails()->delete();
        $craftsman->bankDetails()->delete();
        $craftsman->workers()->delete();

        // Delete the craftsman
        $craftsman->delete();

        return response()->json([
            'success' => true,
            'message' => 'Craftsman deleted successfully'
        ]);
    }

    // ==================== WORK ORDER APIs ====================

    /**
     * Get all work orders with advanced tabs and filtering
     */
    public function getWorkOrders(Request $request)
    {
        $tab = $request->get('tab', 'new-orders');

        // Prepare counts for all tabs
        $counts = [
            'all' => WorkOrder::count(),
            'new' => WorkOrder::where('status', 'new')->count(),
            'allocated' => WorkOrder::where('status', 'allocated')
                ->where('craftsman_status', '!=', 'in_process')
                ->count(),
            'in_process' => WorkOrder::where('craftsman_status', 'in_process')->count(),
            'for_approval' => WorkOrder::where('status', 'for_approval')->count(),
            'completed' => WorkOrder::where('status', 'completed')->count(),
            'rejected' => WorkOrder::where('craftsman_status', 'rejected')->count(),
            'overdue' => WorkOrder::where('status', '!=', 'completed')
                ->where('craftsman_status', '!=', 'rejected')
                ->where(function ($q) {
                    $q->whereDate('due_date', '<', now()->toDateString())
                        ->orWhere(function ($sq) {
                            $sq->whereDate('due_date', now()->toDateString())
                                ->whereRaw('HOUR(NOW()) >= 12');
                        });
                })->count(),
        ];
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'created_at');
        // If sort_by is 'id', it's better to verify it exists, otherwise default to created_at
        if (!in_array($sortBy, ['id', 'work_order_number', 'customer_name', 'product_name', 'due_date', 'status', 'created_at'])) {
            $sortBy = 'created_at';
        }
        $sortOrder = $request->get('sort_order', 'desc');

        // Base Query
        $query = WorkOrder::with(['productCategory', 'subcategoryRelation', 'buyer', 'craftsman', 'product.images']);

        // Tab Logic
        switch ($tab) {
            case 'new-orders':
                $query->where('status', 'new');
                break;
            case 'allocated-orders':
                $query->where('status', 'allocated')
                    ->where('craftsman_status', '!=', 'in_process');
                break;
            case 'in-process-orders':
                $query->where('craftsman_status', 'in_process');
                break;
            case 'for-approval-orders':
                $query->where('status', 'for_approval');
                break;
            case 'completed-orders':
                $query->where('status', 'completed');
                break;
            case 'rejected-orders':
                $query->where('craftsman_status', 'rejected');
                break;
            case 'overdue-orders':
                $query->where('status', '!=', 'completed')
                    ->where('craftsman_status', '!=', 'rejected')
                    ->where(function ($q) {
                        $q->whereDate('due_date', '<', now()->toDateString())
                            ->orWhere(function ($sq) {
                                $sq->whereDate('due_date', now()->toDateString())
                                    ->whereRaw('HOUR(NOW()) >= 12');
                            });
                    });
                break;
            case 'all-orders':
                // No filters, show all work orders
                break;
            default:
                // Fallback to showing everything or just new? 
                // Let's stick to 'new' if tab is invalid/missing to match web behavior, 
                // OR if specific filters are passed without tab, respect those.
                // But user asked for tabs.
                if (!$request->has('tab')) {
                    // If no tab specified, maybe just standard filtering (backward compatibility)
                    if ($request->filled('status')) $query->where('status', $request->status);
                } else {
                    $query->where('status', 'new');
                }
                break;
        }

        // Additional Filters
        if ($request->filled('category_id')) {
            $query->where('product_category_id', $request->category_id);
        }

        if ($request->filled('bp_code_filter')) {
            $query->where('bp_code', $request->bp_code_filter);
        }

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('work_order_number', 'LIKE', "%$search%")
                    ->orWhere('product_code', 'LIKE', "%$search%")
                    ->orWhere('product_name', 'LIKE', "%$search%")
                    ->orWhere('customer_name', 'LIKE', "%$search%")
                    ->orWhere('bp_code', 'LIKE', "%$search%")
                    ->orWhere('reference_no', 'LIKE', "%$search%");
            });
        }

        // Sorting
        $query->orderBy($sortBy, $sortOrder);

        $workOrders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'counts' => $counts,
            'data' => $workOrders
        ]);
    }

    /**
     * Get a specific work order with all details
     */
    public function getWorkOrder(WorkOrder $workOrder)
    {
        $workOrder->load(['productCategory', 'subcategoryRelation', 'buyer', 'craftsman', 'images', 'product.images']);

        // Load all subcategories for this category for the UI dropdown
        $subcategoryOptions = [];
        if ($workOrder->product_category_id) {
            $subcategoryOptions = \App\Models\ProductSubcategory::where('product_category_id', $workOrder->product_category_id)->get();
        }

        return response()->json([
            'success' => true,
            'data' => $workOrder,
            'subcategory_options' => $subcategoryOptions
        ]);
    }

    /**
     * Create new work order
     */
    public function createWorkOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bp_code' => 'required|string|exists:buyers,bp_code',
            'customer_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'product_name' => 'nullable:product_code|string|max:255',
            'due_date' => 'required|date',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'product_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'product_year' => 'nullable|string',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'subcategory_id' => 'nullable|exists:product_subcategories,id',
            'type' => 'nullable|in:Piece,Pair',
            'open_close' => 'nullable|in:Open,Close',
            'size' => 'nullable|string|max:255',
            'length' => 'nullable|string|max:255',
            'weight_from' => 'nullable|numeric|min:0',
            'weight_to' => 'nullable|numeric|gte:weight_from',
            'hallmark' => 'nullable|string|max:255',
            'rodium' => 'nullable|string|max:255',
            'hook' => 'nullable|string|max:255',
            'stone' => 'nullable|string|max:255',
            'enamel' => 'nullable|string|max:255',
            'category_name' => 'nullable|string|max:255', // New field for on-the-fly creation
            'subcategory_name' => 'nullable|string|max:255', // New field for on-the-fly creation
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // Logic for Flow 1: If product_code matches an existing product, copy its image
        $finalProductCode = $request->product_code;
        $productImage = null;
        $existingProduct = null; // Initialize to avoid undefined variable error

        if (!empty($finalProductCode)) {
            $existingProduct = Product::with(['images', 'category', 'subcategory'])
                ->where('product_code', $finalProductCode)
                ->orWhere('design_code', $finalProductCode)
                ->first();

            if ($existingProduct) {
                // Auto-fill Product Details
                $productName = $existingProduct->product_name;
                $categoryName = $existingProduct->category ? $existingProduct->category->name : null;
                $subcategoryName = $existingProduct->subcategory ? $existingProduct->subcategory->name : null;
                $type = $existingProduct->type;
                $openClose = $existingProduct->open_close;
                $weightFrom = $existingProduct->weight_from;
                $weightTo = $existingProduct->weight_to;
                $hallmark = $existingProduct->hallmark;
                $rodium = $existingProduct->rodium;
                $hook = $existingProduct->hook;
                $size = $existingProduct->size;
                $stone = $existingProduct->stone;
                $enamel = $existingProduct->enamel;
                $length = $existingProduct->length;

                // Copy Image
                if ($existingProduct->images->count() > 0) {
                    $existingImage = $existingProduct->images->first();
                    $sourceImagePath = storage_path('app/public/' . $existingImage->path);

                    if (file_exists($sourceImagePath)) {
                        $imageName = time() . '_copied_from_product_' . basename($existingImage->path);
                        $destinationPath = public_path('images/work-orders/' . $imageName);

                        if (!file_exists(dirname($destinationPath))) {
                            mkdir(dirname($destinationPath), 0755, true);
                        }

                        copy($sourceImagePath, $destinationPath);
                        $productImage = 'images/work-orders/' . $imageName;

                        // Apply watermark
                        $watermarkService = new ImageWatermarkService();
                        $watermarkService->addWatermark($productImage, true);
                    }
                }
            }
        } else {
            // Generate OOXXXX style code
            $finalProductCode = $this->generateUniqueProductCode();
        }

        // Handle direct image upload
        if ($request->hasFile('product_image')) {
            $image = $request->file('product_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/work-orders'), $imageName);
            $productImage = 'images/work-orders/' . $imageName;

            $watermarkService = new ImageWatermarkService();
            $watermarkService->addWatermark($productImage, true);
        }

        // Resolve Category and Subcategory
        $categoryId = $request->product_category_id;
        $categoryName = null;
        $subcategoryId = $request->subcategory_id;
        $subcategoryName = null;

        // 1. If Product Code was used, prioritize its details
        if ($existingProduct) {
            $categoryId = $existingProduct->product_category_id;
            $categoryName = $existingProduct->category ? $existingProduct->category->name : null;
            $subcategoryId = $existingProduct->product_subcategory_id;
            $subcategoryName = $existingProduct->subcategory ? $existingProduct->subcategory->name : null;
        }
        // 2. Handle "New Name" inputs for overrides or connection for Manual Creation
        else {
            // Handle Category
            if ($request->filled('category_name')) {
                $category = ProductCategory::firstOrCreate(
                    ['name' => $request->category_name]
                );
                $categoryId = $category->id;
                $categoryName = $category->name;
            } elseif ($categoryId) {
                $cat = ProductCategory::find($categoryId);
                $categoryName = $cat ? $cat->name : null;
            }

            // Handle Subcategory (Only if we have a category)
            if ($request->filled('subcategory_name') && $categoryId) {
                $subcategory = ProductSubcategory::firstOrCreate([
                    'product_category_id' => $categoryId,
                    'name' => $request->subcategory_name
                ]);
                $subcategoryId = $subcategory->id;
                $subcategoryName = $subcategory->name;
            } elseif ($subcategoryId) {
                $sub = ProductSubcategory::find($subcategoryId);
                $subcategoryName = $sub ? $sub->name : null;
            }
        }

        // Generate work order number
        $workOrderNumber = WorkOrder::generateWorkOrderNumber();

        // Create record
        // Create record
        $workOrder = WorkOrder::create([
            'work_order_number' => $workOrderNumber,
            'product_image' => $productImage,
            'bp_code' => $request->bp_code,
            'customer_name' => $request->customer_name,
            'reference_no' => $request->reference_no,
            'due_date' => $request->due_date,
            'product_category' => $categoryName, // Resolved above
            'product_category_id' => $categoryId, // Resolved above
            'subcategory' => $subcategoryName, // Resolved above
            'subcategory_id' => $subcategoryId, // Resolved above
            'quantity' => $request->quantity,
            'type' => $type ?? $request->type,
            'open_close' => $openClose ?? $request->open_close,
            'weight_from' => $weightFrom ?? $request->weight_from,
            'weight_to' => $weightTo ?? $request->weight_to,
            'hallmark' => $hallmark ?? $request->hallmark,
            'rodium' => $rodium ?? $request->rodium,
            'hook' => $hook ?? $request->hook,
            'size' => $size ?? $request->size,
            'stone' => $stone ?? $request->stone,
            'enamel' => $enamel ?? $request->enamel,
            'length' => $length ?? $request->length,
            'product_code' => $finalProductCode,
            'relabel_code' => $request->relabel_code,
            'product_name' => $productName ?? $request->product_name,
            'craftsman_due_date' => $request->craftsman_due_date,
            'narration_craftsman' => $request->narration_craftsman,
            'narration_admin' => $request->narration_admin,
            'status' => 'new',
            'creator_type' => 'super_admin',
            'created_by' => Auth::id(),
        ]);

        // Handle multiple images upload
        if ($request->hasFile('product_images')) {
            $watermarkService = new ImageWatermarkService();
            foreach ($request->file('product_images') as $index => $file) {
                $imageName = time() . '_multi_' . $index . '_' . $file->getClientOriginalName();
                $file->move(public_path('images/work-orders'), $imageName);
                $imagePath = 'images/work-orders/' . $imageName;

                $watermarkService->addWatermark($imagePath, true);

                WorkOrderImage::create([
                    'work_order_id' => $workOrder->id,
                    'image_path' => $imagePath,
                ]);

                // If no single image was set yet, set the first of multi-images as the primary
                if (!$workOrder->product_image) {
                    $workOrder->update(['product_image' => $imagePath]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Work Order created successfully',
            'data' => [
                'work_order' => $workOrder,
                'product_code' => $finalProductCode
            ]
        ], 201);
    }

    /**
     * Allocate Work Order (Single)
     */
    public function allocateWorkOrder(Request $request, WorkOrder $workOrder)
    {
        $validator = Validator::make($request->all(), [
            'allocated_craftsman_bp_code' => 'required|exists:craftmen,craftman_code',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $workOrder->update([
            'allocated_craftsman_bp_code' => $request->allocated_craftsman_bp_code,
            'status' => 'allocated',
            'craftsman_status' => 'allocated',
        ]);

        // Send Notification
        try {
            $craftsman = \App\Models\Craftman::where('craftman_code', $request->allocated_craftsman_bp_code)->first();
            if ($craftsman && $craftsman->fcm_token) {
                $craftsman->notify(new WorkOrderAllocated($workOrder));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Work Order allocated successfully!',
            'data' => $workOrder
        ]);
    }

    /**
     * Bulk Allocate Work Orders
     */
    public function bulkAllocateWorkOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'work_order_ids' => 'required|array',
            'work_order_ids.*' => 'exists:work_orders,id',
            'allocated_craftsman_bp_code' => 'required|exists:craftmen,craftman_code',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $workOrderIds = $request->input('work_order_ids');
        $craftsmanBpCode = $request->input('allocated_craftsman_bp_code');

        WorkOrder::whereIn('id', $workOrderIds)
            ->update([
                'allocated_craftsman_bp_code' => $craftsmanBpCode,
                'status' => 'allocated',
                'craftsman_status' => 'allocated',
            ]);

        // Send Notification
        try {
            $craftsman = \App\Models\Craftman::where('craftman_code', $craftsmanBpCode)->first();
            if ($craftsman && $craftsman->fcm_token) {
                $count = count($workOrderIds);
                $message = "You have been allocated {$count} new Work Orders.";
                $firstOrder = WorkOrder::find($workOrderIds[0]); // Just for object reference
                $craftsman->notify(new WorkOrderAllocated($firstOrder, $message));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => count($workOrderIds) . ' Work Orders allocated successfully!'
        ]);
    }

    /**
     * Re-allocate Work Order
     */
    public function reallocateWorkOrder(Request $request, WorkOrder $workOrder)
    {
        $validator = Validator::make($request->all(), [
            'allocated_craftsman_bp_code' => 'required|exists:craftmen,craftman_code',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $workOrder->update([
            'allocated_craftsman_bp_code' => $request->allocated_craftsman_bp_code,
            'status' => 'allocated',
            'craftsman_status' => 'allocated',
        ]);

        // Send Notification
        try {
            $craftsman = \App\Models\Craftman::where('craftman_code', $request->allocated_craftsman_bp_code)->first();
            if ($craftsman && $craftsman->fcm_token) {
                $craftsman->notify(new WorkOrderAllocated($workOrder, "Work Order #{$workOrder->work_order_number} has been reallocated to you."));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Work Order reallocated successfully!',
            'data' => $workOrder
        ]);
    }

    /**
     * Bulk Approve Work Orders
     */
    public function bulkApproveWorkOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'work_order_ids' => 'required|array',
            'work_order_ids.*' => 'exists:work_orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $workOrderIds = $request->input('work_order_ids');

        WorkOrder::whereIn('id', $workOrderIds)
            ->where('status', 'for_approval')
            ->update([
                'status' => 'completed',
                'approved_by' => auth('sanctum')->id()
            ]);

        // Notify Original Requester (Grouped)
        try {
            $recipients = [];
            foreach ($workOrderIds as $id) {
                $workOrder = WorkOrder::find($id);
                if ($workOrder && $workOrder->status === 'completed') {
                    $recipientKey = "{$workOrder->creator_type}_{$workOrder->creator_user_code}";
                    if (!isset($recipients[$recipientKey])) {
                        $recipient = null;
                        if ($workOrder->creator_type === 'buyer') {
                            $recipient = \App\Models\Buyer::where('bp_code', $workOrder->creator_user_code)->first();
                        } elseif ($workOrder->creator_type === 'key_user') {
                            $recipient = \App\Models\KeyUser::where('user_code', $workOrder->creator_user_code)->first();
                        } elseif ($workOrder->creator_type === 'user') {
                            $recipient = \App\Models\User::where('user_code', $workOrder->creator_user_code)->first();
                        }

                        if ($recipient && $recipient->fcm_token) {
                            $recipients[$recipientKey] = [
                                'model' => $recipient,
                                'count' => 1
                            ];
                        }
                    } else {
                        $recipients[$recipientKey]['count']++;
                    }
                }
            }

            foreach ($recipients as $data) {
                $recipient = $data['model'];
                $count = $data['count'];
                $message = $count > 1
                    ? "Your {$count} Work Orders have been approved and completed."
                    : "Your Work Order has been approved and completed.";

                $recipient->notify(new \App\Notifications\WorkOrderCompleted(WorkOrder::find($workOrderIds[0]), $message));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to notify requesters on bulk approval (API): ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => count($workOrderIds) . ' Work Orders approved successfully!'
        ]);
    }

    // ==================== PURCHASE ORDER APIs ====================

    /**
     * Get a single Purchase Order with full item details resolved.
     * Returns category_name, subcategory_name, full image_url, design_code per item.
     */
    public function getPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $itemsWithDetails    = $this->resolvePurchaseOrderItems($purchaseOrder->items ?? []);
        $rejectedWithDetails = $this->resolvePurchaseOrderItems($purchaseOrder->rejected_items ?? []);

        return response()->json([
            'success' => true,
            'data'    => array_merge($purchaseOrder->toArray(), [
                'items'          => $itemsWithDetails,
                'rejected_items' => $rejectedWithDetails,
            ])
        ]);
    }

    /**
     * Get all purchase orders with advanced tabs and filtering
     */
    public function getPurchaseOrders(Request $request)
    {
        $tab = $request->get('tab', 'created');
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'created_at');

        // Validate sort column
        if (!in_array($sortBy, ['id', 'purchase_order_code', 'due_date', 'created_at', 'updated_at'])) {
            $sortBy = 'created_at';
        }
        $sortOrder = in_array(strtolower($request->get('sort_order', 'desc')), ['asc', 'desc'])
            ? $request->get('sort_order', 'desc')
            : 'desc';

        // NOTE: 'items' is a JSON column, NOT a relationship — do NOT use with() on it
        $query = PurchaseOrder::query();

        // Tab Logic (mirrors web PurchaseOrderController@index)
        switch ($tab) {
            case 'created':
                $query->where('status', 'created')->whereNull('allocated_craftsman_code');
                break;
            case 'allocated':
                $query->where('craftsman_status', 'allocated')->where('status', 'created');
                break;
            case 'in_process':
                $query->where('status', 'in_process');
                break;
            case 'for_approval':
                $query->where('status', 'for_approval');
                break;
            case 'completed':
                $query->where('status', 'completed');
                break;
            case 'rejected':
                $query->where(function ($q) {
                    $q->where('craftsman_status', 'rejected')
                        ->orWhereRaw('JSON_LENGTH(rejected_items) > 0');
                });
                break;
            default:
                $query->where('status', 'created')->whereNull('allocated_craftsman_code');
                break;
        }

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('purchase_order_code', 'LIKE', "%$search%")
                    ->orWhere('notes', 'LIKE', "%$search%")
                    ->orWhereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", ["%$search%"]);
            });
        }

        // Category Filter (JSON check)
        if ($request->filled('category_filter')) {
            $query->whereRaw("JSON_CONTAINS(items, ?, '$')", [json_encode(['category' => (int)$request->category_filter])]);
        }

        // Sorting
        $query->orderBy($sortBy, $sortOrder);

        $purchaseOrders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $purchaseOrders
        ]);
    }


    /**
     * Allocate Purchase Order (Single)
     */
    public function allocatePurchaseOrder(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validator = Validator::make($request->all(), [
            'allocated_craftsman_code' => 'required|exists:craftmen,craftman_code',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $purchaseOrder->update([
            'allocated_craftsman_code' => $request->allocated_craftsman_code,
            'craftsman_status' => 'allocated'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Purchase Order allocated successfully!',
            'data' => $purchaseOrder
        ]);
    }

    /**
     * Bulk Allocate Purchase Orders
     */
    public function bulkAllocatePurchaseOrder(Request $request)
    {
        Log::info('Bulk Allocate Purchase Orders Triggered', $request->all());

        $validator = Validator::make($request->all(), [
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:purchase_orders,id',
            'allocated_craftsman_code' => 'required|exists:craftmen,craftman_code',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $orderIds = $request->input('order_ids');
        $craftsmanCode = $request->input('allocated_craftsman_code');

        try {
            PurchaseOrder::whereIn('id', $orderIds)->update([
                'allocated_craftsman_code' => $craftsmanCode,
                'craftsman_status' => 'allocated'
            ]);

            // Notify Craftsman
            try {
                $craftsman = \App\Models\Craftman::where('craftman_code', $craftsmanCode)->first();
                if ($craftsman && $craftsman->fcm_token) {
                    $count = count($orderIds);
                    $firstOrder = PurchaseOrder::find($orderIds[0]);
                    $message = "You have been allocated {$count} new Purchase Orders.";
                    $craftsman->notify(new \App\Notifications\PurchaseOrderAllocated($firstOrder, $message));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to notify craftsman on bulk PO allocation (API): ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => count($orderIds) . ' Purchase Orders allocated successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk Allocation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to allocate purchase orders. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Re-allocate Purchase Order
     */
    public function reallocatePurchaseOrder(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Reallocation logic from web: Reset to created
        // Or if API implies re-sending to someone else? 
        // Web Controller `reallocate` actually resets it to 'created' and clears craftsman.
        // But user asked for "reallocate". 
        // Actually, Web's `reallocate` method: resets to created.

        $purchaseOrder->update([
            'status' => 'created',
            'craftsman_status' => null,
            'allocated_craftsman_code' => null,
            'rejected_items' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Purchase Order reset/reallocated successfully (Moved to Created)',
            'data' => $purchaseOrder
        ]);
    }

    /**
     * Bulk Approve Purchase Orders
     */
    public function bulkApprovePurchaseOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:purchase_orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $orderIds = $request->input('order_ids');

        PurchaseOrder::whereIn('id', $orderIds)
            ->where('status', 'for_approval')
            ->update([
                'status' => 'completed',
            ]);

        // Send Bulk Notification
        try {
            $adminUsers = \App\Models\ProcessOwner::where('role', 'super_admin')->get();
            foreach ($adminUsers as $admin) {
                if ($admin->fcm_token) {
                    $admin->notify(new \App\Notifications\PurchaseOrderCompleted(PurchaseOrder::find($orderIds[0])));
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send bulk PO approval notification (API): ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => count($orderIds) . ' Purchase Orders approved successfully!'
        ]);
    }

    /**
     * Approve a single Purchase Order (for_approval -> completed)
     */
    public function approvePurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update([
            'status' => 'completed',
            'craftsman_status' => 'completed'
        ]);

        // Send Notification
        try {
            $adminUsers = \App\Models\ProcessOwner::where('role', 'super_admin')->get();
            foreach ($adminUsers as $admin) {
                if ($admin->fcm_token) {
                    $admin->notify(new \App\Notifications\PurchaseOrderCompleted($purchaseOrder));
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send PO approval notification (API): ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Purchase Order approved successfully.',
            'data' => $purchaseOrder
        ]);
    }

    /**
     * Update Purchase Order Status (in_process / for_approval / etc.)
     * Used by craftsman flow or SuperAdmin to manually move status.
     */
    public function updatePurchaseOrderStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $allowedStatuses = ['created', 'in_process', 'for_approval', 'completed'];

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:' . implode(',', $allowedStatuses),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $purchaseOrder->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Purchase Order status updated to ' . $request->status,
            'data' => $purchaseOrder
        ]);
    }

    /**
     * Create a new Purchase Order
     * Mirrors web PurchaseOrderController@store
     * Items array format:
     *   items[0][product_id], items[0][design_code], items[0][category],
     *   items[0][grams][], items[0][quantity][], items[0][image] (file)
     */
    public function createPurchaseOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'due_date'              => 'nullable|date',
            'items'                 => 'nullable|array|min:1',
            'items.*.product_id'    => 'nullable|exists:products,id',
            'items.*.design_code'   => 'nullable|string',
            'items.*.category'      => 'nullable',
            'items.*.grams'         => 'nullable|array',
            'items.*.quantity'      => 'nullable|array',
            'notes'                 => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        $purchaseOrderCode = PurchaseOrder::generatePurchaseOrderCode();
        $items = $request->items;
        $uploadedFiles = $request->file('items') ?? [];

        $processedItems = [];
        foreach ($items as $index => $item) {
            // Skip items marked for deletion
            if (isset($item['_deleted']) && $item['_deleted'] == '1') {
                continue;
            }

            // ── Resolve design code & image from product if not provided ──────
            if (empty($item['design_code']) && !empty($item['product_id'])) {
                $product = Product::with(['designs', 'images'])->find($item['product_id']);
                if ($product) {
                    $design = $product->designs->first();
                    if ($design) {
                        $item['design_code'] = $design->design_code;
                        // Auto-fill image from design if no file uploaded
                        if (empty($item['image']) && $design->image) {
                            $item['image'] = $design->image;
                        }
                    }
                    if (empty($item['design_code'])) {
                        $item['design_code'] = $product->design_code;
                    }
                }
            }

            // ── Handle manual image file upload ───────────────────────────────
            if (isset($uploadedFiles[$index]['image'])) {
                $file      = $uploadedFiles[$index]['image'];
                $imageName = time() . "_{$index}_" . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/purchase-orders'), $imageName);
                $item['image'] = 'images/purchase-orders/' . $imageName;
            } elseif (empty($item['image'])) {
                $item['image'] = null;
            }

            // ── Multi-row weight calculation ──────────────────────────────────
            $total = 0;
            if (isset($item['grams']) && is_array($item['grams'])) {
                foreach ($item['grams'] as $i => $gram) {
                    $total += floatval($gram) * intval($item['quantity'][$i] ?? 0);
                }
            }
            $item['total'] = $total;

            $processedItems[] = $item;
        }

        $purchaseOrder = PurchaseOrder::create([
            'purchase_order_code' => $purchaseOrderCode,
            'due_date'            => $request->due_date,
            'notes'               => $request->notes,
            'items'               => $processedItems,
            'status'              => 'created',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Purchase Order created successfully',
            'data'    => $purchaseOrder
        ], 201);
    }

    /**
     * Update a Purchase Order
     * Mirrors web PurchaseOrderController@update
     */
    public function updatePurchaseOrder(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validator = Validator::make($request->all(), [
            'due_date'              => 'nullable|date',
            'items'                 => 'nullable|array',
            'items.*.product_id'    => 'nullable|exists:products,id',
            'items.*.design_code'   => 'nullable|string',
            'items.*.category'      => 'nullable',
            'items.*.grams'         => 'required_with:items|array',
            'items.*.quantity'      => 'required_with:items|array',
            'notes'                 => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        $items         = $request->items ?? [];
        $uploadedFiles = $request->file('items') ?? [];
        $existingItems = $purchaseOrder->items ?? [];

        $processedItems = [];
        foreach ($items as $index => $item) {
            // Skip items marked for deletion
            if (isset($item['_deleted']) && $item['_deleted'] == '1') {
                continue;
            }

            // ── Resolve design code from product if not provided ──────────────
            if (empty($item['design_code']) && !empty($item['product_id'])) {
                $product = Product::with(['designs'])->find($item['product_id']);
                if ($product) {
                    $design = $product->designs->first();
                    if ($design) {
                        $item['design_code'] = $design->design_code;
                        if (empty($item['old_image']) && $design->image) {
                            $item['old_image'] = $design->image;
                        }
                    }
                    if (empty($item['design_code'])) {
                        $item['design_code'] = $product->design_code;
                    }
                }
            }

            // ── Handle image: new upload > request image > DB fallback > null ──
            if (isset($uploadedFiles[$index]['image'])) {
                $file      = $uploadedFiles[$index]['image'];
                $imageName = time() . "_{$index}_" . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/purchase-orders'), $imageName);
                $item['image'] = 'images/purchase-orders/' . $imageName;
            } else {
                // Check multiple request fields
                $requestImage = $item['image'] ?? $item['image_url'] ?? $item['old_image'] ?? null;

                if ($requestImage) {
                    // If the frontend sends the full URL, strip the base URL for DB storage
                    $path = str_replace(asset(''), '', $requestImage);
                    $item['image'] = ltrim($path, '/');
                } elseif (isset($existingItems[$index]['image'])) {
                    // CRITICAL: Fallback to existing DB record if missing in request
                    $item['image'] = $existingItems[$index]['image'];
                } else {
                    $item['image'] = null;
                }
            }
            unset($item['old_image']);   // clean up helper key
            unset($item['image_url']);   // clean up helper key

            // ── Multi-row weight calculation ──────────────────────────────────
            $total = 0;
            if (isset($item['grams']) && is_array($item['grams'])) {
                foreach ($item['grams'] as $i => $gram) {
                    $total += floatval($gram) * intval($item['quantity'][$i] ?? 0);
                }
            }
            $item['total'] = $total;

            $processedItems[] = $item;
        }

        $updateData = [
            'due_date' => $request->due_date ?? $purchaseOrder->due_date,
            'notes'    => $request->has('notes') ? $request->notes : $purchaseOrder->notes,
        ];
        if (!empty($processedItems)) {
            $updateData['items'] = $processedItems;
        }

        $purchaseOrder->update($updateData);

        $po = $purchaseOrder->fresh();
        $itemsWithDetails    = $this->resolvePurchaseOrderItems($po->items ?? []);
        $rejectedWithDetails = $this->resolvePurchaseOrderItems($po->rejected_items ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Purchase Order updated successfully',
            'data'    => array_merge($po->toArray(), [
                'items'          => $itemsWithDetails,
                'rejected_items' => $rejectedWithDetails,
            ])
        ]);
    }

    /**
     * Delete a Purchase Order
     */
    public function deletePurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Purchase Order deleted successfully'
        ]);
    }

    /**
     * Shared helper: resolve each item in a purchase order items array.
     * Returns category_name, subcategory_name, full image_url, design_code,
     * design_name, product details, and calculated totals.
     */
    private function resolvePurchaseOrderItems(array $items): array
    {
        $resolved = [];

        foreach ($items as $item) {
            $product = Product::with(['subcategory', 'category', 'images', 'designs'])
                ->find($item['product_id'] ?? null);

            // ── Totals ────────────────────────────────────────────────────────
            $total            = 0;
            $individualTotals = [];
            if (isset($item['grams']) && is_array($item['grams'])) {
                foreach ($item['grams'] as $i => $gram) {
                    $t                  = floatval($gram) * intval($item['quantity'][$i] ?? 0);
                    $individualTotals[] = $t;
                    $total             += $t;
                }
            }

            // ── Design ───────────────────────────────────────────────────────
            $designCode = $item['design_code'] ?? '';
            $designName = '';
            $design     = null;

            if ($product) {
                $design = $product->designs->first();
                if ($design) {
                    $designCode = $designCode ?: ($design->design_code ?? '');
                    $designName = $design->design_name ?? '';
                }
                if (empty($designCode)) {
                    $designCode = $product->design_code ?? '';
                }
            }

            // ── Image URL (full URL) ──────────────────────────────────────────
            $imageUrl = '';
            $rawImage = $item['image'] ?? null;

            if ($rawImage) {
                // Already an absolute URL — leave it
                if (str_starts_with($rawImage, 'http://') || str_starts_with($rawImage, 'https://')) {
                    $imageUrl = $rawImage;
                } else {
                    $path = $rawImage;
                    if (!str_starts_with($path, 'storage/') && !str_starts_with($path, 'images/')) {
                        $path = 'storage/' . $path;
                    }
                    $imageUrl = asset($path);
                }
            }

            // Fallback: design image
            if (empty($imageUrl) && $design && $design->image) {
                $path = $design->image;
                if (!str_starts_with($path, 'storage/')) {
                    $path = 'storage/' . $path;
                }
                $imageUrl = asset($path);
            }

            // Fallback: product images
            if (empty($imageUrl) && $product && $product->images->count() > 0) {
                $path = $product->images->first()->path;
                if (!str_starts_with($path, 'storage/')) {
                    $path = 'storage/' . $path;
                }
                $imageUrl = asset($path);
            }

            // ── Category / Subcategory names ──────────────────────────────────
            $categoryName    = 'N/A';
            $subcategoryName = 'N/A';

            // 1. Resolve Category Name
            if ($product && $product->category) {
                $categoryName = $product->category->name;
            } elseif (!empty($item['category'])) {
                if (is_numeric($item['category'])) {
                    $cat = ProductCategory::find($item['category']);
                    $categoryName = $cat ? $cat->name : 'N/A';
                } else {
                    $categoryName = $item['category']; // Use as name if string
                }
            } elseif (!empty($item['category_id'])) {
                $cat = ProductCategory::find($item['category_id']);
                $categoryName = $cat ? $cat->name : 'N/A';
            }

            // 2. Resolve Subcategory Name
            if ($product && $product->subcategory) {
                $subcategoryName = $product->subcategory->name;
            } elseif ($product && $product->product_subcategory_id) {
                // Manual lookup if relationship is somehow missing
                $sub = ProductSubcategory::find($product->product_subcategory_id);
                $subcategoryName = $sub ? $sub->name : 'N/A';
            } elseif (!empty($item['subcategory'])) {
                if (is_numeric($item['subcategory'])) {
                    $sub = ProductSubcategory::find($item['subcategory']);
                    $subcategoryName = $sub ? $sub->name : 'N/A';
                } else {
                    $subcategoryName = $item['subcategory'];
                }
            } elseif (!empty($item['subcategory_id'])) {
                $sub = ProductSubcategory::find($item['subcategory_id']);
                $subcategoryName = $sub ? $sub->name : 'N/A';
            } elseif (!empty($item['sub_category_id'])) {
                $sub = ProductSubcategory::find($item['sub_category_id']);
                $subcategoryName = $sub ? $sub->name : 'N/A';
            } elseif (!empty($item['subcategory_name'])) {
                $subcategoryName = $item['subcategory_name'];
            } elseif (!empty($item['sub_category_name'])) {
                $subcategoryName = $item['sub_category_name'];
            }

            $resolved[] = array_merge($item, [
                // Enriched computed fields
                'image'              => $imageUrl, // Full URL as requested
                'image_url'          => $imageUrl, // Full URL as requested
                'category_name'      => $categoryName,
                'subcategory_name'   => $subcategoryName,
                'sub_category_name'  => $subcategoryName, // Variant with underscore
                'produts_category'   => $categoryName,   // Typo mentioned by user
                'design_code'        => $designCode,
                'design_name'        => $designName,
                // Numerics
                'total'              => $total,
                'individual_totals'  => $individualTotals,
                // Related models
                'product'            => $product,
                'design'             => $design,
            ]);
        }

        return $resolved;
    }

    /**
     * AJAX helper: Get products by category with design code + image URL
     * Mirrors web PurchaseOrderController@getProductsByCategory
     * GET /api/super-admin/purchase-orders/products-by-category?category_id=1
     */
    public function getPoProductsByCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer|exists:product_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'category_id is required',
                'errors'  => $validator->errors()
            ], 422);
        }

        $query = Product::with(['subcategory', 'category', 'images', 'designs'])
            ->where('product_category_id', $request->category_id)
            ->where(function ($q) {
                $q->whereNotNull('bp_code')->orWhereNotNull('created_by');
            });

        // Search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('product_name', 'LIKE', "%$searchTerm%")
                    ->orWhere('product_code', 'LIKE', "%$searchTerm%")
                    ->orWhere('design_code', 'LIKE', "%$searchTerm%");
            });
        }

        $products = $query->get()
            ->map(function ($product) {
                $designCode = '';
                $imageUrl   = '';

                $design = $product->designs->first();
                if ($design) {
                    $designCode = $design->design_code;
                    if ($design->image) {
                        $path = $design->image;
                        if (!str_starts_with($path, 'storage/')) {
                            $path = 'storage/' . $path;
                        }
                        $imageUrl = asset($path);
                    }
                }

                if (empty($designCode)) {
                    $designCode = $product->design_code ?? '';
                }

                // Fallback: product images
                if (empty($imageUrl) && $product->images->count() > 0) {
                    $path = $product->images->first()->path;
                    if (!str_starts_with($path, 'storage/')) {
                        $path = 'storage/' . $path;
                    }
                    $imageUrl = asset($path);
                }

                return [
                    'id'           => $product->id,
                    'product_name' => $product->product_name,
                    'product_code' => $product->product_code,
                    'design_code'  => $designCode,
                    'image_url'    => $imageUrl,
                    'category_id'  => $product->product_category_id,
                    'subcategory_id' => $product->product_subcategory_id,
                    'subcategory'  => $product->subcategory,
                    'category'     => $product->category,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $products
        ]);
    }

    /**
     * AJAX helper: Get designs for a product
     * Mirrors web PurchaseOrderController@getDesignsByProduct
     * GET /api/super-admin/purchase-orders/designs-by-product?product_id=5
     */
    public function getPoDesignsByProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'product_id is required',
                'errors'  => $validator->errors()
            ], 422);
        }

        $query = Design::where('product_id', $request->product_id);

        // Search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('design_name', 'LIKE', "%$searchTerm%")
                    ->orWhere('design_code', 'LIKE', "%$searchTerm%");
            });
        }

        $designs = $query->get(['id', 'design_code', 'design_name', 'image'])
            ->map(function ($d) {
                $imageUrl = '';
                if ($d->image) {
                    $path = $d->image;
                    if (!str_starts_with($path, 'storage/')) {
                        $path = 'storage/' . $path;
                    }
                    $imageUrl = asset($path);
                }
                return [
                    'id'          => $d->id,
                    'design_code' => $d->design_code,
                    'design_name' => $d->design_name,
                    'image'       => $d->image,
                    'image_url'   => $imageUrl,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $designs
        ]);
    }

    /**
     * AJAX helper: Get product details by design code
     * Mirrors web PurchaseOrderController@getProductByDesignCode
     * GET /api/super-admin/purchase-orders/product-by-design-code?design_code=DC0001
     */
    public function getPoProductByDesignCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'design_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'design_code is required',
                'errors'  => $validator->errors()
            ], 422);
        }

        $design = Design::with(['product.category', 'product.subcategory', 'product.images'])
            ->where('design_code', $request->design_code)
            ->first();

        if (!$design || !$design->product) {
            return response()->json([
                'success' => false,
                'message' => 'Design not found'
            ], 404);
        }

        $product  = $design->product;
        $imageUrl = '';

        if ($design->image) {
            $path = $design->image;
            if (!str_starts_with($path, 'storage/')) {
                $path = 'storage/' . $path;
            }
            $imageUrl = asset($path);
        }

        // Fallback: product images
        if (empty($imageUrl) && $product->images->count() > 0) {
            $path = $product->images->first()->path;
            if (!str_starts_with($path, 'storage/')) {
                $path = 'storage/' . $path;
            }
            $imageUrl = asset($path);
        }

        return response()->json([
            'success'        => true,
            'data'           => [
                'product_id'     => $product->id,
                'product_name'   => $product->product_name,
                'product_code'   => $product->product_code,
                'design_code'    => $design->design_code,
                'design_name'    => $design->design_name,
                'image_url'      => $imageUrl,
                'image'          => $design->image,
                'category_id'    => $product->product_category_id,
                'subcategory_id' => $product->product_subcategory_id,
                'category'       => $product->category,
                'subcategory'    => $product->subcategory,
            ]
        ]);
    }

    /**
     * Get all admins
     */
    public function getAdmins(Request $request)
    {
        $query = ProcessOwner::query();

        // Search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%$searchTerm%")
                    ->orWhere('email', 'LIKE', "%$searchTerm%")
                    ->orWhere('role', 'LIKE', "%$searchTerm%");
            });
        }

        // Filtering
        if ($request->filled('ids')) {
            $query->whereIn('id', (array) $request->ids);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Export (CSV) or Print (JSON) View
        if ($request->get('export') === 'true' || $request->get('print') === 'true') {
            $admins = $query->get();

            if ($request->get('export') === 'true') {
                $headers = [
                    "Content-type" => "text/csv",
                    "Content-Disposition" => "attachment; filename=admins_export_" . date('Y-m-d') . ".csv",
                    "Pragma" => "no-cache",
                    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                    "Expires" => "0"
                ];

                $columns = ['ID', 'Name', 'Email', 'Role', 'Status', 'Created At'];

                $callback = function () use ($admins, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);

                    foreach ($admins as $admin) {
                        fputcsv($file, [
                            $admin->id,
                            $admin->name,
                            $admin->email,
                            $admin->role,
                            $admin->status,
                            $admin->created_at,
                        ]);
                    }
                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            }

            return response()->json([
                'success' => true,
                'data' => $admins
            ]);
        }

        // Standard Paginated View
        $admins = $query->get();

        return response()->json([
            'success' => true,
            'data' => $admins
        ]);
    }

    // ==================== PRODUCT APIs ====================

    /**
     * Get all products
     */
    public function getProducts(Request $request)
    {
        $query = Product::with(['category', 'subcategory', 'creator', 'images']);

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('product_category_id', $request->category_id);
        }

        // Filter by subcategory
        if ($request->filled('subcategory_id')) {
            $query->where('product_subcategory_id', $request->subcategory_id);
        }

        // Filter by design status
        if ($request->filled('design_status')) {
            $query->where('design_status', $request->design_status);
        }

        // Search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('product_code', 'LIKE', "%$searchTerm%")
                    ->orWhere('product_name', 'LIKE', "%$searchTerm%")
                    ->orWhere('design_code', 'LIKE', "%$searchTerm%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get a specific product
     */
    public function getProduct(Product $product)
    {
        $product->load(['category', 'subcategory', 'creator', 'images']);
        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Get all product categories
     */
    public function getProductCategories()
    {
        $categories = ProductCategory::select('id', 'name', 'has_hook', 'has_enamel', 'has_rodium', 'has_open_close', 'has_stone')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get subcategories for a specific category.
     * Each item includes design_code + image_url from its first product's design,
     * so purchase order UI can auto-fill on subcategory selection.
     */
    public function getProductSubcategories($categoryId)
    {
        $subcategories = ProductSubcategory::where('product_category_id', $categoryId)
            ->select('id', 'name', 'product_category_id')
            ->orderBy('name')
            ->get()
            ->map(function ($subcategory) {
                $designCode = '';
                $imageUrl   = '';
                $designName = '';
                $productId  = null;

                $product = Product::with(['designs', 'images'])
                    ->where('product_subcategory_id', $subcategory->id)
                    ->where(function ($q) {
                        $q->whereNotNull('bp_code')->orWhereNotNull('created_by');
                    })
                    ->first();

                if ($product) {
                    $productId = $product->id;
                    $design    = $product->designs->first();

                    if ($design) {
                        $designCode = $design->design_code ?? '';
                        $designName = $design->design_name ?? '';
                        if ($design->image) {
                            $path = $design->image;
                            if (!str_starts_with($path, 'storage/')) {
                                $path = 'storage/' . $path;
                            }
                            $imageUrl = asset($path);
                        }
                    }

                    if (empty($designCode)) {
                        $designCode = $product->design_code ?? '';
                    }

                    if (empty($imageUrl) && $product->images->count() > 0) {
                        $path = $product->images->first()->path;
                        if (!str_starts_with($path, 'storage/')) {
                            $path = 'storage/' . $path;
                        }
                        $imageUrl = asset($path);
                    }
                }

                return [
                    'id'                  => $subcategory->id,
                    'name'                => $subcategory->name,
                    'product_category_id' => $subcategory->product_category_id,
                    'product_id'          => $productId,
                    'design_code'         => $designCode,
                    'design_name'         => $designName,
                    'image_url'           => $imageUrl,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $subcategories
        ]);
    }

    /**
     * Get design code + image for a single selected subcategory.
     * Used by purchase order create/edit: when user selects a subcategory,
     * call this to get the design_code and image to auto-fill.
     *
     * GET /api/super-admin/purchase-orders/helpers/subcategory-design?subcategory_id=3
     */
    public function getSubcategoryDesignInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subcategory_id' => 'required|integer|exists:product_subcategories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'subcategory_id is required',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Load the first matching product under this subcategory
        $product = Product::with(['designs', 'images'])
            ->where('product_subcategory_id', $request->subcategory_id)
            ->where(function ($q) {
                $q->whereNotNull('bp_code')->orWhereNotNull('created_by');
            })
            ->first();

        if (!$product) {
            return response()->json([
                'success'    => true,
                'design_code' => '',
                'image_url'  => '',
                'product_id' => null,
                'message'    => 'No product found for this subcategory'
            ]);
        }

        $designCode = '';
        $imageUrl   = '';
        $designName = '';

        $design = $product->designs->first();
        if ($design) {
            $designCode = $design->design_code ?? '';
            $designName = $design->design_name ?? '';
            if ($design->image) {
                $path = $design->image;
                if (!str_starts_with($path, 'storage/')) {
                    $path = 'storage/' . $path;
                }
                $imageUrl = asset($path);
            }
        }

        if (empty($designCode)) {
            $designCode = $product->design_code ?? '';
        }

        if (empty($imageUrl) && $product->images->count() > 0) {
            $path = $product->images->first()->path;
            if (!str_starts_with($path, 'storage/')) {
                $path = 'storage/' . $path;
            }
            $imageUrl = asset($path);
        }

        return response()->json([
            'success'     => true,
            'product_id'  => $product->id,
            'design_code' => $designCode,
            'design_name' => $designName,
            'image_url'   => $imageUrl,
        ]);
    }

    /**
     * Get category options (hook, enamel, stone, etc.) for frontend
     */
    public function getCategoryOptions(Request $request)
    {
        $categoryId = $request->query('category_id');

        if (!$categoryId) {
            return response()->json([
                'success' => false,
                'message' => 'Category ID is required'
            ], 422);
        }

        $category = ProductCategory::find($categoryId);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $options = [
            'has_hook' => (bool) $category->has_hook,
            'has_enamel' => (bool) $category->has_enamel,
            'has_rodium' => (bool) $category->has_rodium,
            'has_open_close' => (bool) $category->has_open_close,
            'has_stone' => (bool) $category->has_stone,
        ];

        return response()->json([
            'success' => true,
            'data' => $options
        ]);
    }

    /**
     * Create new product category
     */
    public function createProductCategory(Request $request)
    {
        // If parent_category_id is present, create subcategory
        if ($request->filled('parent_category_id')) {
            $validator = Validator::make($request->all(), [
                'parent_category_id' => 'required|exists:product_categories,id',
                'name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $subcategory = ProductSubcategory::create([
                'product_category_id' => $request->parent_category_id,
                'name' => $request->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subcategory created successfully',
                'data' => [
                    'subcategory' => $subcategory
                ]
            ], 201);
        }

        // Create category
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:product_categories,name',
            'has_hook' => 'nullable|boolean',
            'has_enamel' => 'nullable|boolean',
            'has_rodium' => 'nullable|boolean',
            'has_open_close' => 'nullable|boolean',
            'has_stone' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $category = ProductCategory::create([
            'name' => $request->name,
            'has_hook' => $request->boolean('has_hook'),
            'has_enamel' => $request->boolean('has_enamel'),
            'has_rodium' => $request->boolean('has_rodium'),
            'has_open_close' => $request->boolean('has_open_close'),
            'has_stone' => $request->boolean('has_stone'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => [
                'category' => $category
            ]
        ], 201);
    }

    /**
     * Create new product subcategory
     */
    public function createProductSubcategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_category_id' => 'required|exists:product_categories,id',
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $subcategory = ProductSubcategory::create([
            'product_category_id' => $request->product_category_id,
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subcategory created successfully',
            'data' => $subcategory
        ], 201);
    }

    /**
     * Create new product
     */
    public function createProduct(Request $request)
    {
        $productCode = $request->product_code;
        if (empty($productCode)) {
            $productCode = Product::generateProductCode();
        }

        $validator = Validator::make($request->all(), [
            'product_code' => 'nullable|string|max:255|unique:products,product_code',
            'product_name' => 'required|string|max:255',
            'bp_code' => 'nullable|string|exists:buyers,bp_code',
            'product_category_id' => 'required|exists:product_categories,id',
            'subcategory_id' => 'nullable|exists:product_subcategories,id',
            'type' => 'required|in:Piece,Pair',
            'open_close' => 'nullable|in:Open,Close',
            'size' => 'nullable|string|max:255',
            'length' => 'nullable|string|max:255',
            'weight_from' => 'nullable|numeric|min:0',
            'weight_to' => 'nullable|numeric|gte:weight_from',
            'hallmark' => 'nullable|string|max:255',
            'rodium'              => 'nullable|string|max:255',
            'hook'                => 'nullable|string|max:255',
            'stone'               => 'nullable|string|max:255',
            'enamel'              => 'nullable|string|max:255',
            'product_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::create([
            'product_code' => $productCode,
            'product_name' => $request->product_name,
            'bp_code' => $request->bp_code,
            'product_category_id' => $request->product_category_id,
            'product_subcategory_id' => $request->subcategory_id,
            'type' => $request->type,
            'open_close' => $request->open_close,
            'size' => $request->size,
            'length' => $request->length,
            'weight_from' => $request->weight_from,
            'weight_to' => $request->weight_to,
            'hallmark' => $request->hallmark,
            'rodium' => $request->rodium,
            'hook' => $request->hook,
            'stone' => $request->stone,
            'enamel' => $request->enamel,
            'created_by' => auth('sanctum')->id(),
        ]);

        // Handle images
        if ($request->hasFile('product_images')) {
            $images = $request->file('product_images');
            if (!is_array($images)) {
                $images = [$images];
            }

            $watermarkService = new ImageWatermarkService();
            $firstImage = true;
            foreach ($images as $image) {
                $path = $image->store('products', 'public');
                $watermarkedPath = $watermarkService->addWatermark($path);

                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $watermarkedPath,
                ]);

                // Set the first image as the main product_image for backward compatibility
                if ($firstImage) {
                    $product->update(['product_image' => $watermarkedPath]);
                    $firstImage = false;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    // ==================== DESIGN APIs ====================

    /**
     * Get all products for design approval
     */
    public function getDesignProducts(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $query = Product::with(['category', 'subcategory', 'images', 'creator'])
            ->where(function ($query) {
                $query->whereNull('design_status')
                    ->orWhere('design_status', 'Pending');
            });

        // Search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('product_name', 'LIKE', "%$searchTerm%")
                    ->orWhere('product_code', 'LIKE', "%$searchTerm%")
                    ->orWhere('design_code', 'LIKE', "%$searchTerm%");
            });
        }

        $products = $query->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Accept product design
     */
    public function acceptDesign(Request $request, Product $product)
    {
        // Use manual design code if provided, otherwise generate
        $designCode = $request->get('design_code') ?: $this->generateDesignCode();

        // Update product
        $product->update([
            'design_code' => $designCode,
            'design_status' => 'Accepted'
        ]);

        // Create Design record
        Design::updateOrCreate(
            ['product_id' => $product->id],
            [
                'design_code' => $designCode,
                'design_name' => $product->product_name,
                'image' => $product->images->first() ? $product->images->first()->path : null,
                'design_status' => 'Accepted' // Ensure design status is also updated
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Product accepted! Design code {$designCode} used.",
            'data' => [
                'product' => $product,
                'design_code' => $designCode
            ]
        ]);
    }

    /**
     * Reject product design
     */
    public function rejectDesign(Product $product)
    {
        $product->update([
            'design_status' => 'Rejected'
        ]);

        // Create or Update Design record to ensure it shows up in "Rejected" tab
        Design::updateOrCreate(
            ['product_id' => $product->id],
            [
                'design_code' => $product->design_code ?: 'REJ-' . time(),
                'design_name' => $product->product_name,
                'image' => $product->images->first() ? $product->images->first()->path : null,
                'design_status' => 'Rejected'
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Product rejected successfully',
            'data' => $product
        ]);
    }

    /**
     * Get all designs
     */
    public function getDesigns(Request $request)
    {
        $tab = $request->get('tab', 'all');
        // Align with SuperAdmin/DesignController logic: query Products with designated types
        // whereNotNull('type') matches the web controller's filter (excludes plain products)
        $query = Product::with(['category', 'subcategory', 'images', 'creator'])
            ->notFromFrozenAccounts()
            ->whereNotNull('type');

        // Tab Filtering Logic (Matching SuperAdmin/DesignController.php)
        if ($tab === 'pending') {
            // Web logic for pending: whereNotIn Accepted/Rejected
            $query->whereNotIn('design_status', ['Accepted', 'Rejected']);
        } elseif ($tab === 'accepted') {
            $query->where('design_status', 'Accepted');
        } elseif ($tab === 'rejected') {
            $query->where('design_status', 'Rejected');
        }

        // Additional filters from original API logic
        if ($request->filled('design_type')) {
            $query->where('type', $request->design_type);
        }

        if ($request->filled('category')) {
            $query->where('product_category_id', $request->category);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('product_name', 'like', '%' . $request->search . '%')
                    ->orWhere('product_code', 'like', '%' . $request->search . '%')
                    ->orWhere('design_code', 'like', '%' . $request->search . '%');
            });
        }

        $products = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'tab' => $tab,
            'data' => $products
        ]);
    }

    /**
     * Get a specific design
     */
    public function getDesign(Design $design)
    {
        $design->load(['product', 'creator']);
        return response()->json([
            'success' => true,
            'data' => $design
        ]);
    }

    /**
     * Create a new design
     */
    public function createDesign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'design_type' => 'required|string',
            'design_name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category' => 'nullable|string',
            'sub_category' => 'nullable|string',
            'weight' => 'nullable|numeric',
            'details' => 'nullable|string',
            'product_id' => 'nullable|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // Use manual design code if provided, otherwise generate
        $designCode = $request->get('design_code') ?: Design::generateDesignCode();

        $designData = [
            'design_code' => $designCode,
            'design_type' => $request->design_type,
            'design_name' => $request->design_name,
            'category' => $request->category,
            'sub_category' => $request->sub_category,
            'weight' => $request->weight,
            'details' => $request->details,
            'product_id' => $request->product_id,
            'design_status' => 'Pending',
        ];

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('designs', 'public');

            $watermarkService = new ImageWatermarkService();
            $watermarkedPath = $watermarkService->addWatermark($path);

            $designData['image'] = $watermarkedPath;
        }

        $design = Design::create($designData);

        return response()->json([
            'success' => true,
            'message' => 'Design created successfully',
            'data' => $design
        ], 201);
    }

    /**
     * Update a design
     */
    public function updateDesign(Request $request, Design $design)
    {
        $validator = Validator::make($request->all(), [
            'design_code' => 'sometimes|required|string|unique:designs,design_code,' . $design->id,
            'design_type' => 'sometimes|required|string',
            'design_name' => 'sometimes|required|string|max:255',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
            'category' => 'nullable|string',
            'sub_category' => 'nullable|string',
            'weight' => 'nullable|numeric',
            'details' => 'nullable|string',
            'product_id' => 'nullable|exists:products,id',
            'design_status' => 'sometimes|in:Pending,Accepted,Rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $designData = $request->only([
            'design_code',
            'design_type',
            'design_name',
            'category',
            'sub_category',
            'weight',
            'details',
            'product_id',
            'design_status'
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($design->image) {
                Storage::disk('public')->delete($design->image);
            }

            $file = $request->file('image');
            $path = $file->store('designs', 'public');

            $watermarkService = new ImageWatermarkService();
            $watermarkedPath = $watermarkService->addWatermark($path);

            $designData['image'] = $watermarkedPath;
        }

        $design->update($designData);

        return response()->json([
            'success' => true,
            'message' => 'Design updated successfully',
            'data' => $design
        ]);
    }

    // ==================== CATALOGUE APIs ====================

    /**
     * Get all accepted products (catalogue)
     */
    public function getCatalogueProducts(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $query = Product::with(['category', 'subcategory', 'images', 'creator'])
            ->where('design_status', 'Accepted')
            ->whereNotNull('design_code');

        // Search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('product_name', 'LIKE', "%$searchTerm%")
                    ->orWhere('product_code', 'LIKE', "%$searchTerm%")
                    ->orWhere('design_code', 'LIKE', "%$searchTerm%");
            });
        }

        $products = $query->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get a specific catalogue product details
     */
    public function getCatalogueProduct(Product $product)
    {
        if ($product->design_status !== 'Accepted') {
            return response()->json([
                'success' => false,
                'message' => 'Product is not in the catalogue'
            ], 404);
        }

        $product->load(['category', 'subcategory', 'images', 'creator']);
        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    // ==================== PINCODE APIs ====================

    /**
     * Fetch Pincode Details
     */
    private function fetchPincode($pincode)
    {
        // Validate pincode format (6 digits)
        if (!preg_match('/^\d{6}$/', $pincode)) {
            return response()->json(['Status' => 'Error', 'Message' => 'Invalid Pincode Format'], 400);
        }

        try {
            // Fetch data from http://www.postalpincode.in (HTTP)
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ])->get("http://www.postalpincode.in/api/pincode/{$pincode}");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['Status']) && $data['Status'] === 'Success') {
                    return $data;
                } else {
                    return response()->json(['Status' => 'Error', 'Message' => 'No records found'], 404);
                }
            } else {
                Log::error("Pincode API Error (postalpincode.in): " . $response->status());
                return response()->json(['Status' => 'Error', 'Message' => 'Unable to fetch data'], $response->status());
            }
        } catch (\Exception $e) {
            Log::error("Pincode Exception: " . $e->getMessage());
            return response()->json(['Status' => 'Error', 'Message' => 'Server Error'], 500);
        }
    }

    // ==================== KEY USER APIs ====================

    /**
     * Get all key users
     */
    public function getKeyUsers(Request $request)
    {
        $query = KeyUser::query();

        // Search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('full_name', 'LIKE', "%$searchTerm%")
                    ->orWhere('email_id', 'LIKE', "%$searchTerm%")
                    ->orWhere('user_code', 'LIKE', "%$searchTerm%");
            });
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $keyUsers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $keyUsers
        ]);
    }

    /**
     * Get a specific key user
     */
    public function getKeyUser(KeyUser $keyUser)
    {
        return response()->json([
            'success' => true,
            'data' => $keyUser
        ]);
    }

    /**
     * Create new key user
     */
    public function createKeyUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bp_code' => 'required|string|exists:buyers,bp_code',
            'full_name' => 'required|string|max:255',
            'email_id' => 'required|email|unique:key_users,email_id',
            'mobile_no' => 'required|string|max:15',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'nullable|boolean',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'aadhar_number' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'permissions' => 'nullable|array',
            'aadhar_photo' => 'required_with:aadhar_number|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // Build data explicitly — do NOT call Hash::make() here.
        // The KeyUser model has `'password' => 'hashed'` in its casts,
        // which automatically hashes the password on save.
        $data = [
            'user_code'    => KeyUser::generateUserCode(),
            'bp_code'      => $request->bp_code,
            'full_name'    => $request->full_name,
            'email_id'     => $request->email_id,
            'mobile_no'    => $request->mobile_no,
            'password'     => $request->password, // model cast will hash this
            'password_plain' => $request->password,
            'status'       => $request->input('status', 1),
            'city'         => $request->city,
            'state'        => $request->state,
            'country'      => $request->country,
            'pincode'      => $request->pincode,
            'aadhar_number' => $request->aadhar_number,
            'dob'          => $request->dob,
            'permissions'  => $request->input('permissions', []),
        ];

        // Handle file uploads
        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        if ($request->hasFile('aadhar_photo')) {
            $data['aadhar_photo'] = $request->file('aadhar_photo')->store('aadhar_photos', 'public');
        }

        $keyUser = KeyUser::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Key User created successfully',
            'data' => $keyUser
        ], 201);
    }

    /**
     * Update key user
     */
    public function updateKeyUser(Request $request, KeyUser $keyUser)
    {
        $validator = Validator::make($request->all(), [
            'bp_code'      => 'sometimes|required|string|exists:buyers,bp_code',
            'full_name'    => 'sometimes|required|string|max:255',
            'email_id'     => 'sometimes|required|email|unique:key_users,email_id,' . $keyUser->id,
            'mobile_no'    => 'sometimes|required|string|max:15',
            'status'       => 'sometimes|nullable|boolean',
            'password'     => 'nullable|string|min:8|confirmed',
            'city'         => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:100',
            'country'      => 'nullable|string|max:100',
            'pincode'      => 'nullable|string|max:10',
            'aadhar_number' => 'nullable|string|max:20',
            'dob'          => 'nullable|date',
            'permissions'  => 'nullable|array',
            'aadhar_photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'profile_picture' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // Build update data from only the fillable fields sent in the request.
        // Do NOT call Hash::make() — the KeyUser model casts `password` as `hashed`
        // and will hash it automatically on save.
        $fillable = [
            'bp_code',
            'full_name',
            'email_id',
            'mobile_no',
            'status',
            'city',
            'state',
            'country',
            'pincode',
            'aadhar_number',
            'dob',
            'permissions',
        ];

        $data = [];
        foreach ($fillable as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->input($field);
            }
        }

        // Only update password if a new one is provided (model cast will hash it)
        if ($request->filled('password')) {
            $data['password'] = $request->password;
            $data['password_plain'] = $request->password;
        }

        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        if ($request->hasFile('aadhar_photo')) {
            $data['aadhar_photo'] = $request->file('aadhar_photo')->store('aadhar_photos', 'public');
        }

        $keyUser->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Key User updated successfully',
            'data' => $keyUser->fresh()
        ]);
    }

    /**
     * Delete key user
     */
    public function deleteKeyUser(KeyUser $keyUser)
    {
        $keyUser->delete();

        return response()->json([
            'success' => true,
            'message' => 'Key User deleted successfully'
        ]);
    }

    // ==================== USER APIs ====================

    /**
     * Get all users
     */
    public function getUsers(Request $request)
    {
        $query = User::with(['createdBy', 'buyer']);

        // Search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('full_name', 'LIKE', "%$searchTerm%")
                    ->orWhere('email_id', 'LIKE', "%$searchTerm%")
                    ->orWhere('user_code', 'LIKE', "%$searchTerm%")
                    ->orWhere('city', 'LIKE', "%$searchTerm%");
            });
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Get a specific user
     */
    public function getUser(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Create new user
     */
    public function createUser(Request $request)
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
            'aadhar_photo' => 'required_with:aadhar_number|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

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
            'password_plain' => $request->password,
            'status' => $request->status,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'pincode' => $request->pincode,
            'aadhar_number' => $request->aadhar_number,
            'permissions' => $request->permissions ?? [],
            'created_by' => auth('sanctum')->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'bp_code' => 'sometimes|required|string|exists:buyers,bp_code',
            'full_name' => 'sometimes|required|string|max:255',
            'email_id' => 'sometimes|required|email|unique:users,email_id,' . $user->id,
            'mobile_no' => 'sometimes|required|string|max:15',
            'status' => 'sometimes|required|in:active,inactive',
            'password' => 'nullable|string|min:8|confirmed',
            'aadhar_photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->except(['password', 'password_confirmation']);

        // Explicit mapping for duplicate fields if they are present in request
        if ($request->has('full_name')) {
            $updateData['name'] = $request->full_name;
        }
        if ($request->has('email_id')) {
            $updateData['email'] = $request->email_id;
        }

        if ($request->has('password') && $request->password) {
            $updateData['password'] = Hash::make($request->password);
            $updateData['password_plain'] = $request->password;
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user)
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get frozen accounts
     */
    public function getFrozenAccounts(Request $request)
    {
        $accountType = $request->get('account_type'); // 'buyer', 'craftsman', 'admin', 'key_user', 'user'
        $search = $request->get('search');
        $ids = $request->get('ids');

        $data = [];

        // Define a mapping of types to models
        $typeMap = [
            'buyer'     => Buyer::class,
            'craftsman' => Craftman::class,
            'admin'     => ProcessOwner::class,
            'key_user'  => KeyUser::class,
            'user'      => User::class,
        ];

        if ($accountType && isset($typeMap[$accountType])) {
            $query = $typeMap[$accountType]::where('is_frozen', true);

            if ($ids) {
                $query->whereIn('id', (array)$ids);
            }

            if ($search) {
                $query->where(function ($q) use ($search, $accountType) {
                    if (in_array($accountType, ['admin', 'key_user', 'user', 'buyer', 'craftsman'])) {
                        $q->where('name', 'LIKE', "%$search%")
                            ->orWhere('email', 'LIKE', "%$search%");
                    }
                    if ($accountType === 'buyer' || $accountType === 'craftsman') {
                        $q->orWhere('business_name', 'LIKE', "%$search%");
                    }
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'updated_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Export/Print
            if ($request->get('export') === 'true' || $request->get('print') === 'true') {
                $results = $query->get();

                if ($request->get('export') === 'true') {
                    $headers = [
                        "Content-type" => "text/csv",
                        "Content-Disposition" => "attachment; filename=frozen_{$accountType}_export_" . date('Y-m-d') . ".csv",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    ];

                    $callback = function () use ($results, $accountType) {
                        $file = fopen('php://output', 'w');
                        fputcsv($file, ['ID', 'Name', 'Email', 'Type', 'Updated At']);

                        foreach ($results as $item) {
                            fputcsv($file, [
                                $item->id,
                                $item->name,
                                $item->email,
                                $accountType,
                                $item->updated_at,
                            ]);
                        }
                        fclose($file);
                    };

                    return response()->stream($callback, 200, $headers);
                }

                return response()->json(['success' => true, 'data' => [$accountType . 's' => $results]]);
            }

            $data[$accountType . 's'] = $query->get();
        } else {
            // Default "All" view (paginated or full for print)
            $limit = ($request->get('print') === 'true') ? 1000 : 10;

            $data = [
                'buyers' => Buyer::where('is_frozen', true)->limit($limit)->get(),
                'craftsmen' => Craftman::where('is_frozen', true)->limit($limit)->get(),
                'admins' => ProcessOwner::where('is_frozen', true)->limit($limit)->get(),
                'key_users' => KeyUser::where('is_frozen', true)->limit($limit)->get(),
                'users' => User::where('is_frozen', true)->limit($limit)->get(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Toggle freeze status
     */
    public function toggleFreezeAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'model_type' => 'required|in:buyer,craftsman,admin,key_user,user',
            'model_id' => 'required|integer',
            'action' => 'required|in:freeze,unfreeze'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $model = null;
        switch ($request->model_type) {
            case 'buyer':
                $model = Buyer::find($request->model_id);
                break;
            case 'craftsman':
                $model = Craftman::find($request->model_id);
                break;
            case 'admin':
                $model = ProcessOwner::find($request->model_id);
                break;
            case 'key_user':
                $model = KeyUser::find($request->model_id);
                break;
            case 'user':
                $model = User::find($request->model_id);
                break;
        }

        if (!$model) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found'
            ], 404);
        }

        $model->is_frozen = ($request->action === 'freeze');
        $model->save();

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->model_type) . ' account ' . $request->action . 'd successfully',
            'is_frozen' => $model->is_frozen
        ]);
    }

    /**
     * Get KYC Pending Lists
     */
    public function getKycPending(Request $request)
    {
        $accountType = $request->get('account_type'); // 'buyer' or 'craftsman'
        $search = $request->get('search');
        $ids = $request->get('ids');

        $pendingBuyersQuery = Buyer::with(['aadharDetails', 'panDetails', 'bankDetails'])
            ->where(function ($query) {
                $query->where('kyc_status', 'pending')
                    ->orWhere(function ($q) {
                        $q->whereNull('kyc_status')
                            ->where(function ($sub) {
                                $sub->doesntHave('aadharDetails')
                                    ->orDoesntHave('panDetails')
                                    ->orDoesntHave('bankDetails');
                            });
                    });
            });

        $pendingCraftsmenQuery = Craftman::with(['aadharDetails', 'panDetails', 'bankDetails'])
            ->where(function ($query) {
                $query->where('kyc_status', 'pending')
                    ->orWhere(function ($q) {
                        $q->whereNull('kyc_status')
                            ->where(function ($sub) {
                                $sub->doesntHave('aadharDetails')
                                    ->orDoesntHave('panDetails')
                                    ->orDoesntHave('bankDetails');
                            });
                    });
            });

        // Apply filters to both if type not specified, or just one
        if ($ids) {
            $pendingBuyersQuery->whereIn('id', (array)$ids);
            $pendingCraftsmenQuery->whereIn('id', (array)$ids);
        }

        if ($search) {
            $pendingBuyersQuery->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")->orWhere('business_name', 'LIKE', "%$search%");
            });
            $pendingCraftsmenQuery->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")->orWhere('business_name', 'LIKE', "%$search%");
            });
        }

        // Handle Export/Print
        if ($request->get('export') === 'true' || $request->get('print') === 'true') {
            $buyers = ($accountType === 'craftsman') ? collect() : $pendingBuyersQuery->get();
            $craftsmen = ($accountType === 'buyer') ? collect() : $pendingCraftsmenQuery->get();

            if ($request->get('export') === 'true') {
                $headers = [
                    "Content-type" => "text/csv",
                    "Content-Disposition" => "attachment; filename=kyc_pending_export_" . date('Y-m-d') . ".csv",
                    "Pragma" => "no-cache",
                    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                    "Expires" => "0"
                ];

                $callback = function () use ($buyers, $craftsmen) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, ['ID', 'Type', 'Name', 'Business Name', 'Status', 'Created At']);

                    foreach ($buyers as $buyer) {
                        fputcsv($file, [$buyer->id, 'Buyer', $buyer->name, $buyer->business_name, $buyer->kyc_status ?? 'Pending', $buyer->created_at]);
                    }
                    foreach ($craftsmen as $craftsman) {
                        fputcsv($file, [$craftsman->id, 'Craftsman', $craftsman->name, $craftsman->business_name, $craftsman->kyc_status ?? 'Pending', $craftsman->created_at]);
                    }
                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'pending_buyers' => $buyers,
                    'pending_craftsmen' => $craftsmen
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'pending_buyers' => ($accountType === 'craftsman') ? [] : $pendingBuyersQuery->get(),
                'pending_craftsmen' => ($accountType === 'buyer') ? [] : $pendingCraftsmenQuery->get()
            ]
        ]);
    }

    /**
     * Approve/Unlock Buyer KYC
     */
    public function manageBuyerKyc(Request $request, Buyer $buyer)
    {
        $request->validate([
            'action' => 'required|in:approve,unlock'
        ]);

        if ($request->action === 'approve') {
            $buyer->update(['kyc_status' => 'approved']);
            $message = 'Buyer KYC approved';
        } else {
            $buyer->update(['kyc_status' => 'pending']);
            $message = 'Buyer profile unlocked';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $buyer
        ]);
    }

    /**
     * Approve/Unlock Craftsman KYC
     */
    public function manageCraftsmanKyc(Request $request, Craftman $craftsman)
    {
        $request->validate([
            'action' => 'required|in:approve,unlock'
        ]);

        if ($request->action === 'approve') {
            $craftsman->update(['kyc_status' => 'approved']);
            $message = 'Craftsman KYC approved';
        } else {
            $craftsman->update(['kyc_status' => 'pending']);
            $message = 'Craftsman profile unlocked';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $craftsman
        ]);
    }

    // ==================== MISSING CRUD APIs ====================

    /**
     * Update Work Order
     */
    public function updateWorkOrder(Request $request, WorkOrder $workOrder)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:1',
            'product_name' => 'nullable|string|max:255',
            'due_date' => 'nullable|date',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'product_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle image based on product code lookup or direct upload
        $productImage = $workOrder->product_image; // Preserve existing image

        // Handle explicit removal of primary image
        if ($request->has('remove_product_image') && $request->remove_product_image == 1) {
            if ($productImage && file_exists(public_path($productImage))) {
                @unlink(public_path($productImage));
            }
            $productImage = null;
        }

        // Handle removal of specific images from gallery
        if ($request->has('remove_images')) {
            $removeImages = is_array($request->remove_images) ? $request->remove_images : json_decode($request->remove_images, true);
            if (!empty($removeImages)) {
                foreach ($removeImages as $imageId) {
                    $image = WorkOrderImage::find($imageId);
                    if ($image && $image->work_order_id == $workOrder->id) {
                        if (file_exists(public_path($image->image_path))) {
                            @unlink(public_path($image->image_path));
                        }
                        $image->delete();
                    }
                }
            }
        }

        if (!empty($request->product_code) && !$request->hasFile('product_image') && !$request->has('remove_product_image')) {
            // If product code is provided and no new image uploaded, try to copy from existing product
            $existingProduct = Product::with('images')
                ->where('product_code', $request->product_code)
                ->orWhere('design_code', $request->product_code)
                ->first();

            if ($existingProduct && $existingProduct->images->count() > 0) {
                // Copy the first image from the existing product
                $existingImage = $existingProduct->images->first();
                $sourceImagePath = storage_path('app/public/' . $existingImage->path);

                if (file_exists($sourceImagePath)) {
                    // Copy image to work-orders directory with new name
                    $imageName = time() . '_copied_from_product_' . basename($existingImage->path);
                    $destinationPath = public_path('images/work-orders/' . $imageName);

                    // Make sure the directory exists
                    if (!file_exists(dirname($destinationPath))) {
                        mkdir(dirname($destinationPath), 0755, true);
                    }

                    copy($sourceImagePath, $destinationPath);
                    $productImage = 'images/work-orders/' . $imageName;

                    // Apply watermark to the copied image
                    $watermarkService = new ImageWatermarkService();
                    $watermarkService->addWatermark($productImage, true);
                }
            }
        } elseif ($request->hasFile('product_image')) {
            // Handle direct image upload (this overrides any copied image)
            $image = $request->file('product_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/work-orders'), $imageName);
            $productImage = 'images/work-orders/' . $imageName;

            $watermarkService = new ImageWatermarkService();
            $watermarkService->addWatermark($productImage, true);
        }

        // Resolve Category and Subcategory
        $categoryId = $request->product_category_id;
        $categoryName = null;
        $subcategoryId = $request->subcategory_id;
        $subcategoryName = null;

        if ($categoryId) {
            $cat = ProductCategory::find($categoryId);
            $categoryName = $cat ? $cat->name : $workOrder->product_category;
        } else {
            $categoryName = $workOrder->product_category;
        }

        if ($subcategoryId) {
            $sub = ProductSubcategory::find($subcategoryId);
            $subcategoryName = $sub ? $sub->name : $workOrder->subcategory;
        } else {
            $subcategoryName = $workOrder->subcategory;
        }

        $updateData = [];

        // Fields to update only if present in request
        $fields = [
            'bp_code',
            'customer_name',
            'reference_no',
            'due_date',
            'quantity',
            'type',
            'open_close',
            'weight_from',
            'weight_to',
            'hallmark',
            'rodium',
            'hook',
            'size',
            'stone',
            'enamel',
            'length',
            'product_code',
            'relabel_code',
            'product_name',
            'craftsman_due_date',
            'narration_craftsman',
            'narration_admin'
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $value = $request->input($field);
                // Only update if value is not null or if explicitly allowed
                if ($value !== null) {
                    $updateData[$field] = $value;
                }
            }
        }

        // Handle Image separately
        if ($productImage !== null) {
            $updateData['product_image'] = $productImage;
        }

        // Handle Category/Subcategory separately
        if ($categoryId || $request->has('product_category_id')) {
            if ($categoryName !== null) $updateData['product_category'] = $categoryName;
            $updateData['product_category_id'] = $categoryId;
        }

        if ($subcategoryId || $request->has('subcategory_id')) {
            if ($subcategoryName !== null) $updateData['subcategory'] = $subcategoryName;
            $updateData['subcategory_id'] = $subcategoryId;
        }

        if (!empty($updateData)) {
            $workOrder->update($updateData);
        }

        // REFRESH the model to get the latest values from DB for the response
        $workOrder->refresh();

        // Handle new multiple images upload
        if ($request->hasFile('product_images')) {
            $watermarkService = new ImageWatermarkService();
            foreach ($request->file('product_images') as $index => $file) {
                $imageName = time() . '_multi_' . $index . '_' . $file->getClientOriginalName();
                $file->move(public_path('images/work-orders'), $imageName);
                $imagePath = 'images/work-orders/' . $imageName;

                $watermarkService->addWatermark($imagePath, true);

                WorkOrderImage::create([
                    'work_order_id' => $workOrder->id,
                    'image_path' => $imagePath,
                ]);

                // If no primary image exists, use this one
                if (!$workOrder->product_image) {
                    $workOrder->update(['product_image' => $imagePath]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Work Order updated successfully',
            'data' => $workOrder
        ]);
    }

    /**
     * Delete Work Order
     */
    public function deleteWorkOrder(WorkOrder $workOrder)
    {
        $workOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Work Order deleted successfully'
        ]);
    }




    /**
     * Update Product
     */
    public function updateProduct(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'sometimes|required|string|max:255',
            'product_category_id' => 'sometimes|required|exists:product_categories,id',
            'subcategory_id' => 'sometimes|nullable|exists:product_subcategories,id',
            'type' => 'sometimes|required|in:Piece,Pair',
            'product_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'removed_images' => 'nullable|array',
            'removed_images.*' => 'exists:product_images,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // Update basic fields
        $fields = [
            'product_name',
            'bp_code',
            'product_category_id',
            'product_subcategory_id',
            'type',
            'open_close',
            'size',
            'length',
            'weight_from',
            'weight_to',
            'hallmark',
            'rodium',
            'hook',
            'stone',
            'enamel'
        ];

        $updateData = [];
        foreach ($fields as $field) {
            $requestField = ($field == 'product_subcategory_id') ? 'subcategory_id' : $field;
            if ($request->has($requestField)) {
                $value = $request->get($requestField);
                if ($value !== null) {
                    $updateData[$field] = $value;
                }
            }
        }

        if (!empty($updateData)) {
            $product->update($updateData);
        }

        // Handle removed images
        if ($request->has('removed_images')) {
            foreach ($request->removed_images as $imageId) {
                $image = ProductImage::find($imageId);
                if ($image && $image->product_id == $product->id) {
                    // Delete from storage
                    Storage::disk('public')->delete($image->path);
                    $image->delete();
                }
            }
        }

        // Handle new image uploads
        if ($request->hasFile('product_images')) {
            $images = $request->file('product_images');
            if (!is_array($images)) {
                $images = [$images];
            }

            $watermarkService = new ImageWatermarkService();
            foreach ($images as $imageFile) {
                $path = $imageFile->store('products', 'public');
                $watermarkedPath = $watermarkService->addWatermark($path);

                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $watermarkedPath,
                ]);
            }
        }

        // Sync main product_image field with the first available image
        $firstImage = $product->images()->first();
        $product->update([
            'product_image' => $firstImage ? $firstImage->path : null
        ]);

        $product->load(['category', 'subcategory', 'images']);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    /**
     * Delete Product
     */
    public function deleteProduct(Product $product)
    {
        // Delete associated logic (images, designs) if necessary
        // For now, standard delete
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Generate admin user code
     */
    private function generateAdminUserCode()
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

    /**
     * Get product details by product_code or design_code (for work order creation)
     */
    public function getProductDetails(Request $request)
    {
        $code = $request->query('product_code');

        if (!$code) {
            return response()->json(['success' => false, 'message' => 'Code is required']);
        }

        $product = Product::with('images')
            ->where(function ($query) use ($code) {
                $query->where('product_code', $code)
                    ->orWhere('design_code', $code);
            })
            ->where('design_status', 'Accepted')  // Only accepted designs
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found or not accepted']);
        }

        // Handle image URL construction
        $firstImage = null;
        if ($product->images->first()) {
            $imagePath = $product->images->first()->path;

            if (!empty($imagePath)) {
                if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                    $firstImage = $imagePath;
                } elseif (strpos($imagePath, 'images/') === 0 || strpos($imagePath, 'storage/') === 0) {
                    $firstImage = asset($imagePath);
                } else {
                    $firstImage = asset('storage/' . $imagePath);
                }
            }
        }

        return response()->json([
            'success' => true,
            'product' => [
                'product_name' => $product->product_name,
                'design_code' => $product->design_code,
                'product_code' => $product->product_code,
                'product_image_url' => $firstImage,
                'product_category_id' => $product->product_category_id,
                'subcategory_id' => $product->product_subcategory_id,
                'type' => $product->type,
                'open_close' => $product->open_close,
                'hallmark' => $product->hallmark,
                'rodium' => $product->rodium,
                'hook' => $product->hook,
                'size' => $product->size,
                'stone' => $product->stone,
                'enamel' => $product->enamel,
                'length' => $product->length,
                'weight_from' => $product->weight_from,
                'weight_to' => $product->weight_to,
                'relabel_code' => $product->relabel_code,
            ]
        ]);
    }

    /**
     * Generate unique product code for work orders
     */
    private function generateUniqueProductCode()
    {
        $latestOrder = WorkOrder::where('product_code', 'LIKE', 'OO%')
            ->orderBy('product_code', 'desc')
            ->first();

        if (!$latestOrder) {
            return 'OO001';
        }

        $numericPart = preg_replace('/[^0-9]/', '', $latestOrder->product_code);
        $nextNumber = intval($numericPart) + 1;

        return 'OO' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique design code
     */
    private function generateDesignCode()
    {
        $existingCodes = Product::whereNotNull('design_code')
            ->where('design_code', 'LIKE', 'DSG%')
            ->pluck('design_code')
            ->toArray();

        $existingDesignCodes = Design::whereNotNull('design_code')
            ->where('design_code', 'LIKE', 'DSG%')
            ->pluck('design_code')
            ->toArray();

        $allCodes = array_merge($existingCodes, $existingDesignCodes);

        $counter = 1;
        while (true) {
            $newCode = 'DSG' . str_pad($counter, 4, '0', STR_PAD_LEFT);
            if (!in_array($newCode, $allCodes)) {
                return $newCode;
            }
            $counter++;
        }
    }
    private function fetchPincodeData($pincode)
    {
        // Validate pincode format (6 digits)
        if (!preg_match('/^\d{6}$/', $pincode)) {
            return null;
        }

        try {
            // Fetch data from http://www.postalpincode.in (HTTP)
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ])->get("http://www.postalpincode.in/api/pincode/{$pincode}");

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error("Pincode API Error (postalpincode.in): " . $response->status());
                return null;
            }
        } catch (\Exception $e) {
            Log::error("Pincode Exception: " . $e->getMessage());
            return null;
        }
    }
}
