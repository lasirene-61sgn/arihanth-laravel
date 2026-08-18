<?php
$files = [
    'd:/pulic_html/app/Http/Controllers/Admin/DesignController.php',
    'd:/pulic_html/app/Http/Controllers/SuperAdmin/DesignController.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Add models to top
    if (strpos($content, 'use App\Models\Buyer;') === false) {
        $content = str_replace("use App\Models\Product;", "use App\Models\Product;\nuse App\Models\Buyer;\nuse App\Models\Craftman;\nuse App\Models\ProductSubcategory;", $content);
    }
    
    // Add fetching of buyers, craftsmen, categories, subcategories to index method
    if (strpos($content, '$buyers = Buyer::') === false) {
        $fetchCode = "
        \$buyers = Buyer::orderBy('business_name')->get();
        \$craftsmen = Craftman::orderBy('business_name')->get();
        \$subCategories = ProductSubcategory::orderBy('name')->get();
        \$categories = ProductCategory::orderBy('name')->get();
        ";
        
        $content = str_replace("return view(", $fetchCode . "\n        return view(", $content);
        $content = str_replace("compact('products', 'statusCounts')", "compact('products', 'statusCounts', 'buyers', 'craftsmen', 'subCategories', 'categories')", $content);
        // Sometimes it's different compact array
        $content = str_replace("compact('products', 'statusCounts', 'categories')", "compact('products', 'statusCounts', 'buyers', 'craftsmen', 'subCategories', 'categories')", $content);
        if (strpos($content, "compact('products', 'statusCounts', 'buyers'") === false) {
             // Let's just find compact('products' and replace
             // Handled above usually.
        }
    }
    
    // Add craftsman filter logic (bp_code already has filter_bp_code)
    // We just need to make sure filter_craftsman is added to query if present
    if (strpos($content, '$request->filled(\'filter_craftsman\')') === false) {
        $filterLogic = <<<EOT
        if (\$request->filled('filter_craftsman')) {
            // In Product model, craftsman code is often stored in bp_code for craftsman designs
            \$query->where('bp_code', 'like', '%' . \$request->filter_craftsman . '%');
            \$countQuery->where('bp_code', 'like', '%' . \$request->filter_craftsman . '%');
        }
        if (\$request->filled('filter_product_code')) {
            \$query->where('product_code', 'like', '%' . \$request->filter_product_code . '%');
            \$countQuery->where('product_code', 'like', '%' . \$request->filter_product_code . '%');
        }
EOT;
        // Inject after filter_bp_code
        $content = str_replace('if ($request->filled(\'filter_bp_code\')) $query->where(\'bp_code\', \'like\', \'%\' . $request->filter_bp_code . \'%\');', 'if ($request->filled(\'filter_bp_code\')) $query->where(\'bp_code\', \'like\', \'%\' . $request->filter_bp_code . \'%\');' . "\n" . $filterLogic, $content);
        
        // Sometimes it's written differently in SuperAdmin
        if (strpos($content, $filterLogic) === false) {
            $content = str_replace('if ($request->filled(\'filter_bp_code\')) {', 'if ($request->filled(\'filter_bp_code\')) {' . "\n" . $filterLogic, $content);
        }
    }
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
