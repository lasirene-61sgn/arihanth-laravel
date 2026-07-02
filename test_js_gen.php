<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$workOrder = App\Models\WorkOrder::find(1247);
$js = view('admin.work-order.edit', [
    'workOrder' => $workOrder, 
    'categories' => App\Models\ProductCategory::all(), 
    'buyers' => collect([])
])->render();
// Extract the initialization script part
preg_match('/function initializeSubcategory\(\).*?^\s*}/sm', $js, $matches);
echo "JS for WO 1247:\n";
echo $matches[0] ?? "Not found";
