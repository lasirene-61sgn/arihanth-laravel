<?php

$paths = [
    'd:\pulic_html\app\Http\Controllers\Admin\WorkOrderController.php',
    'd:\pulic_html\app\Http\Controllers\SuperAdmin\WorkOrderController.php'
];

foreach ($paths as $path) {
    if (!file_exists($path)) continue;
    $content = file_get_contents($path);
    
    // Add variable
    if (strpos($content, '$returnFilter = $request->get(\'return_filter\');') === false) {
        $content = str_replace(
            '$productCodeFilter = $request->get(\'product_code_filter\');',
            "\$productCodeFilter = \$request->get('product_code_filter');\n        \$returnFilter = \$request->get('return_filter');",
            $content
        );
    }
    
    // Add query logic
    $filter_logic = "            if (\$productCodeFilter) {
                \$query->where(function(\$q) use (\$productCodeFilter) {
                    \$q->where('product_code', \$productCodeFilter)
                      ->orWhere('design_code', \$productCodeFilter);
                });
            }";
            
    $new_filter_logic = $filter_logic . "\n            if (\$returnFilter === 'returned') {
                \$query->where(function(\$q) {
                    \$q->where('admin_return_count', '>', 0)
                      ->orWhere('superadmin_return_count', '>', 0)
                      ->orWhereNotNull('return_note');
                });
            }";
            
    if (strpos($content, "\$returnFilter === 'returned'") === false) {
        $content = str_replace($filter_logic, $new_filter_logic, $content);
    }
    
    file_put_contents($path, $content);
    echo "Updated $path\n";
}
