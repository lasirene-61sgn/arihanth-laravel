<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\Craftman::first();
echo "Testing with Craftman ID: " . $user->id . "\n";
echo "Permissions array: " . json_encode(is_string($user->permissions) ? json_decode($user->permissions, true) : $user->permissions) . "\n";

// Emulate checkPermission
$hasGlobalOrTabPerm = function($u, $perm) {
    if ($u->hasPermission($perm)) return true;
    $userPerms = is_string($u->permissions) ? json_decode($u->permissions, true) : ($u->permissions ?? []);
    if (!is_array($userPerms)) return false;
    foreach ($userPerms as $p) {
        if (str_ends_with($p, '_' . $perm)) return true;
    }
    return false;
};

$res1 = $hasGlobalOrTabPerm($user, 'wo_accept');
$res2 = $hasGlobalOrTabPerm($user, 'wo_bulk_accept');
echo "wo_accept: " . ($res1 ? "TRUE" : "FALSE") . "\n";
echo "wo_bulk_accept: " . ($res2 ? "TRUE" : "FALSE") . "\n";

echo "Now trying CraftsmanStaff...\n";
$staff = \App\Models\CraftsmanStaff::first();
if ($staff) {
    echo "Permissions array: " . json_encode(is_string($staff->permissions) ? json_decode($staff->permissions, true) : $staff->permissions) . "\n";
    $res1 = $hasGlobalOrTabPerm($staff, 'wo_accept');
    $res2 = $hasGlobalOrTabPerm($staff, 'wo_bulk_accept');
    echo "wo_accept: " . ($res1 ? "TRUE" : "FALSE") . "\n";
    echo "wo_bulk_accept: " . ($res2 ? "TRUE" : "FALSE") . "\n";
}

