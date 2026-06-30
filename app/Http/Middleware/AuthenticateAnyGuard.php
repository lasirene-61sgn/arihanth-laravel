<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAnyGuard
{
    /**
     * The guards to check for authentication.
     */
    protected array $guards = [
        'super_admin',
        'admin',
        'web',
        'buyer',
        'craftsman',
        'key_user',
    ];

    /**
     * Handle an incoming request.
     * Checks all configured guards and allows access if the user is authenticated
     * via ANY one of them. Sets the default guard to the one that authenticated
     * the user so that auth()->user() works correctly in the controller.
     */
    public function handle(Request $request, Closure $next): Response
    {
        foreach ($this->guards as $guard) {
            if (auth()->guard($guard)->check()) {
                // Set the default guard so auth()->user() resolves correctly in the controller
                auth()->shouldUse($guard);

                return $next($request);
            }
        }

        // No guard authenticated the user — redirect to login
        return redirect()->route('process-owner.login');
    }
}
