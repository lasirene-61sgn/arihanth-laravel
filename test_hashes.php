<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "--- ImageHashes ---\n";
foreach (\App\Models\ImageHash::groupBy('hashable_type')->selectRaw('hashable_type, count(*) as count')->get() as $row) {
    echo $row->hashable_type . ": " . $row->count . "\n";
}
