<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Craftman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BuyerController extends Controller
{
    /**
     * Display business partner overview page
     */
    public function businessPartnerIndex(Request $request)
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

        $buyers = $buyersQuery->get();
        $craftmen = $craftmenQuery->get();

        return view('super-admin.business-partner.index', compact('buyers', 'craftmen'));
    }

    /**
     * Display a listing of the buyers.
     */
    public function index(Request $request)
    {
        // Check if export is requested
        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportBuyers($request);
        }

        $query = Buyer::query();

        // Search functionality
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

        // Filtering functionality
        if ($request->filled('bp_code')) {
            $query->where('bp_code', $request->bp_code);
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

        $allowedSortColumns = ['bp_code', 'business_name', 'name', 'mobile', 'email', 'city', 'state', 'created_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $buyers = $query->paginate(15)->appends(request()->query());

        return view('super-admin.business-partner.buyer', compact('buyers'));
    }

    /**
     * Show the form for creating a new buyer.
     */
    public function create()
    {
        return view('super-admin.business-partner.create');
    }

    /**
     * Store a newly created buyer in storage.
     */
    public function store(Request $request)
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
            // At least one Aadhar is required (both name and number)
            'aadhar_name' => 'nullable|array|min:1',
            'aadhar_name.*' => 'nullable|string|max:255',
            'aadhar_number' => 'nullable|array|min:1',
            'aadhar_number.*' => 'nullable|string|max:20|',
            // At least one PAN is required
            'pan_number' => 'nullable|array|min:1',
            'pan_number.*' => 'nullable|string|max:20|',
            'gst_no' => 'nullable|string|max:20|unique:buyers',
            'cin_no' => 'nullable|string|max:21',
            'cin_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Generate BP code
        // Generate BP code based on business name
        $bpCode = Buyer::generateBpCode($request->business_name);

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
            'password' => $request->password ? bcrypt($request->password) : bcrypt('password'),
            'password_plain' => $request->password ?? 'password',
            'permissions' => $request->has('permissions') ? json_encode($request->permissions) : json_encode([]),
        ]);

        // Handle file uploads for attachments
        if ($request->hasFile('gst_attachment')) {
            $file = $request->file('gst_attachment');
            $filename = time() . '_gst_' . $file->getClientOriginalName();
            $file->storeAs('gst', $filename);
            $buyer->gst_attachment = 'gst/' . $filename;
        }

        if ($request->hasFile('bis_attachment')) {
            $file = $request->file('bis_attachment');
            $filename = time() . '_bis_' . $file->getClientOriginalName();
            $file->storeAs('bis', $filename);
            $buyer->bis_attachment = 'bis/' . $filename;
        }

        if ($request->hasFile('msme_attachment')) {
            $file = $request->file('msme_attachment');
            $filename = time() . '_msme_' . $file->getClientOriginalName();
            $file->storeAs('msme', $filename);
            $buyer->msme_attachment = 'msme/' . $filename;
        }

        if ($request->hasFile('tan_attachment')) {
            $file = $request->file('tan_attachment');
            $filename = time() . '_tan_' . $file->getClientOriginalName();
            $file->storeAs('tan', $filename);
            $buyer->tan_attachment = 'tan/' . $filename;
        }

        if ($request->hasFile('cin_attachment')) {
            $file = $request->file('cin_attachment');
            $filename = time() . '_cin_' . $file->getClientOriginalName();
            $file->storeAs('cin', $filename);
            $buyer->cin_attachment = 'cin/' . $filename;
        }

        $buyer->save();

        // Handle multiple Aadhar details
        if ($request->has('aadhar_name') && is_array($request->aadhar_name)) {
            foreach ($request->aadhar_name as $index => $aadharName) {
                if (!empty($aadharName) && !empty($request->aadhar_number[$index])) {
                    $aadharData = [
                        'buyer_id' => $buyer->id,
                        'aadhar_name' => $aadharName,
                        'aadhar_number' => $request->aadhar_number[$index],
                    ];

                    // Handle file upload
                    if ($request->hasFile("aadhar_image.$index")) {
                        $file = $request->file("aadhar_image.$index");
                        $filename = time() . '_' . $index . '_' . $file->getClientOriginalName();
                        $file->storeAs('aadhar', $filename);
                        $aadharData['aadhar_image'] = 'aadhar/' . $filename;
                    }

                    \App\Models\BuyerAadharDetail::create($aadharData);
                }
            }
        }

        // Handle multiple PAN details
        if ($request->has('pan_number') && is_array($request->pan_number)) {
            foreach ($request->pan_number as $index => $panNumber) {
                if (!empty($panNumber)) {
                    $panData = [
                        'buyer_id' => $buyer->id,
                        'pan_number' => $panNumber,
                    ];

                    // Handle file upload
                    if ($request->hasFile("pan_image.$index")) {
                        $file = $request->file("pan_image.$index");
                        $filename = time() . '_pan_' . $index . '_' . $file->getClientOriginalName();
                        $file->storeAs('pan', $filename);
                        $panData['pan_image'] = 'pan/' . $filename;
                    }

                    \App\Models\BuyerPanDetail::create($panData);
                }
            }
        }

        // Handle multiple Bank details
        if ($request->has('bank_name') && is_array($request->bank_name)) {
            foreach ($request->bank_name as $index => $bankName) {
                if (!empty($bankName) && !empty($request->account_holder_name[$index]) && !empty($request->account_number[$index])) {
                    $bankData = [
                        'buyer_id' => $buyer->id,
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
                        $file->storeAs('passbook', $filename);
                        $bankData['passbook_image'] = 'passbook/' . $filename;
                    }

                    \App\Models\BuyerBankDetail::create($bankData);
                }
            }
        }

        return redirect()->route('super-admin.business-partner.buyer')
            ->with('success', 'Buyer created successfully with multiple KYC details!');
    }

    /**
     * Display the specified buyer.
     */
    public function show(Buyer $buyer)
    {
        $buyer->load(['aadharDetails', 'panDetails', 'bankDetails']);
        return view('super-admin.business-partner.show', compact('buyer'));
    }

    public function print(Buyer $buyer)
    {
        $buyer->load(['aadharDetails', 'panDetails', 'bankDetails']);
        return view('super-admin.business-partner.show', compact('buyer'));
    }

    /**
     * Show the form for editing the specified buyer.
     */
    public function edit(Buyer $buyer)
    {
        $buyer->load(['aadharDetails', 'panDetails', 'bankDetails']);
        return view('super-admin.business-partner.edit', compact('buyer'));
    }

    /**
     * Update the specified buyer in storage.
     */
    public function update(Request $request, Buyer $buyer)
    {
        $validator = Validator::make($request->all(), [
            'dear' => 'nullable|string|unique:buyers,dear,' . $buyer->id,
            'business_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15|unique:buyers,mobile,' . $buyer->id,
            'email' => 'required|email|max:255|unique:buyers,email,' . $buyer->id,
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:6|confirmed',
            // At least one Aadhar is required (both name and number)
            'aadhar_name' => 'nullable|array|min:1',
            'aadhar_name.*' => 'nullable|string|max:255',
            'aadhar_number' => 'nullable|array|min:1',
            'aadhar_number.*' => 'nullable|string|max:20|',
            // At least one PAN is required
            'pan_number' => 'nullable|array|min:1',
            'pan_number.*' => 'nullable|string|max:20|',
            'gst_no' => 'nullable|string|max:20|unique:buyers,gst_no,' . $buyer->id,
            'cin_no' => 'nullable|string|max:21',
            'cin_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $buyer->update([
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
            'cin_no' =>  $request->cin_no,
            'pan_no' => $request->pan_number[0] ?? null,
            'tan_no' => $request->tan_no,
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
            'password' => $request->password ? bcrypt($request->password) : $buyer->password,
            'password_plain' => $request->password ?: $buyer->password_plain,
            'permissions' => $request->permissions ?? [],
        ]);

        // Handle file uploads for attachments (only if new files are uploaded)
        if ($request->hasFile('gst_attachment')) {
            // Delete old file if exists
            if ($buyer->gst_attachment && Storage::exists('public/' . $buyer->gst_attachment)) {
                Storage::delete('public/' . $buyer->gst_attachment);
            }
            $file = $request->file('gst_attachment');
            $filename = time() . '_gst_' . $file->getClientOriginalName();
            $file->storeAs('public/gst', $filename);
            $buyer->gst_attachment = 'gst/' . $filename;
        }

        if ($request->hasFile('bis_attachment')) {
            if ($buyer->bis_attachment && Storage::exists('public/' . $buyer->bis_attachment)) {
                Storage::delete('public/' . $buyer->bis_attachment);
            }
            $file = $request->file('bis_attachment');
            $filename = time() . '_bis_' . $file->getClientOriginalName();
            $file->storeAs('public/bis', $filename);
            $buyer->bis_attachment = 'bis/' . $filename;
        }

        if ($request->hasFile('msme_attachment')) {
            if ($buyer->msme_attachment && Storage::exists('public/' . $buyer->msme_attachment)) {
                Storage::delete('public/' . $buyer->msme_attachment);
            }
            $file = $request->file('msme_attachment');
            $filename = time() . '_msme_' . $file->getClientOriginalName();
            $file->storeAs('public/msme', $filename);
            $buyer->msme_attachment = 'msme/' . $filename;
        }

        if ($request->hasFile('tan_attachment')) {
            if ($buyer->tan_attachment && Storage::exists('public/' . $buyer->tan_attachment)) {
                Storage::delete('public/' . $buyer->tan_attachment);
            }
            $file = $request->file('tan_attachment');
            $filename = time() . '_tan_' . $file->getClientOriginalName();
            $file->storeAs('public/tan', $filename);
            $buyer->tan_attachment = 'tan/' . $filename;
        }

        if ($request->hasFile('cin_attachment')) {
            if ($buyer->cin_attachment && Storage::exists('public/' . $buyer->cin_attachment)) {
                Storage::delete('public/' . $buyer->cin_attachment);
            }
            $file = $request->file('cin_attachment');
            $filename = time() . '_cin_' . $file->getClientOriginalName();
            $file->storeAs('cin', $filename);
            $buyer->cin_attachment = 'cin/' . $filename;
        }

        $buyer->save();

        // Delete existing related records
        $buyer->aadharDetails()->delete();
        $buyer->panDetails()->delete();
        $buyer->bankDetails()->delete();

        // Handle multiple Aadhar details
        if ($request->has('aadhar_name') && is_array($request->aadhar_name)) {
            foreach ($request->aadhar_name as $index => $aadharName) {
                if (!empty($aadharName) && !empty($request->aadhar_number[$index])) {
                    $aadharData = [
                        'buyer_id' => $buyer->id,
                        'aadhar_name' => $aadharName,
                        'aadhar_number' => $request->aadhar_number[$index],
                    ];

                    // Handle file upload
                    if ($request->hasFile("aadhar_image.$index")) {
                        $file = $request->file("aadhar_image.$index");
                        $filename = time() . '_' . $index . '_' . $file->getClientOriginalName();
                        $file->storeAs('aadhar', $filename);
                        $aadharData['aadhar_image'] = 'aadhar/' . $filename;
                    }

                    \App\Models\BuyerAadharDetail::create($aadharData);
                }
            }
        }

        // Handle multiple PAN details
        if ($request->has('pan_number') && is_array($request->pan_number)) {
            foreach ($request->pan_number as $index => $panNumber) {
                if (!empty($panNumber)) {
                    $panData = [
                        'buyer_id' => $buyer->id,
                        'pan_number' => $panNumber,
                    ];

                    // Handle file upload
                    if ($request->hasFile("pan_image.$index")) {
                        $file = $request->file("pan_image.$index");
                        $filename = time() . '_pan_' . $index . '_' . $file->getClientOriginalName();
                        $file->storeAs('pan', $filename);
                        $panData['pan_image'] = 'pan/' . $filename;
                    }

                    \App\Models\BuyerPanDetail::create($panData);
                }
            }
        }

        // Handle multiple Bank details
        if ($request->has('bank_name') && is_array($request->bank_name)) {
            foreach ($request->bank_name as $index => $bankName) {
                if (!empty($bankName) && !empty($request->account_holder_name[$index]) && !empty($request->account_number[$index])) {
                    $bankData = [
                        'buyer_id' => $buyer->id,
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
                        $file->storeAs('passbook', $filename);
                        $bankData['passbook_image'] = 'passbook/' . $filename;
                    }

                    \App\Models\BuyerBankDetail::create($bankData);
                }
            }
        }

        return redirect()->route('super-admin.business-partner.buyer')
            ->with('success', 'Buyer updated successfully with multiple KYC details!');
    }

    /**
     * Remove the specified buyer from storage.
     */
    public function destroy(Buyer $buyer)
    {
        $buyer->delete();

        return redirect()->route('super-admin.business-partner.buyer')
            ->with('success', 'Buyer deleted successfully!');
    }

    /**
     * Export buyers to Excel
     */
    private function exportBuyers(Request $request)
    {
        $query = Buyer::query();

        // Apply search if present
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

        // Apply filters
        if ($request->filled('bp_code')) {
            $query->where('bp_code', $request->bp_code);
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

        $allowedSortColumns = ['bp_code', 'business_name', 'name', 'mobile', 'email', 'city', 'state', 'created_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, $sortOrder);

        $buyers = $query->get();

        // Create CSV content
        $csvContent = "BP Code,Business Name,Contact Person,Mobile,Email,City,State,Created At\n";

        foreach ($buyers as $buyer) {
            $csvContent .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s\n",
                $buyer->bp_code,
                $buyer->business_name,
                $buyer->name,
                $buyer->mobile,
                $buyer->email,
                $buyer->city ?? 'N/A',
                $buyer->state ?? 'N/A',
                $buyer->created_at->format('Y-m-d H:i:s')
            );
        }

        // Return CSV download
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="buyers_export.csv"');
    }

    /**
     * Export buyers to Excel or PDF
     */
    public function export(Request $request)
    {
        // Basic validation
        $format = $request->input('format', 'excel');
        if (!in_array($format, ['excel', 'pdf'])) {
            abort(400, 'Invalid format. Please use excel or pdf.');
        }

        $fields = $request->input('fields', []);

        $export = new \App\Exports\BuyerExport($fields);

        if ($format === 'pdf') {
            return \Maatwebsite\Excel\Facades\Excel::download($export, 'buyers.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
        }

        return \Maatwebsite\Excel\Facades\Excel::download($export, 'buyers.xlsx');
    }

    public function printSelected(Request $request)
    {
        $selectedIds = $request->input('selected_buyers', []);

        if (empty($selectedIds)) {
            return redirect()->back()->with('error', 'No buyers selected for printing.');
        }

        $buyers = Buyer::whereIn('id', $selectedIds)->get();

        return view('super-admin.business-partner.print-selected', compact('buyers'));
    }

    public function printAll(Request $request)
    {
        $query = Buyer::query();

        // Apply filters from request parameters
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

        // Filtering functionality
        if ($request->filled('bp_code')) {
            $query->where('bp_code', $request->bp_code);
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

        $allowedSortColumns = ['bp_code', 'business_name', 'name', 'mobile', 'email', 'city', 'state', 'created_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, $sortOrder);

        $buyers = $query->get();

        return view('super-admin.business-partner.print-all', compact('buyers'));
    }
    public function approve(Buyer $buyer)
    {
        $buyer->update(['kyc_status' => 'approved']);

        return redirect()->back()->with('success', 'Buyer KYC approved successfully. Profile is now read-only for the buyer.');
    }

    /**
     * Unlock Buyer Profile.
     */
    public function unlock(Buyer $buyer)
    {
        $buyer->update(['kyc_status' => 'pending']);

        return redirect()->back()->with('success', 'Buyer profile unlocked successfully. Buyer can now edit their details.');
    }
}
