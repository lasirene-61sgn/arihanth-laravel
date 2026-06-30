<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Show the form for editing the profile.
     */
    public function edit()
    {
        $buyer = Auth::guard('buyer')->user();
        
        // Check if the profile is approved (read-only)
        $isReadOnly = $buyer->kyc_status === 'approved';

        return view('buyer.profile.edit', compact('buyer', 'isReadOnly'));
    }

    /**
     * Update the profile.
     */
    public function update(Request $request)
    {
        $buyer = Auth::guard('buyer')->user();

        // If approved, only allow updating non-critical fields if any, or strictly forbid
        // For now, based on requirement, if approved, it's read-only.
        if ($buyer->kyc_status === 'approved') {
            return redirect()->back()->with('error', 'Your profile is approved and cannot be edited. Please contact support for changes.');
        }

        $validator = Validator::make($request->all(), [
            'business_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:buyers,email,' . $buyer->id,
            
            // Address
            'door_no' => 'nullable|string|max:255',
            'shop_no' => 'nullable|string|max:255',
            'complex_name' => 'nullable|string|max:255',
            'street_name' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            
            // KYC Documents (File uploads)
            'bis_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'gst_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'msme_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'pan_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'tan_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'cin_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            
            // Numbers
            'bis_no' => 'nullable|string|max:50',
            // 'gst_no' => 'nullable|string|max:50', // GST No is not updatable by Buyer
            'msme_no' => 'nullable|string|max:50',
            'pan_no' => 'nullable|string|max:50',
            'tan_no' => 'nullable|string|max:50',
            'cin_no' => 'nullable|string|max:50',
            'aadhar_no' => 'nullable|string|max:50',
            
            // Aadhar
            'aadhar_name.*' => 'required|string|max:255',
            'aadhar_number.*' => 'required|string|max:50',
            'aadhar_image.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            
            // PAN
            'pan_image.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            
            // Bank
            'passbook_image.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Handle File Uploads
        $data = $request->except(['image', 'bis_attachment', 'gst_attachment', 'msme_attachment', 'pan_attachment', 'tan_attachment', 'cin_attachment', 'aadhar_image', 'pan_image', 'passbook_image', 'gst_no', 'aadhar_name', 'aadhar_number', 'pan_number', 'bank_name_detail']);

        // Explicitly exclude gst_no from update to be safe
        unset($data['gst_no']);

        $files = ['image', 'bis_attachment', 'gst_attachment', 'msme_attachment', 'pan_attachment', 'tan_attachment', 'cin_attachment'];
        foreach ($files as $file) {
            if ($request->hasFile($file)) {
                // Delete old file if exists
                if ($buyer->$file) {
                    Storage::disk('public')->delete($buyer->$file);
                }
                $data[$file] = $request->file($file)->store('buyers/' . $file, 'public');
            }
        }

        $buyer->update($data);

        // Update Related Models (Delete and Re-create logic as per Pattern)

        // 1. Aadhar Details
        $buyer->aadharDetails()->delete();
        if ($request->has('aadhar_number')) {
            foreach ($request->aadhar_number as $index => $number) {
                if ($number) {
                    $imagePath = null;
                    if ($request->hasFile('aadhar_image.' . $index)) {
                        $imagePath = $request->file('aadhar_image.' . $index)->store('buyers/aadhar', 'public');
                    } elseif (isset($request->existing_aadhar_image[$index])) {
                        $imagePath = $request->existing_aadhar_image[$index];
                    }

                    $buyer->aadharDetails()->create([
                        'aadhar_name' => $request->aadhar_name[$index] ?? $buyer->name, // Fallback to buyer name if missing, though validation should catch it
                        'aadhar_number' => $number,
                        'aadhar_image' => $imagePath,
                    ]);
                }
            }
        }

        // 2. PAN Details
        $buyer->panDetails()->delete();
        if ($request->has('pan_number')) {
            foreach ($request->pan_number as $index => $number) {
                if ($number) {
                    $imagePath = null;
                    if ($request->hasFile('pan_image.' . $index)) {
                        $imagePath = $request->file('pan_image.' . $index)->store('buyers/pan', 'public');
                    } elseif (isset($request->existing_pan_image[$index])) {
                        $imagePath = $request->existing_pan_image[$index];
                    }

                    $buyer->panDetails()->create([
                        'pan_number' => $number,
                        'pan_image' => $imagePath,
                    ]);
                }
            }
        }

        // 3. Bank Details
        $buyer->bankDetails()->delete();
        if ($request->has('bank_name_detail')) {
            foreach ($request->bank_name_detail as $index => $name) {
                if ($name) {
                    $imagePath = null;
                    if ($request->hasFile('passbook_image.' . $index)) {
                        $imagePath = $request->file('passbook_image.' . $index)->store('buyers/bank', 'public');
                    } elseif (isset($request->existing_passbook_image[$index])) {
                        $imagePath = $request->existing_passbook_image[$index];
                    }

                    $buyer->bankDetails()->create([
                        'bank_name' => $name,
                        'account_holder_name' => $request->account_holder_name[$index] ?? null,
                        'account_number' => $request->account_number[$index] ?? null,
                        'ifsc_code' => $request->ifsc_code_detail[$index] ?? null,
                        'branch_name' => $request->branch_name[$index] ?? null,
                        'passbook_image' => $imagePath,
                    ]);
                }
            }
        }

        return redirect()->route('buyer.profile.edit')->with('success', 'Profile updated successfully.');
    }
}
