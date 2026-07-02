<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$workOrders = App\Models\WorkOrder::orderBy('id', 'desc')->take(10)->get();
foreach($workOrders as $wo) {
    echo "ID: {$wo->id}, CatID: {$wo->product_category_id}, Cat: {$wo->product_category}, SubID: {$wo->subcategory_id}, Sub: {$wo->subcategory}\n";
}
