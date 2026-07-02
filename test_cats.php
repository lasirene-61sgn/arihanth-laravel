<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Admin Categories:\n";
$admin_categories = App\Models\ProductCategory::whereHas('products')
    ->orWhereHas('workOrders')
    ->orderBy('name')
    ->get();
echo "Count: " . $admin_categories->count() . "\n";
foreach($admin_categories as $c) { echo $c->id . " - " . $c->name . "\n"; }

echo "\nSuperAdmin Categories:\n";
$super_categories = App\Models\ProductCategory::whereIn('id', function ($query) {
    $query->select('product_category_id')
        ->from('products')
        ->whereNull('bp_code');
})->orWhereIn('name', function ($query) {
    $query->select('product_category')
        ->from('work_orders')
        ->whereNull('bp_code');
})->orderBy('name')->get();
echo "Count: " . $super_categories->count() . "\n";
foreach($super_categories as $c) { echo $c->id . " - " . $c->name . "\n"; }

$wo = App\Models\WorkOrder::find(1247);
echo "\nWO 1247 Category ID: " . $wo->product_category_id . "\n";
