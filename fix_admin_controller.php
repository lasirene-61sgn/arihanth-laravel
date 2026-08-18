<?php

$path = 'd:\pulic_html\app\Http\Controllers\Admin\WorkOrderController.php';
$content = file_get_contents($path);

$old_compact = "'craftsmanFilter',\n            'superAdmins'";
$new_compact = "'craftsmanFilter',\n            'designCodeFilter',\n            'productCodeFilter',\n            'designCodes',\n            'productCodes',\n            'superAdmins'";

$old_data = "        \$subcategories = [];\n        if (\$categoryFilter) {\n            \$subcategories = ProductSubcategory::where('product_category_id', \$categoryFilter)->orderBy('name')->get();\n        } else {\n            \$subcategories = ProductSubcategory::orderBy('name')->get();\n        }";
$new_data = $old_data . "\n\n        \$designCodes = DB::table('work_orders')->whereNotNull('design_code')->where('design_code', '!=', '')->distinct()->orderBy('design_code')->pluck('design_code');\n        \$productCodes = DB::table('work_orders')->whereNotNull('product_code')->where('product_code', '!=', '')->distinct()->orderBy('product_code')->pluck('product_code');";

$content = str_replace($old_compact, $new_compact, $content);
$content = str_replace($old_data, $new_data, $content);

file_put_contents($path, $content);
echo "Fixed Admin controller\n";
