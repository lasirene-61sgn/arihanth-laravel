<?php

namespace App\Http\Controllers\Craftsman;

use App\Http\Controllers\Controller;
use App\Models\Craftman;
use App\Models\CraftmanAadharDetail;
use App\Models\CraftmanBankDetail;
use App\Models\CraftmanPanDetail;
use App\Models\CraftmanWorker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $craftman = Auth::guard('craftsman')->user()->load(['aadharDetails', 'panDetails', 'bankDetails', 'workers']);
        
        // Determine if profile is read-only based on KYC status
        $isReadOnly = $craftman->kyc_status === 'approved';
        
        return view('craftsman.profile.edit', compact('craftman', 'isReadOnly'));
    }

    public function update(Request $request)
    {
        $craftman = Auth::guard('craftsman')->user();

        // If KYC is approved, prevent updates
        if ($craftman->kyc_status === 'approved') {
            return redirect()->back()->with('error', 'Your profile is approved and cannot be edited. Please contact administrator.');
        }

        // Validate basic fields
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15',
            'email' => 'nullable|email|max:255',
            // Add other validations as needed
        ]);
        
        // Exclude sensitive/locked fields from update
        $data = $request->except([
            'craftman_code', 
            'password', 
            'is_frozen', 
            'kyc_status',
            'gst_no',          // Locked by default
            'gst_attachment'   // Locked by default
        ]);

        // Handle File Uploads (excluding GST)
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('craftsman/profile', 'public');
        }
        if ($request->hasFile('bis_attachment')) {
            $data['bis_attachment'] = $request->file('bis_attachment')->store('craftsman/bis', 'public');
        }
        if ($request->hasFile('msme_attachment')) {
            $data['msme_attachment'] = $request->file('msme_attachment')->store('craftsman/msme', 'public');
        }
        // ... handle other simple file uploads

        // Update Craftsman
        $craftman->update($data);

        // Handle Relationships (Aadhar, PAN, Bank, Workers)
        // Note: For simplicity and to match existing logic, we might need to recreate these
        // or update them if ID is provided. existing logic usually deletes and recreates.
        
        $this->updateAadharDetails($craftman, $request);
        $this->updatePanDetails($craftman, $request);
        $this->updateBankDetails($craftman, $request);
        $this->updateWorkers($craftman, $request);

        return redirect()->route('craftsman.profile.edit')->with('success', 'Profile updated successfully.');
    }

    private function updateAadharDetails($craftman, $request)
    {
        $craftman->aadharDetails()->delete();
        if ($request->has('aadhar_name')) {
            foreach ($request->aadhar_name as $key => $name) {
                if ($name && isset($request->aadhar_number[$key])) {
                    $path = null;
                    if (isset($request->file('aadhar_image')[$key])) {
                        $path = $request->file('aadhar_image')[$key]->store('craftman-aadhar', 'public');
                    }
                    
                    CraftmanAadharDetail::create([
                        'craftman_id' => $craftman->id,
                        'aadhar_name' => $name,
                        'aadhar_number' => $request->aadhar_number[$key],
                        'aadhar_image' => $path
                    ]);
                }
            }
        }
    }

    private function updatePanDetails($craftman, $request)
    {
        $craftman->panDetails()->delete();
        if ($request->has('pan_number')) {
            foreach ($request->pan_number as $key => $number) {
                if ($number) {
                    $path = null;
                    if (isset($request->file('pan_image')[$key])) {
                        $path = $request->file('pan_image')[$key]->store('craftman-pan', 'public');
                    }
                    
                    CraftmanPanDetail::create([
                        'craftman_id' => $craftman->id,
                        'pan_number' => $number,
                        'pan_image' => $path
                    ]);
                }
            }
        }
    }

    private function updateBankDetails($craftman, $request)
    {
        $craftman->bankDetails()->delete();
        if ($request->has('bank_name')) {
            foreach ($request->bank_name as $key => $name) {
                if ($name) {
                    $path = null;
                    if (isset($request->file('passbook_image')[$key])) {
                        $path = $request->file('passbook_image')[$key]->store('craftman-passbook', 'public');
                    }
                    
                    CraftmanBankDetail::create([
                        'craftman_id' => $craftman->id,
                        'bank_name' => $name,
                        'account_holder_name' => $request->account_holder_name[$key] ?? null,
                        'account_number' => $request->account_number[$key] ?? null,
                        'ifsc_code' => $request->ifsc_code[$key] ?? null,
                        'branch' => $request->branch[$key] ?? null,
                        'bank_city' => $request->bank_city[$key] ?? null,
                        'bank_state' => $request->bank_state[$key] ?? null,
                        'passbook_image' => $path
                    ]);
                }
            }
        }
    }

    private function updateWorkers($craftman, $request)
    {
        $craftman->workers()->delete();
        if ($request->has('worker_name')) {
            foreach ($request->worker_name as $key => $name) {
                if ($name) {
                    $path = null;
                    if (isset($request->file('worker_image')[$key])) {
                        $path = $request->file('worker_image')[$key]->store('craftman-workers', 'public');
                    }
                    
                    CraftmanWorker::create([
                        'craftman_id' => $craftman->id,
                        'worker_name' => $name,
                        'worker_number' => $request->worker_number[$key] ?? null,
                        'worker_image' => $path
                    ]);
                }
            }
        }
    }
}
