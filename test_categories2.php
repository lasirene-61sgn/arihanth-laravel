<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$categories = App\Models\ProductCategory::all();
foreach($categories as $c) {
    echo "ID: {$c->id}, Name: {$c->name}\n";
}
