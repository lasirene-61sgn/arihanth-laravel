<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$user = App\Models\ProcessOwner::first();
Auth::login($user);
$workOrder = App\Models\WorkOrder::find(1247);
$categories = App\Models\ProductCategory::all();
$buyers = App\Models\Buyer::all();
$errors = new \Illuminate\Support\MessageBag();
session()->put('errors', $errors);
try {
    $html = view('admin.work-order.edit', compact('workOrder', 'categories', 'buyers'))->withErrors($errors)->render();
    file_put_contents('test_admin_edit.html', $html);
    echo "Saved to test_admin_edit.html\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
