<?php

$adminPath = 'e:/public_html/app/Http/Controllers/Admin/PurchaseOrderController.php';
$saPath = 'e:/public_html/app/Http/Controllers/SuperAdmin/PurchaseOrderController.php';

function updateController($path, $guardStr) {
    if (!file_exists($path)) return;
    $c = file_get_contents($path);

    // allocateStore
    $search = "'craftsman_status' => 'allocated'";
    $replace = "'craftsman_status' => 'allocated',\n            'allocated_by' => $guardStr";
    $c = str_replace($search, $replace, $c);

    // bulkAllocate (has same array) - but already replaced because we used str_replace globally for that string
    // Wait, let's make sure it's correct. 
    
    // approve
    $searchApprove = "->update(['status' => 'completed', 'craftsman_status' => 'completed']);";
    $replaceApprove = "->update(['status' => 'completed', 'craftsman_status' => 'completed', 'approved_by' => $guardStr]);";
    $c = str_replace($searchApprove, $replaceApprove, $c);
    
    // bulkApprove
    $searchBulkApprove = "'status' => 'completed',";
    $replaceBulkApprove = "'status' => 'completed',\n                'approved_by' => $guardStr,";
    // Let's use a regex to only replace the one inside bulkApprove/bulkComplete
    $c = preg_replace("/'status' => 'completed',/i", "'status' => 'completed',\n                'approved_by' => $guardStr,", $c);
    
    file_put_contents($path, $c);
}

updateController($adminPath, '\Auth::id()');
updateController($saPath, '\Auth::guard("super_admin")->id()');

echo "Updated controllers.\n";
