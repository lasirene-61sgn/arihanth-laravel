<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$wo = App\Models\WorkOrder::whereNotNull('product_image')
    ->orWhereHas('images')
    ->latest()
    ->take(5)
    ->get();

foreach ($wo as $w) {
    echo "WO: " . $w->work_order_number . "\n";
    echo "product_image: " . $w->product_image . "\n";
    echo "product_image_url: " . $w->product_image_url . "\n";
    echo "preview_image_url: " . $w->preview_image_url . "\n";
    echo "gallery_images: " . json_encode($w->gallery_images) . "\n";
    echo "images count: " . $w->images->count() . "\n";
    echo "--------------------------\n";
}
