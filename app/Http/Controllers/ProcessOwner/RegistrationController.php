<?php

namespace App\Http\Controllers\ProcessOwner;

use App\Http\Controllers\Controller;
use App\Models\ProcessOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegistrationController extends Controller
{
    /**
     * Show the registration form
     */
    public function showRegistrationForm()
    {
        return view('process-owner.register');
    }

    /**
     * Handle the registration request
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email_id' => 'required|string|email|max:255|unique:process_owners',
            'mobile_no' => 'required|string|max:15',
            'password' => 'required|string|min:8|confirmed',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'aadhar_number' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Generate user code
        $userCode = $this->generateUserCode();

        $processOwner = ProcessOwner::create([
            'user_code' => $userCode,
            'full_name' => $request->full_name,
            'email_id' => $request->email_id,
            'mobile_no' => $request->mobile_no,
            'password' => Hash::make($request->password),
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'pincode' => $request->pincode,
            'aadhar_number' => $request->aadhar_number,
            'status' => 1, // Active by default
            'role' => 'process_owner', // Default role
        ]);

        return redirect()->route('process-owner.login')
            ->with('success', 'Registration successful! Please login.');
    }

    /**
     * Generate user code (PO0001, PO0002, etc. for process owners and SA0001, SA0002, etc. for super admins)
     */
    private function generateUserCode($role = 'process_owner')
    {
        // Determine prefix based on role
        $prefix = ($role === 'super_admin') ? 'SA' : 'PO';
        
        // Find the last user with the same role
        $lastProcessOwner = ProcessOwner::where('role', $role)
            ->orderBy('id', 'desc')
            ->first();
        
        if (!$lastProcessOwner) {
            return $prefix . '0001';
        }

        $lastUserCode = $lastProcessOwner->user_code;
        $number = intval(substr($lastUserCode, 2)) + 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Handle super admin creation request from process owner dashboard
     */
    public function createSuperAdmin(Request $request)
    {
        // Validate that the authenticated user is a process owner (not a super admin)
        $processOwner = auth()->guard('process_owner')->user();
        
        // Only process owners can create super admins
        if ($processOwner->role !== 'process_owner') {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email_id' => 'required|string|email|max:255|unique:process_owners',
            'mobile_no' => 'required|string|max:15',
            'password' => 'required|string|min:8|confirmed',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'aadhar_number' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Generate super admin user code
        $userCode = $this->generateUserCode('super_admin');

        $superAdmin = ProcessOwner::create([
            'user_code' => $userCode,
            'full_name' => $request->full_name,
            'email_id' => $request->email_id,
            'mobile_no' => $request->mobile_no,
            'password' => Hash::make($request->password),
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'pincode' => $request->pincode,
            'aadhar_number' => $request->aadhar_number,
            'status' => 1, // Active by default
            'role' => 'super_admin',
        ]);

        return redirect()->back()
            ->with('success', 'Super Admin created successfully!');
    }

    /**
     * Show the dashboard with the correct list of Super Admins
     */
    public function index()
    {
        // FIXED: Changed 'superadmin' to 'super_admin' to match the database values
        $superAdmins = ProcessOwner::where('role', 'super_admin')
            ->orderBy('id', 'desc')
            ->get();

        return view('process-owner.dashboard', compact('superAdmins'));
    }
    protected function authenticated(Request $request, $user)
    {
        // Explicitly point to the named route structure
        return redirect()->route('process-owner.dashboard');
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        Auth::guard('process_owner')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('process-owner.login');
    }
}