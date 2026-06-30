<?php

// Clear PHP OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<h2>✅ OPcache flushed</h2>";
} else {
    echo "<h2>⚠️ OPcache not available</h2>";
}

// Clear Laravel caches
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Artisan::call('route:clear');
Artisan::call('cache:clear');
Artisan::call('config:clear');

echo "<p>Laravel caches cleared.</p>";

// Show if route exists
$router = app('router');
$routes = collect($router->getRoutes())->map(function ($route) {
    return [
        'method' => implode('|', $route->methods()),
        'uri'    => $route->uri(),
        'name'   => $route->getName(),
        'action' => $route->getActionName(),
    ];
})->filter(function ($route) {
    return str_contains($route['uri'], 'designs') && str_contains($route['uri'], 'favourite');
});

echo "<h3>Matching routes:</h3><pre>";
print_r($routes->toArray());
echo "</pre>";
