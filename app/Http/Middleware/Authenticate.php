<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Check which guard is being used and redirect accordingly
        if (! $request->expectsJson()) {

            // For buyer routes

            // For buyer routes
            if ($request->is('buyer*') || $request->is('api/buyer*')) {
                return route('buyer.login');
            }

            // For key user routes
            if ($request->is('key-user*') || $request->is('api/key-user*')) {
                return route('key-user.login');
            }

            // For user routes (regular users)
            if ($request->is('user*') || $request->is('api/user*')) {
                return route('user.login');
            }

            // For super admin routes
            if ($request->is('super-admin*') || $request->is('api/super-admin*')) {
                return route('super-admin.login');
            }
            
            // For admin routes
            if ($request->is('admin*') || $request->is('api/admin*')) {
                return route('admin.login');
            }
            
            // For craftsman routes
            if ($request->is('craftsman*') || $request->is('api/craftsman*')) {
                return route('craftsman.login');
            }
            
            // For process owner routes
            if ($request->is('process-owner*') || $request->is('api/process-owner*')) {
                return route('process-owner.login');
            }
            
            // Default fallback
            return route('process-owner.login');
        }
        
        return null;
    }
}