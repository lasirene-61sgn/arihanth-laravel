<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$workOrders = App\Models\WorkOrder::orderBy('id', 'desc')->take(5)->get();
foreach($workOrders as $wo) {
    echo "WO ID: {$wo->id}\n";
    echo "  Cat ID: " . json_encode($wo->product_category_id) . "\n";
    echo "  Cat Name: " . json_encode($wo->product_category) . "\n";
    echo "  Sub ID: " . json_encode($wo->subcategory_id) . "\n";
    echo "  Sub Name: " . json_encode($wo->subcategory) . "\n\n";
}
