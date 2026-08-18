<?php

function get_filter_html($label, $request_var, $loop_var, $value_key, $text_key, $id_prefix, $is_superadmin) {
    $outer_class = $is_superadmin ? "tw-relative tw-w-full" : "relative w-full";
    $display_class = $is_superadmin ? "tw-w-full tw-min-h-[38px] tw-px-3 tw-py-2 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-flex tw-justify-between tw-items-center tw-cursor-pointer" : "w-full min-h-[40px] px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm flex justify-between items-center cursor-pointer";
    $menu_class = $is_superadmin ? "tw-absolute tw-top-full tw-left-0 tw-w-full tw-bg-white tw-border tw-border-gray-300 tw-rounded-b-lg tw-shadow-lg tw-z-50 tw-hidden tw-p-2" : "absolute top-full left-0 w-full bg-white border border-slate-200 rounded-b-lg shadow-lg z-50 hidden p-2";
    $search_class = $is_superadmin ? "tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-200 tw-rounded-lg tw-mb-2 focus:tw-outline-none tw-text-sm" : "w-full px-3 py-2 border border-slate-200 rounded-lg mb-2 focus:outline-none text-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500";
    $list_class = $is_superadmin ? "tw-max-h-60 tw-overflow-y-auto tw-list-none tw-p-0 tw-m-0" : "max-h-60 overflow-y-auto list-none p-0 m-0";
    $item_class = $is_superadmin ? "tw-px-3 tw-py-2 hover:tw-bg-gray-50 tw-cursor-pointer tw-text-sm tw-rounded" : "px-3 py-2 hover:bg-slate-50 cursor-pointer text-sm rounded";
    
    $label_tag = $is_superadmin ? "" : "<label class=\"block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1\">$label</label>";

    $html = "
            <!-- $label Filter -->
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
                    
        $filters = ['bp_code_filter', 'category_filter', 'subcategory_filter', 'craftsman_filter', 'design_code_filter', 'product_code_filter', 'return_filter'];
        foreach ($filters as $f) {
            if ($f !== $request_var) {
                $html .= "\n                    <input type=\"hidden\" name=\"$f\" value=\"{{ request('$f') }}\">";
            }
        }
    }

    $html .= "
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

function get_return_filter_html($is_superadmin) {
    $outer_class = $is_superadmin ? "tw-relative" : "relative";
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

exec('git checkout -- d:\pulic_html\resources\views\admin\work-order\index.blade.php');
exec('git checkout -- d:\pulic_html\resources\views\super-admin\work-order\index.blade.php');

// Fix Admin
$filepath = 'd:\pulic_html\resources\views\admin\work-order\index.blade.php';
$content = file_get_contents($filepath);
$cat_html = get_filter_html('Category', 'category_filter', 'categories', '->id', '->name', 'category_filter', false);
$subcat_html = get_filter_html('Subcategory', 'subcategory_filter', 'subcategories', '->id', '->name', 'subcategory_filter', false);
$design_html = get_filter_html('Design Code', 'design_code_filter', 'designCodes', '', '', 'design_code_filter', false);
$product_html = get_filter_html('Product Code', 'product_code_filter', 'productCodes', '', '', 'product_code_filter', false);
$return_html = get_return_filter_html(false);
$replacement = $cat_html . $subcat_html . $design_html . $product_html . $return_html;
$pattern = '/<!-- Category -->.*?<\/div>\s*<!-- Craftsman -->/s';
$content = preg_replace($pattern, $replacement . "\n\n            <!-- Craftsman -->", $content);

$js_init = "
        initSearchableDropdown('category_filter_container', 'category_filter_display', 'category_filter_menu', 'category_filter_search', 'category_filter_list', 'category_filter_select', 'All Categories');
        initSearchableDropdown('subcategory_filter_container', 'subcategory_filter_display', 'subcategory_filter_menu', 'subcategory_filter_search', 'subcategory_filter_list', 'subcategory_filter_select', 'All Subcategories');
        initSearchableDropdown('design_code_filter_container', 'design_code_filter_display', 'design_code_filter_menu', 'design_code_filter_search', 'design_code_filter_list', 'design_code_filter_select', 'All Design Codes');
        initSearchableDropdown('product_code_filter_container', 'product_code_filter_display', 'product_code_filter_menu', 'product_code_filter_search', 'product_code_filter_list', 'product_code_filter_select', 'All Product Codes');
";
$content = preg_replace('/(initSearchableDropdown\([\'"]craftsman_filter_container.*?;\n)/', "$1" . $js_init, $content);
file_put_contents($filepath, $content);

// Fix SuperAdmin
$filepath = 'd:\pulic_html\resources\views\super-admin\work-order\index.blade.php';
$content = file_get_contents($filepath);
$cat_html = get_filter_html('Category', 'category_filter', 'categories', '->id', '->name', 'category_filter', true);
$subcat_html = get_filter_html('Subcategory', 'subcategory_filter', 'subcategories', '->id', '->name', 'subcategory_filter', true);
$design_html = get_filter_html('Design Code', 'design_code_filter', 'designCodes', '', '', 'design_code_filter', true);
$product_html = get_filter_html('Product Code', 'product_code_filter', 'productCodes', '', '', 'product_code_filter', true);
$return_html = get_return_filter_html(true);
$replacement = $cat_html . $subcat_html . $design_html . $product_html . $return_html;
$pattern = '/<!-- Category Filter -->.*?<\/div>\s*<!-- Subcategory Filter -->.*?<\/div>/s';
$content = preg_replace($pattern, $replacement, $content);

$content = preg_replace('/(initSearchableDropdown\([\'"]craftsman_filter_container.*?;\n)/', "$1" . $js_init, $content);
file_put_contents($filepath, $content);

echo "done\n";

