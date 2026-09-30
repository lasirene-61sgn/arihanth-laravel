<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WorkOrder;
$wo = WorkOrder::find(30);
if ($wo) {
    echo "ID: {$wo->id} | craftsman_status: {$wo->craftsman_status} | status: {$wo->status}\n";
} else {
    echo "Not found.";
}
