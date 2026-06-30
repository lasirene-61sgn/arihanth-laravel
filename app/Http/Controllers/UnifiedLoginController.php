<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\ProcessOwner;
use App\Models\Buyer;
use App\Models\Craftman;
use App\Models\KeyUser;
use App\Models\User;

class UnifiedLoginController extends Controller
{
    /**
     * SUCCESS: This method was missing, causing your "undefined method" error.
     */
    public function showLoginForm()
    {
        // Check if user is already logged in to any guard
        if (Auth::guard('super_admin')->check()) return redirect()->route('super-admin.dashboard');
        if (Auth::guard('admin')->check()) return redirect()->route('admin.dashboard');
        if (Auth::guard('buyer')->check()) return redirect()->route('buyer.dashboard');
        if (Auth::guard('craftsman')->check()) return redirect()->route('craftsman.dashboard');
        if (Auth::guard('key_user')->check()) return redirect()->route('key-user.dashboard');
        if (Auth::guard('web')->check()) return redirect()->route('user.dashboard');

        return view('unified-login');
    }

    /**
     * Handle the unified login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $login    = trim($request->input('login'));
        $password = $request->input('password');
        $isEmail  = filter_var($login, FILTER_VALIDATE_EMAIL);

        // ── 1. SuperAdmin & Admin (ProcessOwner) ───────────────────────────
        $processOwner = ProcessOwner::where(function ($q) use ($login, $isEmail) {
            $q->where('user_code', $login);
            // Only check 'email' column if input is an email to avoid SQL errors
            if ($isEmail) {
                $q->orWhere('email', $login); 
            }
        })->first();

        if ($processOwner && Hash::check($password, $processOwner->password)) {
            if ($processOwner->is_frozen) return $this->frozenResponse();
            
            $processOwner->update(['last_login_ip' => $request->ip()]);
            
            if ($processOwner->role === 'super_admin') {
                Auth::guard('super_admin')->login($processOwner);
                $request->session()->regenerate();
                return redirect()->route('super-admin.dashboard');
            } elseif ($processOwner->role === 'admin') {
                Auth::guard('admin')->login($processOwner);
                $request->session()->regenerate();
                return redirect()->route('admin.dashboard');
            }
        }

        // ── 2. Buyer ──────────────────────────────────────────────────────
        $buyer = Buyer::where(function ($q) use ($login, $isEmail) {
            $q->where('bp_code', $login);
            if ($isEmail) $q->orWhere('email', $login);
        })->first();

        if ($buyer && Hash::check($password, $buyer->password)) {
            if ($buyer->is_frozen) return $this->frozenResponse();
            $buyer->update(['last_login_ip' => $request->ip()]);
            Auth::guard('buyer')->login($buyer);
            $request->session()->regenerate();
            return redirect()->route('buyer.dashboard');
        }

        // ── 3. Craftsman ──────────────────────────────────────────────────
        $craftsman = Craftman::where(function ($q) use ($login, $isEmail) {
            $q->where('craftman_code', $login);
            if ($isEmail) $q->orWhere('email', $login);
        })->first();

        if ($craftsman && Hash::check($password, $craftsman->password)) {
            if ($craftsman->is_frozen) return $this->frozenResponse();
            $craftsman->update(['last_login_ip' => $request->ip()]);
            Auth::guard('craftsman')->login($craftsman);
            $request->session()->regenerate();
            return redirect()->route('craftsman.dashboard');
        }

        // ── 4. Key User ───────────────────────────────────────────────────
        $keyUser = KeyUser::where(function ($q) use ($login, $isEmail) {
            $q->where('user_code', $login);
            if ($isEmail) $q->orWhere('email', $login); 
        })->first();

        if ($keyUser && Hash::check($password, $keyUser->password)) {
            if ($keyUser->is_frozen) return $this->frozenResponse();
            $keyUser->update(['last_login_ip' => $request->ip()]);
            Auth::guard('key_user')->login($keyUser);
            $request->session()->regenerate();
            return redirect()->route('key-user.dashboard');
        }

        return back()->withErrors([
            'login' => 'Invalid credentials. Please check your ID / email and password.',
        ])->withInput($request->only('login'));
    }

    private function frozenResponse()
    {
        return back()->withErrors(['login' => 'Your account is frozen. Contact the system administrator.'])->withInput();
    }
}