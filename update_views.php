<?php

function get_filter_html($label, $id_prefix, $loop_var, $value_key, $text_key, $request_var, $is_superadmin) {
    $tw = $is_superadmin ? "tw-" : "";
    
    return "
            <!-- $label Filter -->
            <div>
                <form method=\"GET\">
                    <input type=\"hidden\" name=\"tab\" value=\"{{ request('tab', 'new-orders') }}\">
                    <input type=\"hidden\" name=\"search\" value=\"{{ request('search') }}\">
                    <input type=\"hidden\" name=\"sort_by\" value=\"{{ request('sort_by', 'id') }}\">
                    <input type=\"hidden\" name=\"sort_order\" value=\"{{ request('sort_order', 'desc') }}\">
                    <input type=\"hidden\" name=\"per_page\" value=\"{{ request('per_page', 10) }}\">
                    <input type=\"hidden\" name=\"bp_code_filter\" value=\"{{ request('bp_code_filter') }}\">
                    <input type=\"hidden\" name=\"category_filter\" value=\"{{ request('category_filter') }}\">
                    <input type=\"hidden\" name=\"subcategory_filter\" value=\"{{ request('subcategory_filter') }}\">
                    <input type=\"hidden\" name=\"craftsman_filter\" value=\"{{ request('craftsman_filter') }}\">
                    <input type=\"hidden\" name=\"design_code_filter\" value=\"{{ request('design_code_filter') }}\">
                    <input type=\"hidden\" name=\"product_code_filter\" value=\"{{ request('product_code_filter') }}\">

                    <div class=\"{$tw}relative {$tw}w-full\" id=\"{$id_prefix}_container\">
                        <div class=\"{$tw}w-full {$tw}min-h-[38px] {$tw}px-3 {$tw}py-2 {$tw}bg-white {$tw}border {$tw}border-gray-300 {$tw}rounded-lg {$tw}text-sm {$tw}flex {$tw}justify-between {$tw}items-center {$tw}cursor-pointer\" id=\"{$id_prefix}_display\">All {$label}s</div>
                        <div class=\"{$tw}absolute {$tw}top-full {$tw}left-0 {$tw}w-full {$tw}bg-white {$tw}border {$tw}border-gray-300 {$tw}rounded-b-lg {$tw}shadow-lg {$tw}z-50 {$tw}hidden {$tw}p-2\" id=\"{$id_prefix}_menu\">
                            <input type=\"text\" class=\"{$tw}w-full {$tw}px-3 {$tw}py-2 {$tw}border {$tw}border-gray-200 {$tw}rounded-lg {$tw}mb-2 focus:{$tw}outline-none {$tw}text-sm\" id=\"{$id_prefix}_search\" placeholder=\"Search for an item...\">
                            <ul class=\"{$tw}max-h-60 {$tw}overflow-y-auto {$tw}list-none {$tw}p-0 {$tw}m-0\" id=\"{$id_prefix}_list\">
                                <li class=\"{$tw}px-3 {$tw}py-2 hover:{$tw}bg-gray-50 {$tw}cursor-pointer {$tw}text-sm {$tw}rounded\" data-value=\"\">All {$label}s</li>
                                @foreach(\${$loop_var} as \$item)
                                <li class=\"{$tw}px-3 {$tw}py-2 hover:{$tw}bg-gray-50 {$tw}cursor-pointer {$tw}text-sm {$tw}rounded\" data-value=\"{{ \$item{$value_key} }}\" {{ request('{$request_var}') == \$item{$value_key} ? 'selected' : '' }}>
                                    {{ \$item{$text_key} }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <select name=\"{$request_var}\" id=\"{$id_prefix}_select\" style=\"display: none;\">
                            <option value=\"\">All {$label}s</option>
                            @foreach(\${$loop_var} as \$item)
                            <option value=\"{{ \$item{$value_key} }}\" {{ request('{$request_var}') == \$item{$value_key} ? 'selected' : '' }}>
                                {{ \$item{$text_key} }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
";
}

function process_blade($filepath, $is_superadmin) {
    if (!file_exists($filepath)) return;
    $content = file_get_contents($filepath);
    
    // Add hidden inputs to all existing hidden input groups
    $content = preg_replace('/(<input type="hidden" name="craftsman_filter"[^>]*>)/', "$1\n                    <input type=\"hidden\" name=\"design_code_filter\" value=\"{{ request('design_code_filter') }}\">\n                    <input type=\"hidden\" name=\"product_code_filter\" value=\"{{ request('product_code_filter') }}\">", $content);
    
    // Generate new html blocks
    $cat_html = get_filter_html('Category', 'category_filter', 'categories', '->id', '->name', 'category_filter', $is_superadmin);
    $subcat_html = get_filter_html('Subcategory', 'subcategory_filter', 'subcategories', '->id', '->name', 'subcategory_filter', $is_superadmin);
    $design_html = get_filter_html('Design Code', 'design_code_filter', 'designCodes', '', '', 'design_code_filter', $is_superadmin);
    $product_html = get_filter_html('Product Code', 'product_code_filter', 'productCodes', '', '', 'product_code_filter', $is_superadmin);
    
    $replacement = $cat_html . $subcat_html . $design_html . $product_html;
    
    // Replace Category Filter up to the end of Subcategory Filter
    $pattern = '/<!-- Category Filter -->.*?<\/div>\s*<!-- Subcategory Filter -->.*?<\/div>/s';
    $content = preg_replace($pattern, $replacement, $content);
    
    // Add JS initialization
    $js_init = "
        initSearchableDropdown('category_filter_container', 'category_filter_display', 'category_filter_menu', 'category_filter_search', 'category_filter_list', 'category_filter_select', 'All Categories');
        initSearchableDropdown('subcategory_filter_container', 'subcategory_filter_display', 'subcategory_filter_menu', 'subcategory_filter_search', 'subcategory_filter_list', 'subcategory_filter_select', 'All Subcategories');
        initSearchableDropdown('design_code_filter_container', 'design_code_filter_display', 'design_code_filter_menu', 'design_code_filter_search', 'design_code_filter_list', 'design_code_filter_select', 'All Design Codes');
        initSearchableDropdown('product_code_filter_container', 'product_code_filter_display', 'product_code_filter_menu', 'product_code_filter_search', 'product_code_filter_list', 'product_code_filter_select', 'All Product Codes');
    ";
    
    $content = preg_replace('/(initSearchableDropdown\([\'"]craftsman_filter_container.*?;\n)/', "$1" . $js_init, $content);
    
    file_put_contents($filepath, $content);
}

process_blade('d:\pulic_html\resources\views\super-admin\work-order\index.blade.php', true);
process_blade('d:\pulic_html\resources\views\admin\work-order\index.blade.php', false);

echo "done\n";
