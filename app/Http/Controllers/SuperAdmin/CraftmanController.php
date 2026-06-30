<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Craftman;
use App\Models\CraftmanAadharDetail;
use App\Models\CraftmanPanDetail;
use App\Models\CraftmanBankDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CraftmanController extends Controller
{
    /**
     * Display a listing of the craftmen.
     */
    public function index(Request $request)
    {
        // Check if export is requested
        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportCraftmen($request);
        }

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

        // Filtering functionality
        if ($request->filled('craftman_code')) {
            $query->where('craftman_code', $request->craftman_code);
        }

        if ($request->filled('business_name')) {
            $query->where('business_name', 'LIKE', '%' . $request->business_name . '%');
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        // Sorting functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validate sort order
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $allowedSortColumns = ['craftman_code', 'business_name', 'name', 'mobile', 'email', 'city', 'state', 'created_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $craftmen = $query->paginate(15)->appends(request()->query());

        return view('super-admin.business-partner.craftman', compact('craftmen'));
    }

    /**
     * Show the form for creating a new craftman.
     */
    public function create()
    {
        return view('super-admin.business-partner.create-craftman');
    }

    /**
     * Store a newly created craftman in storage.
     */
    public function store(Request $request)
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
            // At least one Aadhar is required (both name and number)
            'aadhar_name' => 'nullable|array|min:1',
            'aadhar_name.*' => 'nullable|string|max:255',
            'aadhar_number' => 'nullable|array|min:1',
            'aadhar_number.*' => 'nullable|string|max:20|unique:craftmen,aadhar_no',
            // At least one PAN is required
            'pan_number' => 'nullable|array|min:1',
            'pan_number.*' => 'nullable|string|max:20|unique:craftmen,pan_no',
            'gst_no' => 'nullable|string|max:20|unique:craftmen',
            'cin_no' => 'nullable|string|max:21',
            'cin_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Generate Craftman code - REMOVED manual entry
        // $craftmanCode = Craftman::generateCraftmanCode();

        $craftman = Craftman::create([
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
            'city' => $request->city,
            'state' => $request->state,
            'map_location' => $request->map_location,
            'location_guide' => $request->location_guide,
            // KYC Fields (we'll store the first one in the main table for backward compatibility)
            'bis_no' => $request->bis_no,
            'gst_no' => $request->gst_no,
            'msme_no' => $request->msme_no,
            'pan_no' => $request->pan_number[0] ?? null,
            'tan_no' => $request->tan_no,
            'cin_no' => $request->cin_no,
            'aadhar_no' => $request->aadhar_number[0] ?? null,
            'aadhar_name' => $request->aadhar_name[0] ?? null,
            'bank_name' => $request->bank_name[0] ?? null,
            'account_name' => $request->account_holder_name[0] ?? null,
            'account_no' => $request->account_number[0] ?? null,
            'ifsc_code' => $request->ifsc_code[0] ?? null,
            'branch' => $request->branch[0] ?? null,
            'bank_city' => $request->bank_city[0] ?? null,
            'bank_state' => $request->bank_state[0] ?? null,
            'note' => $request->note,
            'permissions' => array_unique(array_merge($request->permissions ?? [], ['dashboard'])),
            'password' => $request->password ? bcrypt($request->password) : bcrypt('password'),
            'password_plain' => $request->password ?? 'password',
        ]);

        // Handle file uploads for attachments
        if ($request->hasFile('gst_attachment')) {
            $file = $request->file('gst_attachment');
            $filename = time() . '_gst_' . $file->getClientOriginalName();
            $file->storeAs('gst', $filename);
            $craftman->gst_attachment = 'gst/' . $filename;
        }

        if ($request->hasFile('bis_attachment')) {
            $file = $request->file('bis_attachment');
            $filename = time() . '_bis_' . $file->getClientOriginalName();
            $file->storeAs('bis', $filename);
            $craftman->bis_attachment = 'bis/' . $filename;
        }

        if ($request->hasFile('msme_attachment')) {
            $file = $request->file('msme_attachment');
            $filename = time() . '_msme_' . $file->getClientOriginalName();
            $file->storeAs('msme', $filename);
            $craftman->msme_attachment = 'msme/' . $filename;
        }

        if ($request->hasFile('tan_attachment')) {
            $file = $request->file('tan_attachment');
            $filename = time() . '_tan_' . $file->getClientOriginalName();
            $file->storeAs('tan', $filename);
            $craftman->tan_attachment = 'tan/' . $filename;
        }

        if ($request->hasFile('cin_attachment')) {
            $file = $request->file('cin_attachment');
            $filename = time() . '_cin_' . $file->getClientOriginalName();
            $file->storeAs('cin', $filename);
            $craftman->cin_attachment = 'cin/' . $filename;
        }

        $craftman->cin_no = $request->cin_no;
        $craftman->save();

        // Handle multiple Aadhar details
        if ($request->has('aadhar_name') && is_array($request->aadhar_name)) {
            foreach ($request->aadhar_name as $index => $aadharName) {
                if (!empty($aadharName) && !empty($request->aadhar_number[$index])) {
                    $aadharData = [
                        'craftman_id' => $craftman->id,
                        'aadhar_name' => $aadharName,
                        'aadhar_number' => $request->aadhar_number[$index],
                    ];

                    // Handle file upload
                    if ($request->hasFile("aadhar_image.$index")) {
                        $file = $request->file("aadhar_image.$index");
                        $filename = time() . '_' . $index . '_' . $file->getClientOriginalName();
                        $file->storeAs('craftman-aadhar', $filename);
                        $aadharData['aadhar_image'] = 'craftman-aadhar/' . $filename;
                    }

                    \App\Models\CraftmanAadharDetail::create($aadharData);
                }
            }
        }

        // Handle multiple PAN details
        if ($request->has('pan_number') && is_array($request->pan_number)) {
            foreach ($request->pan_number as $index => $panNumber) {
                if (!empty($panNumber)) {
                    $panData = [
                        'craftman_id' => $craftman->id,
                        'pan_number' => $panNumber,
                    ];

                    // Handle file upload
                    if ($request->hasFile("pan_image.$index")) {
                        $file = $request->file("pan_image.$index");
                        $filename = time() . '_pan_' . $index . '_' . $file->getClientOriginalName();
                        $file->storeAs('craftman-pan', $filename);
                        $panData['pan_image'] = 'craftman-pan/' . $filename;
                    }

                    \App\Models\CraftmanPanDetail::create($panData);
                }
            }
        }

        // Handle multiple Bank details
        if ($request->has('bank_name') && is_array($request->bank_name)) {
            foreach ($request->bank_name as $index => $bankName) {
                if (!empty($bankName) && !empty($request->account_holder_name[$index]) && !empty($request->account_number[$index])) {
                    $bankData = [
                        'craftman_id' => $craftman->id,
                        'bank_name' => $bankName,
                        'account_holder_name' => $request->account_holder_name[$index],
                        'account_number' => $request->account_number[$index],
                        'ifsc_code' => $request->ifsc_code[$index] ?? null,
                        'branch' => $request->branch[$index] ?? null,
                        'bank_city' => $request->bank_city[$index] ?? null,
                        'bank_state' => $request->bank_state[$index] ?? null,
                    ];

                    // Handle file upload
                    if ($request->hasFile("passbook_image.$index")) {
                        $file = $request->file("passbook_image.$index");
                        $filename = time() . '_passbook_' . $index . '_' . $file->getClientOriginalName();
                        $file->storeAs('craftman-passbook', $filename);
                        $bankData['passbook_image'] = 'craftman-passbook/' . $filename;
                    }

                    \App\Models\CraftmanBankDetail::create($bankData);
                }
            }
        }

        // Handle multiple Workers
        if ($request->has('worker_name') && is_array($request->worker_name)) {
            foreach ($request->worker_name as $index => $workerName) {
                if (!empty($workerName)) {
                    $workerData = [
                        'craftman_id' => $craftman->id,
                        'worker_name' => $workerName,
                        'worker_number' => $request->worker_number[$index] ?? null,
                    ];

                    // Handle file upload
                    if ($request->hasFile("worker_image.$index")) {
                        $file = $request->file("worker_image.$index");
                        $filename = time() . '_worker_' . $index . '_' . $file->getClientOriginalName();
                        $file->storeAs('craftman-workers', $filename);
                        $workerData['worker_image'] = 'craftman-workers/' . $filename;
                    }

                    \App\Models\CraftmanWorker::create($workerData);
                }
            }
        }

        return redirect()->route('super-admin.business-partner.craftman')
            ->with('success', 'Craftman created successfully with multiple KYC details!');
    }

    /**
     * Display the specified craftman.
     */
    public function show(Craftman $craftman)
    {
        return view('super-admin.business-partner.show-craftman', compact('craftman'));
    }

    /**
     * Show the form for editing the specified craftman.
     */
    public function edit(Craftman $craftman)
    {
        $craftman->load(['aadharDetails', 'panDetails', 'bankDetails']);
        return view('super-admin.business-partner.edit-craftman', compact('craftman'));
    }

    /**
     * Update the specified craftman in storage.
     */
    public function update(Request $request, Craftman $craftman)
    {
        $validator = Validator::make($request->all(), [
            'craftman_code' => 'required|string|unique:craftmen,craftman_code,' . $craftman->id,
            'dear' => 'nullable|string|unique:craftmen,dear,' . $craftman->id,
            'business_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15',
            'email' => 'required|email|max:255|unique:craftmen,email,' . $craftman->id,
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:6|confirmed',
            // At least one Aadhar is required (both name and number)
            'aadhar_name' => 'nullable|array|min:1',
            'aadhar_name.*' => 'nullable|string|max:255',
            'aadhar_number' => 'nullable|array|min:1',
            'aadhar_number.*' => 'nullable|string|max:20|unique:craftmen,aadhar_no,' . $craftman->id,
            // At least one PAN is required
            'pan_number' => 'nullable|array|min:1',
            'pan_number.*' => 'nullable|string|max:20|unique:craftmen,pan_no,' . $craftman->id,
            'gst_no' => 'nullable|string|max:20|unique:craftmen,gst_no,' . $craftman->id,
            'cin_no' => 'nullable|string|max:21',
            'cin_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $craftman->update([
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
            'city' => $request->city,
            'state' => $request->state,
            'map_location' => $request->map_location,
            'location_guide' => $request->location_guide,
            // KYC Fields (we'll store the first one in the main table for backward compatibility)
            'bis_no' => $request->bis_no,
            'gst_no' => $request->gst_no,
            'msme_no' => $request->msme_no,
            'pan_no' => $request->pan_number[0] ?? null,
            'tan_no' => $request->tan_no,
            'cin_no' => $request->cin_no,
            'aadhar_no' => $request->aadhar_number[0] ?? null,
            'aadhar_name' => $request->aadhar_name[0] ?? null,
            'bank_name' => $request->bank_name[0] ?? null,
            'account_name' => $request->account_holder_name[0] ?? null,
            'account_no' => $request->account_number[0] ?? null,
            'ifsc_code' => $request->ifsc_code[0] ?? null,
            'branch' => $request->branch[0] ?? null,
            'bank_city' => $request->bank_city[0] ?? null,
            'bank_state' => $request->bank_state[0] ?? null,
            'note' => $request->note,
            'permissions' => array_unique(array_merge($request->permissions ?? [], ['dashboard'])),
            'password' => $request->password ? bcrypt($request->password) : $craftman->password,
            'password_plain' => $request->password ?: $craftman->password_plain,
        ]);

        // Handle file uploads for attachments (only if new files are uploaded)
        if ($request->hasFile('gst_attachment')) {
            // Delete old file if exists
            if ($craftman->gst_attachment && Storage::exists('public/' . $craftman->gst_attachment)) {
                Storage::delete('public/' . $craftman->gst_attachment);
            }
            $file = $request->file('gst_attachment');
            $filename = time() . '_gst_' . $file->getClientOriginalName();
            $file->storeAs('gst', $filename);
            $craftman->gst_attachment = 'gst/' . $filename;
        }

        if ($request->hasFile('bis_attachment')) {
            if ($craftman->bis_attachment && Storage::exists('public/' . $craftman->bis_attachment)) {
                Storage::delete('public/' . $craftman->bis_attachment);
            }
            $file = $request->file('bis_attachment');
            $filename = time() . '_bis_' . $file->getClientOriginalName();
            $file->storeAs('bis', $filename);
            $craftman->bis_attachment = 'bis/' . $filename;
        }

        if ($request->hasFile('msme_attachment')) {
            if ($craftman->msme_attachment && Storage::exists('public/' . $craftman->msme_attachment)) {
                Storage::delete('public/' . $craftman->msme_attachment);
            }
            $file = $request->file('msme_attachment');
            $filename = time() . '_msme_' . $file->getClientOriginalName();
            $file->storeAs('msme', $filename);
            $craftman->msme_attachment = 'msme/' . $filename;
        }

        if ($request->hasFile('tan_attachment')) {
            if ($craftman->tan_attachment && Storage::exists('public/' . $craftman->tan_attachment)) {
                Storage::delete('public/' . $craftman->tan_attachment);
            }
            $file = $request->file('tan_attachment');
            $filename = time() . '_tan_' . $file->getClientOriginalName();
            $file->storeAs('tan', $filename);
            $craftman->tan_attachment = 'tan/' . $filename;
        }

        if ($request->hasFile('cin_attachment')) {
            if ($craftman->cin_attachment && Storage::exists('public/' . $craftman->cin_attachment)) {
                Storage::delete('public/' . $craftman->cin_attachment);
            }
            $file = $request->file('cin_attachment');
            $filename = time() . '_cin_' . $file->getClientOriginalName();
            $file->storeAs('cin', $filename);
            $craftman->cin_attachment = 'cin/' . $filename;
        }

        $craftman->save();

        // Delete existing related records
        $craftman->aadharDetails()->delete();
        $craftman->panDetails()->delete();
        $craftman->bankDetails()->delete();
        $craftman->workers()->delete();

        // Handle multiple Aadhar details
        if ($request->has('aadhar_name') && is_array($request->aadhar_name)) {
            foreach ($request->aadhar_name as $index => $aadharName) {
                if (!empty($aadharName) && !empty($request->aadhar_number[$index])) {
                    $aadharData = [
                        'craftman_id' => $craftman->id,
                        'aadhar_name' => $aadharName,
                        'aadhar_number' => $request->aadhar_number[$index],
                    ];

                    // Handle file upload
                    if ($request->hasFile("aadhar_image.$index")) {
                        $file = $request->file("aadhar_image.$index");
                        $filename = time() . '_' . $index . '_' . $file->getClientOriginalName();
                        $file->storeAs('craftman-aadhar', $filename);
                        $aadharData['aadhar_image'] = 'craftman-aadhar/' . $filename;
                    }

                    CraftmanAadharDetail::create($aadharData);
                }
            }
        }

        // Handle multiple PAN details
        if ($request->has('pan_number') && is_array($request->pan_number)) {
            foreach ($request->pan_number as $index => $panNumber) {
                if (!empty($panNumber)) {
                    $panData = [
                        'craftman_id' => $craftman->id,
                        'pan_number' => $panNumber,
                    ];

                    // Handle file upload
                    if ($request->hasFile("pan_image.$index")) {
                        $file = $request->file("pan_image.$index");
                        $filename = time() . '_pan_' . $index . '_' . $file->getClientOriginalName();
                        $file->storeAs('craftman-pan', $filename);
                        $panData['pan_image'] = 'craftman-pan/' . $filename;
                    }

                    CraftmanPanDetail::create($panData);
                }
            }
        }

        // Handle multiple Bank details
        if ($request->has('bank_name') && is_array($request->bank_name)) {
            foreach ($request->bank_name as $index => $bankName) {
                if (!empty($bankName) && !empty($request->account_holder_name[$index]) && !empty($request->account_number[$index])) {
                    $bankData = [
                        'craftman_id' => $craftman->id,
                        'bank_name' => $bankName,
                        'account_holder_name' => $request->account_holder_name[$index],
                        'account_number' => $request->account_number[$index],
                        'ifsc_code' => $request->ifsc_code[$index] ?? null,
                        'branch' => $request->branch[$index] ?? null,
                        'bank_city' => $request->bank_city[$index] ?? null,
                        'bank_state' => $request->bank_state[$index] ?? null,
                    ];

                    // Handle file upload
                    if ($request->hasFile("passbook_image.$index")) {
                        $file = $request->file("passbook_image.$index");
                        $filename = time() . '_passbook_' . $index . '_' . $file->getClientOriginalName();
                        $file->storeAs('craftman-passbook', $filename);
                        $bankData['passbook_image'] = 'craftman-passbook/' . $filename;
                    }

                    CraftmanBankDetail::create($bankData);
                }
            }
        }

        // Handle multiple Workers
        if ($request->has('worker_name') && is_array($request->worker_name)) {
            foreach ($request->worker_name as $index => $workerName) {
                if (!empty($workerName)) {
                    $workerData = [
                        'craftman_id' => $craftman->id,
                        'worker_name' => $workerName,
                        'worker_number' => $request->worker_number[$index] ?? null,
                    ];

                    // Handle file upload
                    if ($request->hasFile("worker_image.$index")) {
                        $file = $request->file("worker_image.$index");
                        $filename = time() . '_worker_' . $index . '_' . $file->getClientOriginalName();
                        $file->storeAs('public/craftman-workers', $filename);
                        $workerData['worker_image'] = 'craftman-workers/' . $filename;
                    }

                    \App\Models\CraftmanWorker::create($workerData);
                }
            }
        }

        return redirect()->route('super-admin.business-partner.craftman')
            ->with('success', 'Craftman updated successfully with multiple KYC details!');
    }

    /**
     * Export craftmen to Excel
     */
    private function exportCraftmen(Request $request)
    {
        $query = Craftman::query();

        // Apply search if present
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

        // Apply filters
        if ($request->filled('craftman_code')) {
            $query->where('craftman_code', $request->craftman_code);
        }

        if ($request->filled('business_name')) {
            $query->where('business_name', 'LIKE', '%' . $request->business_name . '%');
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validate sort order
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $allowedSortColumns = ['craftman_code', 'business_name', 'name', 'mobile', 'email', 'city', 'state', 'created_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, $sortOrder);

        $craftmen = $query->get();

        // Create CSV content
        $csvContent = "Craftman Code,Business Name,Contact Person,Mobile,Email,City,State,Created At\n";

        foreach ($craftmen as $craftman) {
            $csvContent .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s\n",
                $craftman->craftman_code,
                $craftman->business_name,
                $craftman->name,
                $craftman->mobile,
                $craftman->email,
                $craftman->city ?? 'N/A',
                $craftman->state ?? 'N/A',
                $craftman->created_at->format('Y-m-d H:i:s')
            );
        }

        // Return CSV download
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="craftmen_export.csv"');
    }

    /**
     * Remove the specified craftman from storage.
     */
    public function destroy(Craftman $craftman)
    {
        $craftman->delete();

        return redirect()->route('super-admin.business-partner.craftman')
            ->with('success', 'Craftman deleted successfully!');
    }

    /**
     * Approve the craftman's KYC.
     */
    public function approve(Craftman $craftman)
    {
        $craftman->update(['kyc_status' => 'approved']);

        return redirect()->back()
            ->with('success', 'Craftman KYC has been approved. Profile is now locked for the craftsman.');
    }

    /**
     * Unlock the craftman's profile for editing.
     */
    public function unlock(Craftman $craftman)
    {
        $craftman->update(['kyc_status' => 'pending']);

        return redirect()->back()
            ->with('success', 'Craftman profile has been unlocked for editing.');
    }

    public function printSelected(Request $request)
    {
        $selectedIds = $request->input('selected_craftsmen', []);

        if (empty($selectedIds)) {
            return redirect()->back()->with('error', 'No craftsmen selected for printing.');
        }

        $craftmen = Craftman::whereIn('id', $selectedIds)->get();

        return view('super-admin.business-partner.print-selected-craftman', compact('craftmen'));
    }
}
