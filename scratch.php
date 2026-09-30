<?php
$file = 'd:/pulic_html/app/Models/Craftman.php';
$content = file_get_contents($file);

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$grouped = \App\Models\Craftman::getGroupedApiPermissions();
$defaults = \App\Models\Craftman::getDefaultApiPermissions();

$code = "public static function getGroupedApiPermissions()\n    {\n        return [\n";

foreach ($grouped as $tab => $perms) {
    $code .= "            '{$tab}' => [\n";
    foreach ($perms as $perm) {
        if (in_array($perm, $defaults)) {
            $code .= "                '{$perm}',\n";
        }
    }
    $code .= "            ],\n";
}
$code .= "        ];\n    }";

$content = preg_replace('/public static function getGroupedApiPermissions\(\)\s*\{.*?\n    \}/s', $code, $content);

file_put_contents($file, $content);
echo "Updated Craftsman getGroupedApiPermissions.\n";
