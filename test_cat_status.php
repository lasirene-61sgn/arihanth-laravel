<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cats = App\Models\ProductCategory::orderBy('id', 'desc')->take(2)->get();
foreach($cats as $c) {
    echo "ID: " . $c->id . " Status: " . ($c->status ?? 'NULL') . "\n";
}
