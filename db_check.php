<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = ['buyers', 'catalogues', 'catalogs', 'catalogue', 'designs', 'products', 'work_orders', 'purchase_orders', 'craftmen', 'key_users', 'craftsman_staff', 'stock_orders', 'repairs'];
foreach ($tables as $tableName) {
    echo "\nTable: $tableName\n";
    try {
        foreach(Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM $tableName") as $col) {
            echo $col->Field . ', ';
        }
    } catch (\Exception $e) {
        echo 'NOT FOUND';
    }
}
