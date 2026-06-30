<?php

namespace App\Http\Controllers\ProcessOwner;

use App\Http\Controllers\Controller;
use App\Models\ProcessOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('process-owner.login');
    }

    /**
     * Handle the login request
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_or_user_code' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if input is email or user code
        $processOwner = ProcessOwner::where('email_id', $request->email_or_user_code)
            ->orWhere('user_code', $request->email_or_user_code)
            ->first();

        if (!$processOwner) {
            return redirect()->back()
                ->with('error', 'Invalid credentials.')
                ->withInput();
        }

        // Verify password
        if (!Hash::check($request->password, $processOwner->password)) {
            return redirect()->back()
                ->with('error', 'Invalid credentials.')
                ->withInput();
        }

        // Check if account is frozen
        if ($processOwner->is_frozen) {
            return redirect()->back()
                ->with('error', 'Your account has been frozen. Please contact the Super Admin.')
                ->withInput();
        }
        
        // Log in the process owner
        Auth::guard('process_owner')->login($processOwner);

        return redirect()->route('process-owner.dashboard');
    }

    /**
     * Handle the logout request
     */
    public function logout()
    {
        Auth::guard('process_owner')->logout();
        return redirect()->route('process-owner.login');
    }

    /**
     * Show the dashboard
     */
    public function dashboard()
    {
        return view('process-owner.dashboard');
    }
}