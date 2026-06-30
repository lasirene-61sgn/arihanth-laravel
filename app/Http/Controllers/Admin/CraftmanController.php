<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Craftman;
use App\Models\CraftmanAadharDetail;
use App\Models\CraftmanPanDetail;
use App\Models\CraftmanBankDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class CraftmanController extends Controller
{
    /**
     * Display a listing of the craftmen.
     */
    public function index(Request $request)
    {
        $query = Craftman::query();

        // Specific Filters
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('craftman_code')) {
            $query->where('craftman_code', 'like', '%' . $request->craftman_code . '%');
        }
        if ($request->filled('business_name')) {
            $query->where('business_name', 'like', '%' . $request->business_name . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->filled('mobile')) {
            $query->where('mobile', 'like', '%' . $request->mobile . '%');
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        $craftmen = $query->orderBy($sort, $direction)
                      ->paginate(10) 
                      ->appends($request->all());

        return view('admin.business-partner.craftman', compact('craftmen'));
    }

// 3. Add the Export Method

    /**
     * Show the form for creating a new craftman.
     */
    public function create()
    {
        return view('admin.business-partner.create-craftman');
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
            // At least one Aadhar is required (both name and number)
            'aadhar_name' => 'nullable|array|min:1',
            'aadhar_name.*' => 'nullable|string|max:255',
            'aadhar_number' => 'nullable|array|min:1',
            'aadhar_number.*' => 'nullable|string|max:20|unique:craftmen,aadhar_no',
            // At least one PAN is required
            'pan_number' => 'nullable|array|min:1',
            'pan_number.*' => 'nullable|string|max:20|unique:craftmen,pan_no',
            'gst_no' => 'nullable|string|max:20|unique:craftmen',
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
            'permissions' => $request->permissions ?? [],
            'password' => $request->password ? bcrypt($request->password) : bcrypt('password'),
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

        return redirect()->route('admin.business-partner.craftman')
            ->with('success', 'Craftman created successfully with multiple KYC details!');
    }

    /**
     * Display the specified craftman.
     */
    public function show(Craftman $craftman)
    {
        return view('admin.business-partner.show-craftman', compact('craftman'));
    }

    /**
     * Show the form for editing the specified craftman.
     */
    public function edit(Craftman $craftman)
    {
        $craftman->load(['aadharDetails', 'panDetails', 'bankDetails']);
        return view('admin.business-partner.edit-craftman', compact('craftman'));
    }

    /**
     * Update the specified craftman in storage.
     */
    public function update(Request $request, Craftman $craftman)
    {
        $validator = Validator::make($request->all(), [
            'craftman_code' => 'required|string|unique:craftmen,craftman_code,' . $craftman->id,
            'dear' => 'nullable|string',
            'business_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15|unique:craftmen,mobile,' . $craftman->id,
            'email' => 'required|email|max:255|unique:craftmen,email,' . $craftman->id,
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            // At least one Aadhar is required (both name and number)
            'aadhar_name' => 'nullable|array|min:1',
            'aadhar_name.*' => 'nullable|string|max:255',
            'aadhar_number' => 'nullable|array|min:1',
            'aadhar_number.*' => 'nullable|string',
            // At least one PAN is required
            'pan_number' => 'nullable|array|min:1',
            'pan_number.*' => 'nullable|string|max:20',
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
            'permissions' => $request->permissions ?? [],
            'password' => $request->password ? bcrypt($request->password) : $craftman->password,
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

        $craftman->cin_no = $request->cin_no;
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

        return redirect()->route('admin.business-partner.craftman')
            ->with('success', 'Craftman updated successfully with multiple KYC details!');
    }

    /**
     * Remove the specified craftman from storage.
     */
    public function destroy(Craftman $craftman)
    {
        $craftman->delete();

        return redirect()->route('admin.business-partner.craftman')
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
    public function export(Request $request)
    {
        // Make sure you create this Export class later!
        return Excel::download(new \App\Exports\AdminCraftsmansExport, 'craftmen_list.xlsx');
    }

    public function printSelected(Request $request)
    {
        $selectedIds = $request->input('selected_craftsmen', []);

        if (empty($selectedIds)) {
            return redirect()->back()->with('error', 'No craftsmen selected for printing.');
        }

        $craftmen = Craftman::whereIn('id', $selectedIds)->get();

        return view('admin.business-partner.print-selected-craftman', compact('craftmen'));
    }
}
