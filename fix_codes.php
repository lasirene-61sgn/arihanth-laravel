<?php

$paths = [
    'd:\pulic_html\app\Http\Controllers\Admin\WorkOrderController.php',
    'd:\pulic_html\app\Http\Controllers\SuperAdmin\WorkOrderController.php'
];

foreach ($paths as $path) {
    if (!file_exists($path)) continue;
    $content = file_get_contents($path);
    
    // Replace the assignments
    $old_assignment = "DB::table('work_orders')->whereNotNull('design_code')->where('design_code', '!=', '')->distinct()->orderBy('design_code')->pluck('design_code');";
    $new_assignment = "DB::table('products')->whereNotNull('design_code')->where('design_code', '!=', '')->distinct()->orderBy('design_code')->pluck('design_code');";
    $content = str_replace($old_assignment, $new_assignment, $content);
    
    $old_assignment2 = "DB::table('work_orders')->whereNotNull('product_code')->where('product_code', '!=', '')->distinct()->orderBy('product_code')->pluck('product_code');";
    $new_assignment2 = "DB::table('products')->whereNotNull('product_code')->where('product_code', '!=', '')->distinct()->orderBy('product_code')->pluck('product_code');";
    $content = str_replace($old_assignment2, $new_assignment2, $content);
    
    // Replace the filter logic.
    // Original filter logic (if it exists):
    // if ($designCodeFilter) $query->where('design_code', $designCodeFilter);
    // if ($productCodeFilter) $query->where('product_code', $productCodeFilter);
    
    // We want to replace it with a closure that searches both.
    $old_filter = "            if (\$designCodeFilter) \$query->where('design_code', \$designCodeFilter);\n            if (\$productCodeFilter) \$query->where('product_code', \$productCodeFilter);";
    $new_filter = "            if (\$designCodeFilter) {
                \$query->where(function(\$q) use (\$designCodeFilter) {
                    \$q->where('design_code', \$designCodeFilter)
                      ->orWhere('product_code', \$designCodeFilter);
                });
            }
            if (\$productCodeFilter) {
                \$query->where(function(\$q) use (\$productCodeFilter) {
                    \$q->where('product_code', \$productCodeFilter)
                      ->orWhere('design_code', \$productCodeFilter);
                });
            }";
    
    $content = str_replace($old_filter, $new_filter, $content);
    
    file_put_contents($path, $content);
    echo "Updated $path\n";
}
