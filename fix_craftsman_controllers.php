<?php

function fixController($file, $viewName) {
    if (!file_exists($file)) return;
    $content = file_get_contents($file);

    // If the file is missing the productCategories and return view, let's inject it before the closing brace of index() method.
    // The index method ends right before `public function export(Request $request)` or `public function printSelected(Request $request)`.
    
    // Check if productCategories is missing
    if (strpos($content, '$productCategories = ProductCategory::orderBy(\'name\')->get();') === false) {
        $replacement = <<<EOD
        \$productCategories = ProductCategory::orderBy('name')->get();

        return view('$viewName', compact(
            'overdueOrders',
            'allocatedOrders', 
            'inProcessOrders', 
            'completedOrders', 
            'rejectedOrders',
            'productCategories'
        ));
    }
EOD;

        // Replace the single `}` before `public function export` or similar
        // Let's use a regex to find the end of the index method
        $pattern = '/\s*\}\s*(public function export|public function printSelected)/s';
        
        $content = preg_replace($pattern, "\n" . $replacement . "\n\n    $1", $content);
        file_put_contents($file, $content);
        echo "Fixed $file\n";
    }
}

fixController('d:/pulic_html/app/Http/Controllers/Craftsman/PurchaseOrderController.php', 'craftsman.purchase-order.index');
fixController('d:/pulic_html/app/Http/Controllers/CraftsmanStaff/PurchaseOrderController.php', 'craftsman_staff.purchase-order.index');
