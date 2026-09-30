<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WorkOrder;
$orders = WorkOrder::orderBy('id', 'desc')->take(10)->get();
foreach ($orders as $wo) {
    echo "ID: {$wo->id} | craftsman_status: {$wo->craftsman_status} | status: {$wo->status}\n";
}
