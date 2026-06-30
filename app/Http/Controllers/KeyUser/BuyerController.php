<?php

namespace App\Http\Controllers\KeyUser;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class BuyerController extends Controller
{
    /**
     * Display business partner overview page for key users
     */
    public function businessPartnerIndex()
    {
        // Check if it's a key user or buyer
        $keyUser = Auth::guard('key_user')->user();
        $buyer = Auth::guard('buyer')->user();
        
        // Get buyers based on user type
        if ($keyUser) {
            // Key users can see all buyers
            $buyers = Buyer::all();
        } elseif ($buyer) {
            // Buyers can only see themselves
            $buyers = collect([$buyer]);
        } else {
            return redirect()->route('key-user.login');
        }
        
        $craftmen = \App\Models\Craftman::all();
        return view('key-user.business-partner.index', compact('buyers', 'craftmen'));
    }

    /**
     * Display a listing of the buyers.
     */
    public function index()
    {
        // Check if it's a key user or buyer
        $keyUser = Auth::guard('key_user')->user();
        $buyer = Auth::guard('buyer')->user();
        
        // Get buyers based on user type
        if ($keyUser) {
            // Key users can see all buyers
            $buyers = Buyer::all();
        } elseif ($buyer) {
            // Buyers can only see themselves
            $buyers = collect([$buyer]);
        } else {
            return redirect()->route('key-user.login');
        }
        
        return view('key-user.business-partner.buyer', compact('buyers'));
    }

    /**
     * Show the form for creating a new buyer.
     */
    public function create()
    {
        // Only key users can create buyers
        if (!Auth::guard('key_user')->check()) {
            return redirect()->route('key-user.dashboard')->with('error', 'Access denied.');
        }
        
        return view('key-user.business-partner.create');
    }

    /**
     * Store a newly created buyer in storage.
     */
    public function store(Request $request)
    {
        // Only key users can create buyers
        if (!Auth::guard('key_user')->check()) {
            return redirect()->route('key-user.dashboard')->with('error', 'Access denied.');
        }
        
        $validator = Validator::make($request->all(), [
            'business_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15',
            'email' => 'required|email|max:255|unique:buyers',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:6|confirmed',
            // At least one Aadhar is required (both name and number)
            'aadhar_name' => 'required|array|min:1',
            'aadhar_name.*' => 'required|string|max:255',
            'aadhar_number' => 'required|array|min:1',
            'aadhar_number.*' => 'required|string|max:20',
            // At least one PAN is required
            'pan_number' => 'required|array|min:1',
            'pan_number.*' => 'required|string|max:20',
            'gst_no' => 'nullable|string|max:20',
            'cin_no' => 'nullable|string|max:21',
            'cin_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Generate BP code
        $bpCode = Buyer::generateBpCode();

        // Handle file uploads
        $cinAttachmentPath = null;
        if ($request->hasFile('cin_attachment')) {
            $cinAttachmentPath = $request->file('cin_attachment')->store('buyers/cin', 'public');
        }

        // Create buyer
        $buyer = Buyer::create([
            'bp_code' => $bpCode,
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
            'cin_attachment' => $cinAttachmentPath,
            'note' => $request->note,
            'password' => $request->password ? Hash::make($request->password) : Hash::make('password123'),
            'created_by' => Auth::guard('key_user')->id(),
        ]);

        // Handle multiple Aadhar details
        if ($request->has('aadhar_name') && $request->has('aadhar_number')) {
            foreach ($request->aadhar_name as $index => $aadharName) {
                if (!empty($aadharName) && !empty($request->aadhar_number[$index])) {
                    $aadharImagePath = null;
                    if (isset($request->aadhar_image[$index]) && $request->aadhar_image[$index]) {
                        $aadharImagePath = $request->aadhar_image[$index]->store('buyers/aadhar', 'public');
                    }
                    
                    $buyer->aadharDetails()->create([
                        'name' => $aadharName,
                        'aadhar_number' => $request->aadhar_number[$index],
                        'image' => $aadharImagePath
                    ]);
                }
            }
        }

        // Handle multiple PAN details
        if ($request->has('pan_number')) {
            foreach ($request->pan_number as $index => $panNumber) {
                if (!empty($panNumber)) {
                    $panImagePath = null;
                    if (isset($request->pan_image[$index]) && $request->pan_image[$index]) {
                        $panImagePath = $request->pan_image[$index]->store('buyers/pan', 'public');
                    }
                    
                    $buyer->panDetails()->create([
                        'pan_number' => $panNumber,
                        'image' => $panImagePath
                    ]);
                }
            }
        }

        // Handle multiple Bank details
        if ($request->has('bank_name') && $request->has('account_holder_name')) {
            foreach ($request->bank_name as $index => $bankName) {
                if (!empty($bankName) && !empty($request->account_holder_name[$index])) {
                    $passbookImagePath = null;
                    if (isset($request->passbook_image[$index]) && $request->passbook_image[$index]) {
                        $passbookImagePath = $request->passbook_image[$index]->store('buyers/bank', 'public');
                    }
                    
                    $buyer->bankDetails()->create([
                        'bank_name' => $bankName,
                        'account_holder_name' => $request->account_holder_name[$index],
                        'account_number' => $request->account_number[$index] ?? null,
                        'ifsc_code' => $request->ifsc_code[$index] ?? null,
                        'branch' => $request->branch[$index] ?? null,
                        'bank_city' => $request->bank_city[$index] ?? null,
                        'bank_state' => $request->bank_state[$index] ?? null,
                        'passbook_image' => $passbookImagePath
                    ]);
                }
            }
        }

        return redirect()->route('key-user.business-partner.buyer')
            ->with('success', 'Buyer created successfully with BP Code: ' . $bpCode);
    }

    /**
     * Display the specified buyer.
     */
    public function show(Buyer $buyer)
    {
        // Check permissions
        $keyUser = Auth::guard('key_user')->user();
        $loggedInBuyer = Auth::guard('buyer')->user();
        
        // Buyers can only view themselves
        if ($loggedInBuyer && $loggedInBuyer->id !== $buyer->id) {
            return redirect()->route('key-user.dashboard')->with('error', 'Access denied.');
        }
        
        $buyer->load(['aadharDetails', 'panDetails', 'bankDetails']);
        return view('key-user.business-partner.show', compact('buyer'));
    }

    /**
     * Show the form for editing the specified buyer.
     */
    public function edit(Buyer $buyer)
    {
        // Check permissions
        $keyUser = Auth::guard('key_user')->user();
        $loggedInBuyer = Auth::guard('buyer')->user();
        
        // Buyers can only edit themselves
        if ($loggedInBuyer && $loggedInBuyer->id !== $buyer->id) {
            return redirect()->route('key-user.dashboard')->with('error', 'Access denied.');
        }
        
        // Only key users can edit other buyers
        if (!$keyUser && !$loggedInBuyer) {
            return redirect()->route('key-user.dashboard')->with('error', 'Access denied.');
        }
        
        $buyer->load(['aadharDetails', 'panDetails', 'bankDetails']);
        return view('key-user.business-partner.edit', compact('buyer'));
    }

    /**
     * Update the specified buyer in storage.
     */
    public function update(Request $request, Buyer $buyer)
    {
        // Check permissions
        $keyUser = Auth::guard('key_user')->user();
        $loggedInBuyer = Auth::guard('buyer')->user();
        
        // Buyers can only update themselves
        if ($loggedInBuyer && $loggedInBuyer->id !== $buyer->id) {
            return redirect()->route('key-user.dashboard')->with('error', 'Access denied.');
        }
        
        // Only key users can update other buyers
        if (!$keyUser && !$loggedInBuyer) {
            return redirect()->route('key-user.dashboard')->with('error', 'Access denied.');
        }
        
        $validator = Validator::make($request->all(), [
            'business_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15',
            'email' => 'required|email|max:255|unique:buyers,email,' . $buyer->id,
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:6|confirmed',
            // At least one Aadhar is required (both name and number)
            'aadhar_name' => 'required|array|min:1',
            'aadhar_name.*' => 'required|string|max:255',
            'aadhar_number' => 'required|array|min:1',
            'aadhar_number.*' => 'required|string|max:20',
            // At least one PAN is required
            'pan_number' => 'required|array|min:1',
            'pan_number.*' => 'required|string|max:20',
            'gst_no' => 'nullable|string|max:20',
            'cin_no' => 'nullable|string|max:21',
            'cin_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle file uploads
        $updateData = [
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
            'bis_no' => $request->bis_no,
            'gst_no' => $request->gst_no,
            'msme_no' => $request->msme_no,
            'cin_no' => $request->cin_no,
            'pan_no' => $request->pan_number[0] ?? null,
            'tan_no' => $request->tan_no,
            'aadhar_no' => $request->aadhar_number[0] ?? null,
            'note' => $request->note,
        ];

        if ($request->password) {
            $updateData['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('cin_attachment')) {
            // Delete old attachment if exists
            if ($buyer->cin_attachment) {
                Storage::disk('public')->delete($buyer->cin_attachment);
            }
            $updateData['cin_attachment'] = $request->file('cin_attachment')->store('buyers/cin', 'public');
        }

        $buyer->update($updateData);

        // Update Aadhar details
        $buyer->aadharDetails()->delete();
        if ($request->has('aadhar_name') && $request->has('aadhar_number')) {
            foreach ($request->aadhar_name as $index => $aadharName) {
                if (!empty($aadharName) && !empty($request->aadhar_number[$index])) {
                    $aadharImagePath = null;
                    if (isset($request->aadhar_image[$index]) && $request->aadhar_image[$index]) {
                        // Delete old image if exists
                        $oldAadhar = $buyer->aadharDetails()->skip($index)->first();
                        if ($oldAadhar && $oldAadhar->image) {
                            Storage::disk('public')->delete($oldAadhar->image);
                        }
                        $aadharImagePath = $request->aadhar_image[$index]->store('buyers/aadhar', 'public');
                    } elseif (isset($request->existing_aadhar_image[$index])) {
                        $aadharImagePath = $request->existing_aadhar_image[$index];
                    }
                    
                    $buyer->aadharDetails()->create([
                        'name' => $aadharName,
                        'aadhar_number' => $request->aadhar_number[$index],
                        'image' => $aadharImagePath
                    ]);
                }
            }
        }

        // Update PAN details
        $buyer->panDetails()->delete();
        if ($request->has('pan_number')) {
            foreach ($request->pan_number as $index => $panNumber) {
                if (!empty($panNumber)) {
                    $panImagePath = null;
                    if (isset($request->pan_image[$index]) && $request->pan_image[$index]) {
                        // Delete old image if exists
                        $oldPan = $buyer->panDetails()->skip($index)->first();
                        if ($oldPan && $oldPan->image) {
                            Storage::disk('public')->delete($oldPan->image);
                        }
                        $panImagePath = $request->pan_image[$index]->store('buyers/pan', 'public');
                    } elseif (isset($request->existing_pan_image[$index])) {
                        $panImagePath = $request->existing_pan_image[$index];
                    }
                    
                    $buyer->panDetails()->create([
                        'pan_number' => $panNumber,
                        'image' => $panImagePath
                    ]);
                }
            }
        }

        // Update Bank details
        $buyer->bankDetails()->delete();
        if ($request->has('bank_name') && $request->has('account_holder_name')) {
            foreach ($request->bank_name as $index => $bankName) {
                if (!empty($bankName) && !empty($request->account_holder_name[$index])) {
                    $passbookImagePath = null;
                    if (isset($request->passbook_image[$index]) && $request->passbook_image[$index]) {
                        // Delete old image if exists
                        $oldBank = $buyer->bankDetails()->skip($index)->first();
                        if ($oldBank && $oldBank->passbook_image) {
                            Storage::disk('public')->delete($oldBank->passbook_image);
                        }
                        $passbookImagePath = $request->passbook_image[$index]->store('buyers/bank', 'public');
                    } elseif (isset($request->existing_passbook_image[$index])) {
                        $passbookImagePath = $request->existing_passbook_image[$index];
                    }
                    
                    $buyer->bankDetails()->create([
                        'bank_name' => $bankName,
                        'account_holder_name' => $request->account_holder_name[$index],
                        'account_number' => $request->account_number[$index] ?? null,
                        'ifsc_code' => $request->ifsc_code[$index] ?? null,
                        'branch' => $request->branch[$index] ?? null,
                        'bank_city' => $request->bank_city[$index] ?? null,
                        'bank_state' => $request->bank_state[$index] ?? null,
                        'passbook_image' => $passbookImagePath
                    ]);
                }
            }
        }

        return redirect()->route('key-user.business-partner.buyer')
            ->with('success', 'Buyer updated successfully!');
    }

    /**
     * Remove the specified buyer from storage.
     */
    public function destroy(Buyer $buyer)
    {
        // Only key users can delete buyers
        if (!Auth::guard('key_user')->check()) {
            return redirect()->route('key-user.dashboard')->with('error', 'Access denied.');
        }
        
        // Delete associated files
        if ($buyer->cin_attachment) {
            Storage::disk('public')->delete($buyer->cin_attachment);
        }
        
        foreach ($buyer->aadharDetails as $aadhar) {
            if ($aadhar->image) {
                Storage::disk('public')->delete($aadhar->image);
            }
        }
        
        foreach ($buyer->panDetails as $pan) {
            if ($pan->image) {
                Storage::disk('public')->delete($pan->image);
            }
        }
        
        foreach ($buyer->bankDetails as $bank) {
            if ($bank->passbook_image) {
                Storage::disk('public')->delete($bank->passbook_image);
            }
        }
        
        $buyer->delete();

        return redirect()->route('key-user.business-partner.buyer')
            ->with('success', 'Buyer deleted successfully!');
    }
}