<?php

// Function to generate the Return Filter HTML
function get_return_filter_html($is_superadmin) {
    $outer_class = $is_superadmin ? "tw-relative" : "relative";
    
    // For admin, we use the specific label format.
    $label_tag = $is_superadmin ? "" : "<label class=\"block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1\">Return Status</label>";
    
    $select_class = $is_superadmin ? "tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-200 tw-rounded-lg tw-text-sm focus:tw-outline-none" : "w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500";

    $html = "
            <!-- Return Status Filter -->
            <div class=\"".($is_superadmin?"":"relative")."\">
                $label_tag";
                
    if ($is_superadmin) {
        $html .= "
                <form method=\"GET\">
                    <input type=\"hidden\" name=\"tab\" value=\"{{ request('tab', 'new-orders') }}\">
                    <input type=\"hidden\" name=\"search\" value=\"{{ request('search') }}\">
                    <input type=\"hidden\" name=\"sort_by\" value=\"{{ request('sort_by', 'id') }}\">
                    <input type=\"hidden\" name=\"sort_order\" value=\"{{ request('sort_order', 'desc') }}\">
                    <input type=\"hidden\" name=\"per_page\" value=\"{{ request('per_page', 10) }}\">";
                    
        $filters = ['bp_code_filter', 'category_filter', 'subcategory_filter', 'craftsman_filter', 'design_code_filter', 'product_code_filter'];
        foreach ($filters as $f) {
            $html .= "\n                    <input type=\"hidden\" name=\"$f\" value=\"{{ request('$f') }}\">";
        }
    }

    $html .= "
                    <div class=\"$outer_class\">
                        <select name=\"return_filter\" class=\"$select_class\" onchange=\"this.form.submit()\">
                            <option value=\"\">All Orders</option>
                            <option value=\"returned\" {{ request('return_filter') == 'returned' ? 'selected' : '' }}>Returned Orders Only</option>
                        </select>
                    </div>";
                    
    if ($is_superadmin) {
        $html .= "
                </form>";
    }
    
    $html .= "
            </div>
";

    return $html;
}

// Fix Admin
$filepath = 'd:\pulic_html\resources\views\admin\work-order\index.blade.php';
$content = file_get_contents($filepath);
// Make sure it doesn't already have it
if (strpos($content, 'Return Status Filter') === false) {
    $return_html = get_return_filter_html(false);
    $pattern = '/(<!-- Product Code Filter -->.*?<\/select>\s*<\/div>\s*<\/div>)/s';
    if (preg_match($pattern, $content, $matches)) {
        $content = preg_replace($pattern, $matches[1] . "\n" . $return_html, $content, 1);
        echo "Found and replaced Product Code Filter in Admin\n";
    } else {
        echo "Could not find Product Code Filter in Admin\n";
    }
    file_put_contents($filepath, $content);
} else {
    echo "Admin already has Return Status Filter\n";
}

echo "done\n";

