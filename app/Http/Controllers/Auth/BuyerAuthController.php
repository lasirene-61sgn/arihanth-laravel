<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Buyer;

class BuyerAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.buyer-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'bp_code' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('bp_code', 'password');
        
        if (Auth::guard('buyer')->attempt($credentials)) {
            // Check if account is frozen
            $buyer = Auth::guard('buyer')->user();
            if ($buyer && $buyer->is_frozen) {
                Auth::guard('buyer')->logout();
                return back()->withErrors([
                    'bp_code' => 'Your account has been frozen. Please contact the Super Admin.',
                ]);
            }
            
            $request->session()->regenerate();
            return redirect()->intended(route('buyer.dashboard'));
        }

        return back()->withErrors([
            'bp_code' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('buyer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}