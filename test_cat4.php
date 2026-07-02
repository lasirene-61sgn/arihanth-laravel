<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$subs = App\Models\ProductSubcategory::where('product_category_id', 4)->get();
foreach($subs as $s) {
    echo "ID: {$s->id}, Name: {$s->name}\n";
}
