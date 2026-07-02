<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$workOrder = App\Models\WorkOrder::find(1243);
$categories = App\Models\ProductCategory::all();

echo "WorkOrder Cat ID: " . $workOrder->product_category_id . "\n";
echo "WorkOrder Cat Name: " . $workOrder->product_category . "\n";

foreach($categories as $category) {
    if ($workOrder->product_category_id == $category->id || $workOrder->product_category == $category->name) {
        echo "Selected: " . $category->name . " (ID: " . $category->id . ")\n";
    }
}
