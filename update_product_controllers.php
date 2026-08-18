<?php
$files = [
    'd:/pulic_html/app/Http/Controllers/Admin/ProductController.php',
    'd:/pulic_html/app/Http/Controllers/SuperAdmin/ProductController.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Add models to top
    if (strpos($content, 'use App\Models\Buyer;') === false) {
        $content = str_replace("use App\Models\Product;", "use App\Models\Product;\nuse App\Models\Buyer;\nuse App\Models\Craftman;", $content);
    }
    
    // Add fetching of buyers, craftsmen, categories, subcategories
    if (strpos($content, '$buyers = Buyer::') === false) {
        $fetchCode = "
        \$buyers = Buyer::orderBy('business_name')->get();
        \$craftsmen = Craftman::orderBy('business_name')->get();
        \$subCategories = ProductSubcategory::orderBy('name')->get();
        \$categories = ProductCategory::orderBy('name')->get();
        ";
        
        $content = str_replace('return view(', $fetchCode . "\n        return view(", $content);
        $content = str_replace("compact('products', 'all_categories')", "compact('products', 'all_categories', 'buyers', 'craftsmen', 'subCategories', 'categories')", $content);
        $content = str_replace("compact('products', 'all_categories', 'search', 'sortBy', 'sortOrder', 'categoryFilter', 'productNameFilter', 'bpCodeFilter')", "compact('products', 'all_categories', 'search', 'sortBy', 'sortOrder', 'categoryFilter', 'productNameFilter', 'bpCodeFilter', 'buyers', 'craftsmen', 'subCategories', 'categories')", $content);
    }
    
    // Add design_code filter and craftsman filter logic for Admin
    if (strpos($content, '$request->filled(\'filter_design_code\')') === false && strpos($file, 'Admin') !== false && strpos($file, 'SuperAdmin') === false) {
        $filterLogic = <<<EOT
        if (\$request->filled('filter_design_code')) {
            \$query->where('design_code', 'like', '%' . \$request->filter_design_code . '%');
        }
        if (\$request->filled('filter_craftsman')) {
            \$query->where('bp_code', 'like', '%' . \$request->filter_craftsman . '%');
        }
        if (\$request->filled('filter_product_code')) {
            \$query->where('product_code', 'like', '%' . \$request->filter_product_code . '%');
        }
EOT;
        $content = str_replace('// --- SORTING ---', $filterLogic . "\n\n        // --- SORTING ---", $content);
    }
    
    // Add design_code filter and craftsman filter logic for SuperAdmin
    if (strpos($content, '$request->filled(\'filter_design_code\')') === false && strpos($file, 'SuperAdmin') !== false) {
        // Superadmin uses manual filter params and building query loop or simple ifs
        $filterLogicSuperAdmin = <<<EOT
        if (\$request->filled('filter_design_code')) {
            \$query->where('design_code', 'like', '%' . \$request->filter_design_code . '%');
        }
        if (\$request->filled('filter_craftsman')) {
            \$query->where('bp_code', 'like', '%' . \$request->filter_craftsman . '%');
        }
        if (\$request->filled('filter_product_code')) {
            \$query->where('product_code', 'like', '%' . \$request->filter_product_code . '%');
        }
EOT;
        $content = str_replace('if ($request->filled(\'filter_bp_code\'))', $filterLogicSuperAdmin . "\n        if (\$request->filled('filter_bp_code'))", $content);
        // Sometimes Superadmin doesn't have filter_bp_code block, check where to inject:
        if (strpos($content, $filterLogicSuperAdmin) === false) {
            $content = str_replace('if ($bpCodeFilter) {', $filterLogicSuperAdmin . "\n        if (\$bpCodeFilter) {", $content);
        }
    }
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
