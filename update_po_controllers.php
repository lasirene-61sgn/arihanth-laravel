<?php
$file = 'd:/pulic_html/app/Http/Controllers/Admin/PurchaseOrderController.php';
$content = file_get_contents($file);

// 1. Add categories and subcategories to compact and fetching
$insertPoint = strpos($content, "return view('admin.purchase-order.index'");
if ($insertPoint !== false) {
    $categoriesCode = "\n        \$categories = ProductCategory::orderBy('name')->get();\n        \$subCategories = ProductSubcategory::orderBy('name')->get();\n\n        ";
    $content = substr_replace($content, $categoriesCode, $insertPoint, 0);
}

$content = str_replace("'craftsmen'", "'craftsmen', 'categories', 'subCategories'", $content);

// 2. Add filter params
$insertPoint2 = strpos($content, '$filterDesignCode = $request->get(\'filter_design_code\');');
if ($insertPoint2 !== false) {
    $filterCode = "\$filterDesignCode = \$request->get('filter_design_code');\n        \$filterCategory = \$request->get('category_filter');\n        \$filterSubCategory = \$request->get('sub_category_filter');";
    $content = substr_replace($content, $filterCode, $insertPoint2, strlen('$filterDesignCode = $request->get(\'filter_design_code\');'));
}

// 3. Apply category filters
$insertPoint3 = strpos($content, 'if ($filterDesignCode) {');
if ($insertPoint3 !== false) {
    $catFilter = <<<EOT
if (\$filterCategory) {
            \$createdOrdersQuery->whereRaw("JSON_CONTAINS(items, ?, '$')", [json_encode(['category' => (int)\$filterCategory])]);
            \$allocatedOrdersQuery->whereRaw("JSON_CONTAINS(items, ?, '$')", [json_encode(['category' => (int)\$filterCategory])]);
            \$inProcessOrdersQuery->whereRaw("JSON_CONTAINS(items, ?, '$')", [json_encode(['category' => (int)\$filterCategory])]);
            \$forApprovalOrdersQuery->whereRaw("JSON_CONTAINS(items, ?, '$')", [json_encode(['category' => (int)\$filterCategory])]);
            \$completedOrdersQuery->whereRaw("JSON_CONTAINS(items, ?, '$')", [json_encode(['category' => (int)\$filterCategory])]);
            \$rejectedOrdersQuery->whereRaw("JSON_CONTAINS(items, ?, '$')", [json_encode(['category' => (int)\$filterCategory])]);
        }
        
        if (\$filterSubCategory) {
            \$createdOrdersQuery->whereRaw("JSON_CONTAINS(items, ?, '$')", [json_encode(['sub_category' => (int)\$filterSubCategory])]);
            \$allocatedOrdersQuery->whereRaw("JSON_CONTAINS(items, ?, '$')", [json_encode(['sub_category' => (int)\$filterSubCategory])]);
            \$inProcessOrdersQuery->whereRaw("JSON_CONTAINS(items, ?, '$')", [json_encode(['sub_category' => (int)\$filterSubCategory])]);
            \$forApprovalOrdersQuery->whereRaw("JSON_CONTAINS(items, ?, '$')", [json_encode(['sub_category' => (int)\$filterSubCategory])]);
            \$completedOrdersQuery->whereRaw("JSON_CONTAINS(items, ?, '$')", [json_encode(['sub_category' => (int)\$filterSubCategory])]);
            \$rejectedOrdersQuery->whereRaw("JSON_CONTAINS(items, ?, '$')", [json_encode(['sub_category' => (int)\$filterSubCategory])]);
        }
        
        
EOT;
    $content = substr_replace($content, $catFilter, $insertPoint3, 0);
}

// 4. Fix overdue filter
$content = str_replace('if ($request->get(\'overdue\') == 1)', 'if ($request->get(\'overdue\') == 1 || $filterStatus == \'overdue\')', $content);

file_put_contents($file, $content);
echo "Admin Controller Updated.\n";

// SUPER ADMIN CONTROLLER
$fileSuper = 'd:/pulic_html/app/Http/Controllers/SuperAdmin/PurchaseOrderController.php';
$contentSuper = file_get_contents($fileSuper);

$insertPointSuper = strpos($contentSuper, '$categoryFilter = $request->get(\'category_filter\');');
if ($insertPointSuper !== false) {
    $filterCodeSuper = "\$categoryFilter = \$request->get('category_filter');\n        \$subCategoryFilter = \$request->get('sub_category_filter');\n        \$filterStatus = \$request->get('filter_status');";
    $contentSuper = substr_replace($contentSuper, $filterCodeSuper, $insertPointSuper, strlen('$categoryFilter = $request->get(\'category_filter\');'));
}

$insertPointSuper2 = strpos($contentSuper, 'if ($filterCraftsman) {');
if ($insertPointSuper2 !== false) {
    $subCatFilter = <<<EOT
if (\$subCategoryFilter) {
                \$query->whereRaw("JSON_CONTAINS(items, ?, '$')", [json_encode(['sub_category' => (int)\$subCategoryFilter])]);
            }
            
            if (\$filterStatus && \$filterStatus != 'overdue') {
                \$query->where('status', \$filterStatus); // Though tabs do this anyway, added just in case
            }
            
            
EOT;
    $contentSuper = substr_replace($contentSuper, $subCatFilter, $insertPointSuper2, 0);
}

// Fix overdue logic in SuperAdmin
$contentSuper = str_replace('if ($request->get(\'overdue\') == 1)', 'if ($request->get(\'overdue\') == 1 || $filterStatus == \'overdue\')', $contentSuper);

// Add subCategories to SuperAdmin compact
$insertPointSuper3 = strpos($contentSuper, '$categories = ProductCategory::orderBy(\'name\')->get();');
if ($insertPointSuper3 !== false) {
    $contentSuper = substr_replace($contentSuper, "\$categories = ProductCategory::orderBy('name')->get();\n        \$subCategories = ProductSubcategory::orderBy('name')->get();", $insertPointSuper3, strlen('$categories = ProductCategory::orderBy(\'name\')->get();'));
}
$contentSuper = str_replace("'categories',", "'categories',\n            'subCategories',", $contentSuper);

file_put_contents($fileSuper, $contentSuper);
echo "SuperAdmin Controller Updated.\n";
