<?php
$files = [
    'admin' => "e:/public_html/app/Http/Controllers/Admin/PurchaseOrderController.php",
    'super_admin' => "e:/public_html/app/Http/Controllers/SuperAdmin/PurchaseOrderController.php"
];

foreach ($files as $guard => $path) {
    if (!file_exists($path)) {
        echo "File not found: $path\n";
        continue;
    }
    
    $c = file_get_contents($path);
    
    // 1. Update index method (Add completed_filter)
    $indexSearch = '$completedOrders = PurchaseOrder::where(\'status\', \'completed\')';
    $indexReplace = '$completed_filter = $request->get(\'completed_filter\');
        $completedOrdersQuery = PurchaseOrder::where(\'status\', \'completed\');
        if ($completed_filter == \'day\') {
            $completedOrdersQuery->whereDate(\'updated_at\', now()->toDateString());
        } elseif ($completed_filter == \'week\') {
            $completedOrdersQuery->whereBetween(\'updated_at\', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($completed_filter == \'month\') {
            $completedOrdersQuery->whereMonth(\'updated_at\', now()->month)->whereYear(\'updated_at\', now()->year);
        }
        
        $completedOrders = $completedOrdersQuery';
    $c = str_replace($indexSearch, $indexReplace, $c);

    // 2. Update store method (Add created_by and creator_type)
    $storeSearch = "'status'   => 'created',";
    $guardCode = ($guard == 'super_admin') ? "Auth::guard('super_admin')->id() ?? Auth::id()" : "Auth::id()";
    $storeReplace = "'status'   => 'created',
            'created_by' => $guardCode,
            'creator_type' => 'admin',";
    $c = str_replace($storeSearch, $storeReplace, $c);

    // 3. Update bulkAllocate method
    $bulkAllocSearch1 = "'allocated_craftsman_code' => \$request->craftsman_code,
            'craftsman_status' => 'allocated'";
    $bulkAllocReplace1 = "'allocated_craftsman_code' => \$request->craftsman_code,
            'craftsman_status' => 'allocated',
            'craftsman_due_date' => \$request->craftsman_due_date,
            'allocated_by' => $guardCode";
    $c = str_replace($bulkAllocSearch1, $bulkAllocReplace1, $c);
    
    // Also need to support craftsman_due_date in allocateStore
    $allocStoreSearch = '$purchaseOrder->update([
            \'allocated_craftsman_code\' => $request->craftsman_code,
            \'craftsman_status\' => \'allocated\'
        ]);';
    $allocStoreReplace = '$purchaseOrder->update([
            \'allocated_craftsman_code\' => $request->craftsman_code,
            \'craftsman_status\' => \'allocated\',
            \'craftsman_due_date\' => $request->craftsman_due_date,
            \'allocated_by\' => '.$guardCode.'
        ]);';
    $c = str_replace($allocStoreSearch, $allocStoreReplace, $c);

    // 4. Update approve method
    $approveSearch = '$purchaseOrder->update([
            \'status\' => \'approved\'
        ]);';
    $approveReplace = '$purchaseOrder->update([
            \'status\' => \'approved\',
            \'approved_by\' => '.$guardCode.'
        ]);';
    $c = str_replace($approveSearch, $approveReplace, $c);

    // 5. Update bulkApprove method
    $bulkApproveSearch = "PurchaseOrder::whereIn('id', \$request->order_ids)->update([
            'status' => 'approved'
        ]);";
    $bulkApproveReplace = "PurchaseOrder::whereIn('id', \$request->order_ids)->update([
            'status' => 'approved',
            'approved_by' => ".$guardCode."
        ]);";
    $c = str_replace($bulkApproveSearch, $bulkApproveReplace, $c);

    file_put_contents($path, $c);
    echo "Updated $guard PurchaseOrderController\n";
}
