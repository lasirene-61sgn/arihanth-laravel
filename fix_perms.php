<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$map = [
    'work_order' => ['wo_view', 'wo_accept', 'wo_reject'],
    'purchase_order' => ['po_view', 'po_accept', 'po_reject'],
    'repair' => ['repair_view', 'repair_accept', 'repair_reject'],
    'product' => ['product_view', 'product_create', 'product_edit'],
    'design' => ['design_view'],
    'catalogue' => ['catalogue_view'],
];
$craftsmen = \App\Models\Craftman::all();
foreach ($craftsmen as $craftsman) {
    $perms = $craftsman->getPermissionsArray();
    $newPerms = [];
    foreach ($perms as $perm) {
        $found = false;
        foreach ($map as $oldPerm => $newPermArray) {
            if (in_array($perm, $newPermArray)) {
                $newPerms[] = $oldPerm;
                $found = true;
            }
        }
        if (!$found) {
            $newPerms[] = $perm;
        }
    }
    $craftsman->permissions = array_values(array_unique($newPerms));
    $craftsman->save();
}
echo "done\n";
