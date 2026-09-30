import os
import re

def generate_filter_html(name, label, id_prefix, loop_var, value_key, text_key, current_val_var, request_var, is_superadmin=True):
    # Prefix for tailwind
    tw = "tw-" if is_superadmin else ""
    
    html = f"""
            <!-- {label} Filter -->
            <div>
                <form method="GET">
                    <input type="hidden" name="tab" value="{{{{ request('tab', 'new-orders') }}}}">
                    <input type="hidden" name="search" value="{{{{ request('search') }}}}">
                    <input type="hidden" name="sort_by" value="{{{{ request('sort_by', 'id') }}}}">
                    <input type="hidden" name="sort_order" value="{{{{ request('sort_order', 'desc') }}}}">
                    <input type="hidden" name="per_page" value="{{{{ request('per_page', 10) }}}}">
                    <input type="hidden" name="bp_code_filter" value="{{{{ request('bp_code_filter') }}}}">
                    <input type="hidden" name="category_filter" value="{{{{ request('category_filter') }}}}">
                    <input type="hidden" name="subcategory_filter" value="{{{{ request('subcategory_filter') }}}}">
                    <input type="hidden" name="craftsman_filter" value="{{{{ request('craftsman_filter') }}}}">
                    <input type="hidden" name="design_code_filter" value="{{{{ request('design_code_filter') }}}}">
                    <input type="hidden" name="product_code_filter" value="{{{{ request('product_code_filter') }}}}">

                    <div class="{tw}relative {tw}w-full" id="{id_prefix}_container">
                        <div class="{tw}w-full {tw}min-h-[38px] {tw}px-3 {tw}py-2 {tw}bg-white {tw}border {tw}border-gray-300 {tw}rounded-lg {tw}text-sm {tw}flex {tw}justify-between {tw}items-center {tw}cursor-pointer" id="{id_prefix}_display">All {label}s</div>
                        <div class="{tw}absolute {tw}top-full {tw}left-0 {tw}w-full {tw}bg-white {tw}border {tw}border-gray-300 {tw}rounded-b-lg {tw}shadow-lg {tw}z-50 {tw}hidden {tw}p-2" id="{id_prefix}_menu">
                            <input type="text" class="{tw}w-full {tw}px-3 {tw}py-2 {tw}border {tw}border-gray-200 {tw}rounded-lg {tw}mb-2 focus:{tw}outline-none {tw}text-sm" id="{id_prefix}_search" placeholder="Search for an item...">
                            <ul class="{tw}max-h-60 {tw}overflow-y-auto {tw}list-none {tw}p-0 {tw}m-0" id="{id_prefix}_list">
                                <li class="{tw}px-3 {tw}py-2 hover:{tw}bg-gray-50 {tw}cursor-pointer {tw}text-sm {tw}rounded" data-value="">All {label}s</li>
                                @foreach(${loop_var} as ${current_val_var})
                                <li class="{tw}px-3 {tw}py-2 hover:{tw}bg-gray-50 {tw}cursor-pointer {tw}text-sm {tw}rounded" data-value="{{{{ ${current_val_var}{value_key} }}}}" {{{{ request('{request_var}') == ${current_val_var}{value_key} ? 'selected' : '' }}}}>
                                    {{{{ ${current_val_var}{text_key} }}}}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <select name="{request_var}" id="{id_prefix}_select" style="display: none;">
                            <option value="">All {label}s</option>
                            @foreach(${loop_var} as ${current_val_var})
                            <option value="{{{{ ${current_val_var}{value_key} }}}}" {{{{ request('{request_var}') == ${current_val_var}{value_key} ? 'selected' : '' }}}}>
                                {{{{ ${current_val_var}{text_key} }}}}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
"""
    return html

# We also need to generate the init JS block
def get_init_js(id_prefix, label):
    return f"initSearchableDropdown('{id_prefix}_container', '{id_prefix}_display', '{id_prefix}_menu', '{id_prefix}_search', '{id_prefix}_list', '{id_prefix}_select', 'All {label}s');"


def process_blade(filepath, is_superadmin):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # 1. Update the hidden inputs everywhere so the new filters aren't lost
    content = re.sub(r'(<input type="hidden" name="craftsman_filter".*?>)', r'\1\n                    <input type="hidden" name="design_code_filter" value="{{ request(\'design_code_filter\') }}">\n                    <input type="hidden" name="product_code_filter" value="{{ request(\'product_code_filter\') }}">', content)
    
    # 2. Replace Category Filter block
    # Using regex to find the category block and subcategory block
    cat_block_regex = r'<!-- Category Filter -->.*?</div>\s*<!-- Subcategory Filter -->.*?</div>'
    
    new_cat_html = generate_filter_html('Category', 'Category', 'category_filter', 'categories', '->id', '->name', 'category', 'category_filter', is_superadmin)
    new_subcat_html = generate_filter_html('Subcategory', 'Subcategory', 'subcategory_filter', 'subcategories', '->id', '->name', 'subcategory', 'subcategory_filter', is_superadmin)
    new_design_html = generate_filter_html('Design Code', 'Design Code', 'design_code_filter', 'designCodes', '', '', 'code', 'design_code_filter', is_superadmin)
    new_product_html = generate_filter_html('Product Code', 'Product Code', 'product_code_filter', 'productCodes', '', '', 'code', 'product_code_filter', is_superadmin)
    
    replacement = new_cat_html + new_subcat_html + new_design_html + new_product_html
    
    # 3. Apply replacement
    content = re.sub(cat_block_regex, replacement, content, flags=re.DOTALL)
    
    # 4. Add JS initialization
    js_init = "\n        ".join([
        get_init_js('category_filter', 'Category'),
        get_init_js('subcategory_filter', 'Subcategory'),
        get_init_js('design_code_filter', 'Design Code'),
        get_init_js('product_code_filter', 'Product Code'),
    ])
    
    # Find where initSearchableDropdown is called in the script and append
    js_inject_regex = r'(initSearchableDropdown\(\'craftsman_filter_container\'.*?\);)'
    content = re.sub(js_inject_regex, r'\1\n        ' + js_init, content)
    
    # Wait, in admin, it might not be 'craftsman_filter_container' exactly if variables differ. Let's check.
    
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

process_blade('d:/pulic_html/resources/views/super-admin/work-order/index.blade.php', True)
process_blade('d:/pulic_html/resources/views/admin/work-order/index.blade.php', False)
print("done")
