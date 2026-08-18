<?php
$file = 'd:\pulic_html\app\Http\Controllers\Admin\WorkOrderController.php';
$content = file_get_contents($file);

$search = "            if (\$craftsmanFilter) {
                \$query->where('allocated_craftsman_bp_code', \$craftsmanFilter);
            }";
            
$replace = $search . "

            if (\$designCodeFilter) {
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
            }

            if (\$returnFilter === 'returned') {
                \$query->where(function(\$q) {
                    \$q->where('admin_return_count', '>', 0)
                      ->orWhere('superadmin_return_count', '>', 0)
                      ->orWhereNotNull('return_note');
                });
            }";

if (strpos($content, "\$returnFilter === 'returned'") === false) {
    $content = str_replace($search, $replace, $content);
    file_put_contents($file, $content);
    echo "Updated Admin controller successfully.\n";
} else {
    echo "Logic already exists.\n";
}
