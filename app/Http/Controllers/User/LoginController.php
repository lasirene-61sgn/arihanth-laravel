<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show the login form for users.
     */
    public function showLoginForm()
    {
        return view('user.login');
    }

    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required',
        ]);

        $login = $request->input('login');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'user_code';

        $credentials = [
            $field => $login,
            'password' => $request->input('password'),
        ];

        if (Auth::guard('web')->attempt($credentials)) {
            // Check if account is frozen
            $user = Auth::guard('web')->user();
            if ($user && $user->is_frozen) {
                Auth::guard('web')->logout();
                return back()->withErrors([
                    'login' => 'Your account has been frozen. Please contact the Super Admin.',
                ])->onlyInput('login');
            }
            
            $request->session()->regenerate();
            return redirect()->intended(route('user.dashboard'));
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->onlyInput('login');
    }

    /**
     * Show the user dashboard.
     */
    public function dashboard()
    {
        $user = Auth::guard('web')->user();
        $bpCode = $user->bp_code;
        
        // Statistics filtered by BP Code to match Sidebar
        $totalWorkOrders = \App\Models\WorkOrder::where('bp_code', $bpCode)->count();
        $totalProducts = \App\Models\Product::where('bp_code', $bpCode)->count();
        $totalDesigns = \App\Models\Product::where('bp_code', $bpCode)->count();
        $acceptedProducts = \App\Models\Product::where('bp_code', $bpCode)
            ->where('design_status', 'Accepted')
            ->whereNotNull('design_code')
            ->count();
        
        return view('user.dashboard', [
            'totalWorkOrders' => $totalWorkOrders,
            'totalProducts' => $totalProducts,
            'totalDesigns' => $totalDesigns,
            'acceptedProducts' => $acceptedProducts
        ]);
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('user.login');
    }
}
