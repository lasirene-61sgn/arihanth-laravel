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
    
    $searchStr = '$completedOrdersQuery = PurchaseOrder::where(\'status\', \'completed\');';
    
    $replaceStr = '$completedOrdersQuery = PurchaseOrder::where(\'status\', \'completed\');
        $completed_filter = $request->get(\'completed_filter\');
        if ($completed_filter == \'day\') {
            $completedOrdersQuery->whereDate(\'updated_at\', now()->toDateString());
        } elseif ($completed_filter == \'week\') {
            $completedOrdersQuery->whereBetween(\'updated_at\', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($completed_filter == \'month\') {
            $completedOrdersQuery->whereMonth(\'updated_at\', now()->month)->whereYear(\'updated_at\', now()->year);
        }';
        
    $c = str_replace(str_replace("\n", "\r\n", $searchStr), str_replace("\n", "\r\n", $replaceStr), $c);
    $c = str_replace($searchStr, $replaceStr, $c);

    file_put_contents($path, $c);
    echo "Updated controller for $guard\n";
}
