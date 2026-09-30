<?php
$grouped = \App\Models\Craftman::getGroupedApiPermissions();
$defaults = \App\Models\Craftman::getDefaultApiPermissions();

$allGrouped = [];
foreach ($grouped as $tab => $perms) {
    $allGrouped = array_merge($allGrouped, $perms);
}

foreach ($defaults as $def) {
    if (!in_array($def, $allGrouped)) {
        echo "Missing from grouped: " . $def . "\n";
    }
}
echo "Done checking missing.\n";
