<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::guard('process_owner')->check() || Auth::guard('process_owner')->user()->role !== 'super_admin') {
            return redirect()->route('super-admin.login');
        }

        return $next($request);
    }
}