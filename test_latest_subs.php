<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$subs = App\Models\ProductSubcategory::orderBy('id', 'desc')->take(10)->get();
foreach($subs as $s) {
    echo "Sub ID: {$s->id}, Cat ID: {$s->product_category_id}, Name: {$s->name}\n";
}
