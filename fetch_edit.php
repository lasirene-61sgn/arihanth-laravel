<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$workOrder = App\Models\WorkOrder::find(1243);
$categories = App\Models\ProductCategory::all();
$buyers = App\Models\Buyer::all(); // Assuming Buyer is the model
$errors = new \Illuminate\Support\MessageBag();
session()->put('errors', $errors);

try {
    $html = view('admin.work-order.edit', compact('workOrder', 'categories', 'buyers'))->withErrors($errors)->render();
    file_put_contents('test_edit.html', $html);
    echo "Saved to test_edit.html\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
