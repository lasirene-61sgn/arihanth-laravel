<?php
$adminPath = 'e:/public_html/app/Http/Controllers/Admin/PurchaseOrderController.php';
$saPath = 'e:/public_html/app/Http/Controllers/SuperAdmin/PurchaseOrderController.php';

function cleanDouble($path) {
    if (!file_exists($path)) return;
    $c = file_get_contents($path);
    $c = preg_replace("/'approved_by' => (.*?), 'craftsman_status' => 'completed', 'approved_by' => \\1/i", "'craftsman_status' => 'completed', 'approved_by' => $1", $c);
    file_put_contents($path, $c);
}

cleanDouble($adminPath);
cleanDouble($saPath);
echo "Cleaned up double keys.\n";
