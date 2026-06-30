<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Supported locales.
     */
    protected array $supported = ['en', 'te', 'ta', 'kn', 'hi'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Check session
        $locale = Session::get('locale');

        // 2. Fallback to browser Accept-Language header (English if not matched)
        if (!$locale || !in_array($locale, $this->supported)) {
            $locale = 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
