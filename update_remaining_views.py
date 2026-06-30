import re
import os

files_to_update = [
    r"d:\Company Projects\Lasirene\erp\erp\resources\views\super-admin\work-order\index.blade.php",
    r"d:\Company Projects\Lasirene\erp\erp\resources\views\admin\work-order\show.blade.php",
    r"d:\Company Projects\Lasirene\erp\erp\resources\views\super-admin\work-order\show.blade.php"
]

def update_index(content):
    # Add Header
    def add_header(match):
        full_match = match.group(0)
        if "BP Code" in full_match: return full_match
        indent = match.group(1)
        return f'{indent}<th>Customer Name</th>\n{indent}<th>BP Code</th>\n{indent}<th>Product Name</th>'
    content = re.sub(r'(\s+)<th>Customer Name</th>\n\s+<th>Product Name</th>', add_header, content)
    
    # Add Cell
    def add_cell(match):
        full_match = match.group(0)
        if "bp_code" in full_match: return full_match
        indent = match.group(1)
        return f'{indent}<td>{{{{ $order->customer_name }}}}</td>\n{indent}<td>{{{{ $order->bp_code ?? \'-\' }}}}</td>\n{indent}<td>{{{{ $order->product_name }}}}</td>'
    content = re.sub(r'(\s+)<td>{{ \$order->customer_name }}</td>\n\s+<td>{{ \$order->product_name }}</td>', add_cell, content)
    
    return content

def update_show(content):
    # Add BP Code after Customer Name
    def add_bp_code_show(match):
        full_match = match.group(0)
        if "BP Code" in full_match or "$workOrder->bp_code" in full_match: return full_match
        indent = match.group(1)
        return f'{indent}<div class="col-md-6 mb-3"><label class="text-muted small d-block">Customer Name</label><p class="fw-bold">{{{{ $workOrder->customer_name }}}}</p></div>\n{indent}<div class="col-md-6 mb-3"><label class="text-muted small d-block">BP Code</label><p class="fw-bold">{{{{ $workOrder->bp_code ?? \'-\' }}}}</p></div>'
    
    pattern = r'(\s+)<div class="col-md-6 mb-3"><label class="text-muted small d-block">Customer Name</label><p class="fw-bold">{{ \$workOrder->customer_name }}</p></div>'
    content = re.sub(pattern, add_bp_code_show, content)
    return content

for path in files_to_update:
    if not os.path.exists(path):
        print(f"Skipping {path}, file not found.")
        continue
    
    with open(path, "r", encoding="utf-8") as f:
        content = f.read()
    
    if "index.blade.php" in path:
        new_content = update_index(content)
    else:
        new_content = update_show(content)
    
    if new_content != content:
        with open(path, "w", encoding="utf-8") as f:
            f.write(new_content)
        print(f"Successfully updated {path}")
    else:
        print(f"No changes needed for {path}")
