<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Clear all caches
Artisan::call('route:clear');
Artisan::call('cache:clear');
Artisan::call('config:clear');
Artisan::call('view:clear');

echo "<h2>Cache Cleared Successfully!</h2>";
echo "<ul>";
echo "<li>Route cache: CLEARED</li>";
echo "<li>Application cache: CLEARED</li>";
echo "<li>Config cache: CLEARED</li>";
echo "<li>View cache: CLEARED</li>";
echo "</ul>";
echo "<p>You can now test the API again.</p>";
