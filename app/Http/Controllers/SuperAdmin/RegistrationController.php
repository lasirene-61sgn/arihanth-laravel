<?php
namespace App\Http\Controllers\SuperAdmin ;

use App\Http\Controllers\Controller;
use App\Models\RegistrationRequest;
use App\Models\Buyer;
use App\Models\Craftman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function index()
    {
        $requests = RegistrationRequest::latest()->paginate(15);
        return view('super-admin.registrations.index', compact('requests'));
    }

    public function show($id)
    {
        $registration = RegistrationRequest::findOrFail($id);
        return view('super-admin.registrations.show', compact('registration'));
    }

    public function approve(Request $request, $id)
    {
        $registration = RegistrationRequest::findOrFail($id);
        
        $request->validate([
            'type' => 'required|in:Buyer,Craftsman',
            'custom_code' => 'nullable|string|max:50',
            'admin_notes' => 'nullable|string',
        ]);

        if ($request->type === 'Buyer') {
            $code = $request->custom_code ?: Buyer::generateBpCode($registration->business_name);
            
            Buyer::create([
                'bp_code' => $code,
                'business_name' => $registration->business_name,
                'name' => $registration->name,
                'mobile' => $registration->mobile,
                'email' => $registration->email,
                'city' => $registration->city,
                'state' => $registration->state,
                'pincode' => $registration->pincode,
                'password' => $registration->password,
                'password_plain' => null, // We don't store plain text in the request for security
                'kyc_status' => 'approved',
            ]);
        } else {
            $code = $request->custom_code ?: Craftman::generateCraftmanCode();
            
            Craftman::create([
                'craftman_code' => $code,
                'business_name' => $registration->business_name,
                'name' => $registration->name,
                'mobile' => $registration->mobile,
                'email' => $registration->email,
                'city' => $registration->city,
                'state' => $registration->state,
                'pincode' => $registration->pincode,
                'password' => $registration->password,
                'password_plain' => null,
                'kyc_status' => 'approved',
            ]);
        }

        $registration->update([
            'status' => 'Approved',
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('super-admin.registrations.index')->with('success', "Registration approved as {$request->type}. The user can now log in using the password they created during registration.");
    }

    public function reject(Request $request, $id)
    {
        $registration = RegistrationRequest::findOrFail($id);
        
        $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        $registration->update([
            'status' => 'Rejected',
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('super-admin.registrations.index')->with('success', 'Registration rejected.');
    }
}
