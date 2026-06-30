<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageSwitchController extends Controller
{
    /**
     * Supported locale → label map.
     */
    protected array $supported = [
        'en' => 'English',
        'te' => 'Telugu',
        'ta' => 'Tamil',
        'kn' => 'Kannada',
        'hi' => 'Hindi',
    ];

    /**
     * Switch the application locale and redirect back.
     */
    public function switch(Request $request, string $locale)
    {
        if (!array_key_exists($locale, $this->supported)) {
            abort(400, 'Unsupported language.');
        }

        Session::put('locale', $locale);

        return redirect()->back()->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
