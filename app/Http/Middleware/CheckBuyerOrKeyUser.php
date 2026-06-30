<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckBuyerOrKeyUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Debug: Log authentication status
        Log::info('CheckBuyerOrKeyUser - Key User Auth: ' . (Auth::guard('key_user')->check() ? 'Yes' : 'No') . ', Buyer Auth: ' . (Auth::guard('buyer')->check() ? 'Yes' : 'No'));
        
        if (Auth::guard('key_user')->check() || Auth::guard('buyer')->check()) {
            return $next($request);
        }
        
        return redirect()->route('buyer.login');
    }
}
