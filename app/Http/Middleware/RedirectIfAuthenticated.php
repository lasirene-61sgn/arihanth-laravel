<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Redirect based on the authenticated user's role
                if ($guard === 'process_owner') {
                    $user = Auth::guard($guard)->user();
                    if ($user->role === 'super_admin') {
                        return redirect()->route('super-admin.dashboard');
                    } elseif ($user->role === 'admin') {
                        return redirect()->route('admin.dashboard');
                    }
                } elseif ($guard === 'craftsman') {
                    return redirect()->route('craftsman.dashboard');
                }
                
                // Default redirect
                return redirect('/');
            }
        }

        return $next($request);
    }
}