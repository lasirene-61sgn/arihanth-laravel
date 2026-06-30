<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade; 
use Illuminate\Support\Facades\Auth;  
use Illuminate\Support\Facades\URL;   // 🛠️ Added for URL forcing
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 🛠️ SAFE HTTPS FORCING FOR NGROK PROXY TUNNELS
        // This stops the "Unsupported SSL request" spam error in php artisan serve
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();

        // Your existing middleware registration
        $this->app['router']->aliasMiddleware('check.account.frozen', \App\Http\Middleware\CheckAccountFrozen::class);

        // NEW: Safe Permission Check for Super Admin
        Blade::if('superAdminCan', function ($permission) {
            // This returns false if user is null or doesn't have the permission
            // It prevents the "Call to a member function on null" crash!
            return Auth::guard('super_admin')->check() &&
                Auth::guard('super_admin')->user()->hasPermission($permission);
        });

        // Sidebar Counts View Composer
        view()->composer(
            [
                'admin.layouts.app',
                'super-admin.layouts.app',
                'buyer.layouts.app',
                'craftsman.layouts.app',
                'key-user.layouts.app',
                'user.layouts.app'
            ],
            \App\Http\View\Composers\SidebarCountComposer::class
        );

        // Login Tracking for IP and Location
        \Illuminate\Support\Facades\Event::listen(function (\Illuminate\Auth\Events\Login $event) {
            $user = $event->user;
            $ip = request()->ip();
            $country = $user->last_login_country;
            $location = $user->last_login_location;
            $lat = request()->input('latitude');
            $lon = request()->input('longitude');

            if ($lat && $lon) {
                // Use GPS for precise location
                try {
                    $ch = curl_init("https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lon}&zoom=10&addressdetails=1");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'ArihanthJewellersERP/1.0');
                    $response = curl_exec($ch);
                    curl_close($ch);

                    if ($response) {
                        $data = json_decode($response, true);
                        if (isset($data['address'])) {
                            $country = $data['address']['country'] ?? null;
                            $city = $data['address']['city'] ?? $data['address']['town'] ?? $data['address']['county'] ?? '';
                            $state = $data['address']['state'] ?? '';
                            $location = trim($city . ', ' . $state, ', ');
                            $user->last_login_country = $country;
                            $user->last_login_location = $location;
                        }
                    }
                } catch (\Exception $e) {
                    // Fail silently
                }
            } else if ($ip && $ip !== '127.0.0.1' && ($user->last_login_ip !== $ip || empty($country))) {
                // Check if we already have this IP cached in our logs to save API calls
                $cachedLog = \App\Models\LoginLog::where('ip_address', $ip)->whereNotNull('country')->first();
                if ($cachedLog) {
                    $country = $cachedLog->country;
                    $location = $cachedLog->location;
                    $user->last_login_country = $country;
                    $user->last_login_location = $location;
                } else {
                    try {
                        $ch = curl_init('http://ipwho.is/' . $ip);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                        $response = curl_exec($ch);
                        curl_close($ch);
                        
                        if ($response) {
                            $data = json_decode($response, true);
                            if (isset($data['success']) && $data['success']) {
                                $country = $data['country'] ?? null;
                                $location = trim(($data['city'] ?? '') . ', ' . ($data['region'] ?? ''), ', ');
                                $user->last_login_country = $country;
                                $user->last_login_location = $location;
                            }
                        }
                    } catch (\Exception $e) {
                        // Fail silently if API fails
                    }
                }
            }
            
            $user->last_login_ip = $ip;
            // Prevent touching 'updated_at' if possible, or just let it update. We'll use simple save
            if(method_exists($user, 'saveQuietly')) {
                $user->saveQuietly();
            } else {
                $user->timestamps = false;
                $user->save();
            }

            // Create login log
            \App\Models\LoginLog::create([
                'authenticatable_type' => get_class($user),
                'authenticatable_id' => $user->id,
                'ip_address' => $ip,
                'country' => $country,
                'location' => $location,
                'user_agent' => request()->userAgent()
            ]);
        });
    }
}