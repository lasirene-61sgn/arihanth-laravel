<?php

namespace App\Http\Controllers\KeyUser;

use App\Http\Controllers\Controller;
use App\Models\KeyUser;
use App\Models\Buyer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form for key users.
     */
    public function showLoginForm()
    {
        return view('key-user.login');
    }

    /**
     * Handle login for key users and buyers.
     */
    public function login(Request $request)
    {
        $request->validate([
            'user_code' => 'required|string',
            'password' => 'required|string',
        ]);

        $userCode = $request->user_code;
        $password = $request->password;

        // First, try to authenticate as a key user
        $credentials = [
            'user_code' => $userCode,
            'password' => $password
        ];

        if (Auth::guard('key_user')->attempt($credentials)) {
            // Check if account is frozen
            $keyUser = Auth::guard('key_user')->user();
            if ($keyUser && $keyUser->is_frozen) {
                Auth::guard('key_user')->logout();
                throw ValidationException::withMessages([
                    'user_code' => 'Your account has been frozen. Please contact the Super Admin.',
                ]);
            }
            
            $request->session()->regenerate();
            // Debug: Log successful key user login
            Log::info('Key User Login Success - User Code: ' . $userCode);
            return redirect()->intended(route('key-user.dashboard'));
        }

        // If key user authentication fails, try to authenticate as a buyer
        // Buyers use their BP code as the login identifier
        $buyer = Buyer::where('bp_code', $userCode)->first();

        if ($buyer && Hash::check($password, $buyer->password)) {
            // Check if account is frozen
            if ($buyer->is_frozen) {
                throw ValidationException::withMessages([
                    'user_code' => 'Your account has been frozen. Please contact the Super Admin.',
                ]);
            }
            
            Auth::guard('buyer')->login($buyer);
            $request->session()->regenerate();
            // Debug: Log successful buyer login
            Log::info('Buyer Login Success - BP Code: ' . $userCode);
            return redirect()->intended(route('key-user.dashboard'));
        }

        throw ValidationException::withMessages([
            'user_code' => [trans('auth.failed')],
        ]);
    }

    /**
     * Show the key user dashboard.
     */
    public function dashboard()
    {
        // Check if it's a key user or buyer
        $keyUser = Auth::guard('key_user')->user();
        $buyer = Auth::guard('buyer')->user();
        
        $currentUser = $keyUser ?? $buyer;

        if (!$currentUser) {
            return redirect()->route('key-user.login');
        }

        $bpCode = $currentUser->bp_code;

        // Statistics filtered by BP Code
        $productsCount = \App\Models\Product::where('bp_code', $bpCode)->count();
        $designsCount = \App\Models\Product::where('bp_code', $bpCode)->count(); // Matching sidebar logic
        $cataloguesCount = \App\Models\Product::where('bp_code', $bpCode)
                                    ->where('design_status', 'Accepted')
                                    ->whereNotNull('design_code')
                                    ->count();
        $workOrdersCount = \App\Models\WorkOrder::where('bp_code', $bpCode)->count();
        $usersCount = \App\Models\User::where('bp_code', $bpCode)->count();

        return view('key-user.dashboard', [
            'keyUser' => $currentUser,
            'productsCount' => $productsCount,
            'designsCount' => $designsCount,
            'cataloguesCount' => $cataloguesCount,
            'workOrdersCount' => $workOrdersCount,
            'usersCount' => $usersCount,
        ]);
    }

    /**
     * Log out the key user or buyer.
     */
    public function logout(Request $request)
    {
        // Logout from both guards to ensure clean session
        Auth::guard('key_user')->logout();
        Auth::guard('buyer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('key-user.login');
    }

    /**
     * Show the key user finance page.
     */
    public function finance()
    {
        return view('key-user.finance.index');
    }
}