<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$design = \App\Models\Design::first();
if ($design) {
    echo "Design image: " . $design->image . "\n";
    echo "Design image_url: " . $design->image_url . "\n";
    echo "isset(image_url): " . (isset($design->image_url) ? "TRUE" : "FALSE") . "\n";
    echo "empty(image_url): " . (empty($design->image_url) ? "TRUE" : "FALSE") . "\n";
}

$wo = \App\Models\WorkOrder::first();
if ($wo) {
    echo "\nWO preview_image_url: " . $wo->preview_image_url . "\n";
    echo "isset(preview_image_url): " . (isset($wo->preview_image_url) ? "TRUE" : "FALSE") . "\n";
}
