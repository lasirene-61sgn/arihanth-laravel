<?php
$path = "d:/pulic_html/app/Http/Controllers/API/Common/WorkOrderController.php";
$content = file_get_contents($path);

// Fix global permissions for Admin for-approval tab
// From: 'can_complete' => $p_complete, 'can_bulk_complete' => $p_bulk_complete
// To:   'can_approve' => $this->isAdmin($user), 'can_bulk_approve' => $this->isAdmin($user)
$content = str_replace(
    "'can_complete' => \$p_complete, 'can_bulk_complete' => \$p_bulk_complete", 
    "'can_approve' => \$this->isAdmin(\$user), 'can_bulk_approve' => \$this->isAdmin(\$user)", 
    $content
);

// We should also make bulkApprove accept 'ids' just in case the frontend sends it generically.
$pattern = "/\\\$validator = Validator::make\(\\\$request->all\(\), \[\s*'work_order_ids'\s*=> 'required\|array',\s*'work_order_ids\.\*'\s*=> 'exists:work_orders,id',\s*\]\);/s";
$replacement = "\$workOrderIds = \$request->input('work_order_ids', \$request->input('ids'));
        \$request->merge(['work_order_ids' => \$workOrderIds]);
        
        \$validator = Validator::make(\$request->all(), [
            'work_order_ids'   => 'required|array',
            'work_order_ids.*' => 'exists:work_orders,id',
        ]);";
$content = preg_replace($pattern, $replacement, $content);

// And remove the magic logic from bulkCompleteWorkOrders and completeWorkOrder
// We can just replace the logic back to setting for_approval unconditionally.
$magic1 = "/if \(\\\$this->isAdmin\(\\\$user\) && \\\$workOrder->status === 'for_approval'\) \{\s*\\\$data\['status'\] = 'completed';\s*\}/s";
$content = preg_replace($magic1, "", $content);

file_put_contents($path, $content);
echo "Updated permissions and reverted magic logic.";
