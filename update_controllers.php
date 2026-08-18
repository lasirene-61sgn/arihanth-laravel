<?php

function modifySuperAdminController() {
    $path = 'd:\pulic_html\app\Http\Controllers\SuperAdmin\WorkOrderController.php';
    $content = file_get_contents($path);

    $old_filters_get = "        \$craftsmanFilter = \$request->get('craftsman_filter');";
    $new_filters_get = "        \$craftsmanFilter = \$request->get('craftsman_filter');\n        \$designCodeFilter = \$request->get('design_code_filter');\n        \$productCodeFilter = \$request->get('product_code_filter');";

    $old_filters_apply = "            if (\$craftsmanFilter) \$query->where('allocated_craftsman_bp_code', \$craftsmanFilter);";
    $new_filters_apply = "            if (\$craftsmanFilter) \$query->where('allocated_craftsman_bp_code', \$craftsmanFilter);\n            if (\$designCodeFilter) \$query->where('design_code', \$designCodeFilter);\n            if (\$productCodeFilter) \$query->where('product_code', \$productCodeFilter);";

    $old_compact = "'craftsmanFilter',\n            'counts'";
    $new_compact = "'craftsmanFilter',\n            'designCodeFilter',\n            'productCodeFilter',\n            'designCodes',\n            'productCodes',\n            'counts'";

    $old_data = "        \$subcategories = [];\n        if (\$categoryFilter) {\n            \$subcategories = ProductSubcategory::where('product_category_id', \$categoryFilter)->orderBy('name')->get();\n        } else {\n            \$subcategories = ProductSubcategory::orderBy('name')->get();\n        }";
    $new_data = $old_data . "\n\n        \$designCodes = DB::table('work_orders')->whereNotNull('design_code')->where('design_code', '!=', '')->distinct()->orderBy('design_code')->pluck('design_code');\n        \$productCodes = DB::table('work_orders')->whereNotNull('product_code')->where('product_code', '!=', '')->distinct()->orderBy('product_code')->pluck('product_code');";

    $content = str_replace($old_filters_get, $new_filters_get, $content);
    $content = str_replace($old_filters_apply, $new_filters_apply, $content);
    $content = str_replace($old_compact, $new_compact, $content);
    $content = str_replace($old_data, $new_data, $content);

    file_put_contents($path, $content);
    echo "SuperAdmin Controller updated.\n";
}

function modifyAdminController() {
    $path = 'd:\pulic_html\app\Http\Controllers\Admin\WorkOrderController.php';
    if (!file_exists($path)) {
        echo "Admin Controller not found.\n";
        return;
    }
    
    $content = file_get_contents($path);

    $old_filters_get = "        \$craftsmanFilter = \$request->get('craftsman_filter');";
    $new_filters_get = "        \$craftsmanFilter = \$request->get('craftsman_filter');\n        \$designCodeFilter = \$request->get('design_code_filter');\n        \$productCodeFilter = \$request->get('product_code_filter');";

    $old_filters_apply = "            if (\$craftsmanFilter) \$query->where('allocated_craftsman_bp_code', \$craftsmanFilter);";
    $new_filters_apply = "            if (\$craftsmanFilter) \$query->where('allocated_craftsman_bp_code', \$craftsmanFilter);\n            if (\$designCodeFilter) \$query->where('design_code', \$designCodeFilter);\n            if (\$productCodeFilter) \$query->where('product_code', \$productCodeFilter);";

    $old_compact = "'craftsmanFilter',\n            'counts'";
    $new_compact = "'craftsmanFilter',\n            'designCodeFilter',\n            'productCodeFilter',\n            'designCodes',\n            'productCodes',\n            'counts'";

    $old_data = "        \$subcategories = [];\n        if (\$categoryFilter) {\n            \$subcategories = ProductSubcategory::where('product_category_id', \$categoryFilter)->orderBy('name')->get();\n        } else {\n            \$subcategories = ProductSubcategory::orderBy('name')->get();\n        }";
    $new_data = $old_data . "\n\n        \$designCodes = DB::table('work_orders')->whereNotNull('design_code')->where('design_code', '!=', '')->distinct()->orderBy('design_code')->pluck('design_code');\n        \$productCodes = DB::table('work_orders')->whereNotNull('product_code')->where('product_code', '!=', '')->distinct()->orderBy('product_code')->pluck('product_code');";

    $content = str_replace($old_filters_get, $new_filters_get, $content);
    $content = str_replace($old_filters_apply, $new_filters_apply, $content);
    $content = str_replace($old_compact, $new_compact, $content);
    
    if (strpos($content, '$designCodes =') === false) {
        $content = str_replace($old_data, $new_data, $content);
    }

    file_put_contents($path, $content);
    echo "Admin Controller updated.\n";
}

modifySuperAdminController();
modifyAdminController();
