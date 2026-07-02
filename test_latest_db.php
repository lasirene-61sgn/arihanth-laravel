<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$workOrders = App\Models\WorkOrder::orderBy('id', 'desc')->take(10)->get();
foreach($workOrders as $wo) {
    echo "WO ID: {$wo->id}\n";
    echo "  Cat ID: " . var_export($wo->product_category_id, true) . "\n";
    echo "  Cat Name: " . var_export($wo->product_category, true) . "\n";
    echo "  Sub ID: " . var_export($wo->subcategory_id, true) . "\n";
    echo "  Sub Name: " . var_export($wo->subcategory, true) . "\n\n";
}
