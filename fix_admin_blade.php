<?php

function get_filter_html($label, $id_prefix, $loop_var, $value_key, $text_key, $request_var, $is_superadmin) {
    $tw = $is_superadmin ? "tw-" : "";
    
    // For admin, the width is handled by container layout. The original class was `relative w-full`
    $outer_class = $is_superadmin ? "tw-relative tw-w-full" : "relative w-full";
    $display_class = $is_superadmin ? "tw-w-full tw-min-h-[38px] tw-px-3 tw-py-2 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-flex tw-justify-between tw-items-center tw-cursor-pointer" : "w-full min-h-[40px] px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm flex justify-between items-center cursor-pointer";
    $menu_class = $is_superadmin ? "tw-absolute tw-top-full tw-left-0 tw-w-full tw-bg-white tw-border tw-border-gray-300 tw-rounded-b-lg tw-shadow-lg tw-z-50 tw-hidden tw-p-2" : "absolute top-full left-0 w-full bg-white border border-slate-200 rounded-b-lg shadow-lg z-50 hidden p-2";
    $search_class = $is_superadmin ? "tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-200 tw-rounded-lg tw-mb-2 focus:tw-outline-none tw-text-sm" : "w-full px-3 py-2 border border-slate-200 rounded-lg mb-2 focus:outline-none text-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500";
    $list_class = $is_superadmin ? "tw-max-h-60 tw-overflow-y-auto tw-list-none tw-p-0 tw-m-0" : "max-h-60 overflow-y-auto list-none p-0 m-0";
    $item_class = $is_superadmin ? "tw-px-3 tw-py-2 hover:tw-bg-gray-50 tw-cursor-pointer tw-text-sm tw-rounded" : "px-3 py-2 hover:bg-slate-50 cursor-pointer text-sm rounded";
    
    // Use the label from admin blade if available
    $label_tag = $is_superadmin ? "" : "<label class=\"block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1\">$label</label>";

    return "
            <!-- $label Filter -->
            <div class=\"".($is_superadmin?"":"relative")."\">
                $label_tag
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

                    <div class=\"$outer_class\" id=\"{$id_prefix}_container\">
                        <div class=\"$display_class\" id=\"{$id_prefix}_display\">All {$label}s</div>
                        <div class=\"$menu_class\" id=\"{$id_prefix}_menu\">
                            <input type=\"text\" class=\"$search_class\" id=\"{$id_prefix}_search\" placeholder=\"Search for an item...\">
                            <ul class=\"$list_class\" id=\"{$id_prefix}_list\">
                                <li class=\"$item_class\" data-value=\"\">All {$label}s</li>
                                @foreach(\${$loop_var} as \$item)
                                <li class=\"$item_class\" data-value=\"{{ \$item{$value_key} }}\" {{ request('{$request_var}') == \$item{$value_key} ? 'selected' : '' }}>
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

function process_admin_blade() {
    $filepath = 'd:\pulic_html\resources\views\admin\work-order\index.blade.php';
    if (!file_exists($filepath)) return;
    $content = file_get_contents($filepath);
    
    // Add hidden inputs to all existing hidden input groups
    $content = preg_replace('/(<input type="hidden" name="craftsman_filter"[^>]*>)/', "$1\n                    <input type=\"hidden\" name=\"design_code_filter\" value=\"{{ request('design_code_filter') }}\">\n                    <input type=\"hidden\" name=\"product_code_filter\" value=\"{{ request('product_code_filter') }}\">", $content);
    
    // Generate new html blocks
    $cat_html = get_filter_html('Category', 'category_filter', 'categories', '->id', '->name', 'category_filter', false);
    $subcat_html = get_filter_html('Subcategory', 'subcategory_filter', 'subcategories', '->id', '->name', 'subcategory_filter', false);
    $design_html = get_filter_html('Design Code', 'design_code_filter', 'designCodes', '', '', 'design_code_filter', false);
    $product_html = get_filter_html('Product Code', 'product_code_filter', 'productCodes', '', '', 'product_code_filter', false);
    
    $replacement = $cat_html . $subcat_html . $design_html . $product_html;
    
    // Replace Category Block up to Craftsman Block
    $pattern = '/<!-- Category -->.*?<\/div>\s*<!-- Craftsman -->/s';
    $content = preg_replace($pattern, $replacement . "\n\n            <!-- Craftsman -->", $content);
    
    file_put_contents($filepath, $content);
}

process_admin_blade();
echo "done\n";
