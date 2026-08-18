<?php
$files = [
    'd:/pulic_html/app/Http/Controllers/Admin/CatalogueController.php',
    'd:/pulic_html/app/Http/Controllers/SuperAdmin/CatalogueController.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Add models to top
    if (strpos($content, 'use App\Models\Buyer;') === false) {
        $content = str_replace("use App\Models\Product;", "use App\Models\Product;\nuse App\Models\Buyer;\nuse App\Models\Craftman;\nuse App\Models\ProductCategory;\nuse App\Models\ProductSubcategory;", $content);
    }
    
    // Add fetching to index method
    if (strpos($content, '$buyers = Buyer::') === false) {
        $fetchCode = "
        \$buyers = Buyer::orderBy('business_name')->get();
        \$craftsmen = Craftman::orderBy('business_name')->get();
        \$subCategories = ProductSubcategory::orderBy('name')->get();
        \$categories = ProductCategory::orderBy('name')->get();
        ";
        
        $content = str_replace("return view(", $fetchCode . "\n        return view(", $content);
        $content = str_replace("compact('products')", "compact('products', 'buyers', 'craftsmen', 'subCategories', 'categories')", $content);
    }
    
    // Add filter_craftsman and filter_design_code logic
    if (strpos($content, '$request->filled(\'filter_craftsman\')') === false) {
        $filterLogic = <<<EOT
    if (\$request->filled('filter_craftsman')) {
        \$query->where('bp_code', 'like', '%' . \$request->filter_craftsman . '%');
    }
    if (\$request->filled('filter_design_code')) {
        \$query->where('design_code', 'like', '%' . \$request->filter_design_code . '%');
    }
    if (\$request->filled('filter_product_code')) {
        \$query->where('product_code', 'like', '%' . \$request->filter_product_code . '%');
    }
EOT;
        // Inject after bp_code filter
        $content = str_replace("if (\$request->filled('bp_code')) {\n        \$query->where('bp_code', 'like', '%' . \$request->bp_code . '%');\n    }", "if (\$request->filled('bp_code')) {\n        \$query->where('bp_code', 'like', '%' . \$request->bp_code . '%');\n    }\n" . $filterLogic, $content);
        
        // Sometimes it's filter_bp_code
        if (strpos($content, $filterLogic) === false) {
            $content = str_replace("if (\$request->filled('filter_bp_code')) {\n        \$query->where('bp_code', 'like', '%' . \$request->filter_bp_code . '%');\n    }", "if (\$request->filled('filter_bp_code')) {\n        \$query->where('bp_code', 'like', '%' . \$request->filter_bp_code . '%');\n    }\n" . $filterLogic, $content);
        }
    }
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
