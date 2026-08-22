<?php
$c = file_get_contents('d:/pulic_html/app/Http/Controllers/CraftsmanStaff/PurchaseOrderController.php');
echo (strpos($c, "'overdueOrders'") !== false ? 'FOUND' : 'NOT FOUND');

// also, let's fix it if it's missing just to be safe:
if (strpos($c, "'overdueOrders'") === false) {
    // replace `compact(\n            'allocatedOrders'` with `compact(\n            'overdueOrders',\n            'allocatedOrders'` using regex to handle whitespace
    $c = preg_replace("/compact\(\s*'allocatedOrders',/", "compact(\n            'overdueOrders',\n            'allocatedOrders',", $c);
    file_put_contents('d:/pulic_html/app/Http/Controllers/CraftsmanStaff/PurchaseOrderController.php', $c);
    echo " -> FIXED";
}
