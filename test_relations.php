<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$wo = App\Models\WorkOrder::find(39);
echo "craftsman_staff_id: " . $wo->craftsman_staff_id . "\n";
echo "Staff relation: " . json_encode($wo->craftsmanStaff) . "\n";
echo "Accepted by staff id: " . $wo->accepted_by_staff_id . "\n";
echo "Accepted by Staff relation: " . json_encode($wo->acceptedByStaff) . "\n";
