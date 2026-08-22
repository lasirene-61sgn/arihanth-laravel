<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
if ($user) Auth::login($user);

$controller = new \App\Http\Controllers\API\Common\WorkOrderController();

// Find an older work order that was manually created (not imported)
$wo = App\Models\WorkOrder::whereNotNull('product_image')
    ->whereDoesntHave('product') // Maybe not from product?
    ->first();

if (!$wo) {
    $wo = App\Models\WorkOrder::whereNotNull('product_image')->first();
}

echo "Work Order ID: " . $wo->id . "\n";
echo "Product Image: " . $wo->product_image . "\n";

$reflection = new \ReflectionClass($controller);
$method = $reflection->getMethod('transformWorkOrderResponse');
$method->setAccessible(true);
$transformed = $method->invoke($controller, $wo);

print_r($transformed['gallery_images']);
print_r($transformed['images']);
