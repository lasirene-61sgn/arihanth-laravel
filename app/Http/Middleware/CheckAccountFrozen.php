<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountFrozen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if any authenticated user is frozen
        if (Auth::guard('admin')->check() && Auth::guard('admin')->user()->is_frozen) {
            Auth::guard('admin')->logout();
            return redirect('/account-frozen')->with('frozen_message', 'Your admin account has been frozen. Please contact the Super Admin.');
        }
        
        if (Auth::guard('process_owner')->check() && Auth::guard('process_owner')->user()->is_frozen) {
            Auth::guard('process_owner')->logout();
            return redirect('/account-frozen')->with('frozen_message', 'Your account has been frozen. Please contact the Super Admin.');
        }
        
        if (Auth::guard('super_admin')->check() && Auth::guard('super_admin')->user()->is_frozen) {
            Auth::guard('super_admin')->logout();
            return redirect('/account-frozen')->with('frozen_message', 'Your account has been frozen. Please contact the Super Admin.');
        }
        
        if (Auth::guard('buyer')->check() && Auth::guard('buyer')->user()->is_frozen) {
            Auth::guard('buyer')->logout();
            return redirect('/account-frozen')->with('frozen_message', 'Your account has been frozen. Please contact the Super Admin.');
        }
        
        if (Auth::guard('craftsman')->check() && Auth::guard('craftsman')->user()->is_frozen) {
            Auth::guard('craftsman')->logout();
            return redirect('/account-frozen')->with('frozen_message', 'Your account has been frozen. Please contact the Super Admin.');
        }
        
        if (Auth::guard('craftsman_staff')->check() && !Auth::guard('craftsman_staff')->user()->is_active) {
            Auth::guard('craftsman_staff')->logout();
            return redirect('/account-frozen')->with('frozen_message', 'Your account has been frozen. Please contact the Super Admin.');
        }
        
        if (Auth::guard('key_user')->check() && Auth::guard('key_user')->user()->is_frozen) {
            Auth::guard('key_user')->logout();
            return redirect('/account-frozen')->with('frozen_message', 'Your account has been frozen. Please contact the Super Admin.');
        }
        
        if (Auth::guard('web')->check() && Auth::guard('web')->user()->is_frozen) {
            Auth::guard('web')->logout();
            return redirect('/account-frozen')->with('frozen_message', 'Your account has been frozen. Please contact the Super Admin.');
        }
        
        return $next($request);
    }
}
