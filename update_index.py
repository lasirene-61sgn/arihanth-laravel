import re

path = r"d:\Company Projects\Lasirene\erp\erp\resources\views\admin\work-order\index.blade.php"

with open(path, "r", encoding="utf-8") as f:
    content = f.read()

# 1. Update cells for Allocated and In Process (Headers are already added)
# Pattern: <td>{{ $order->customer_name }}</td>\n\s+<td>{{ $order->product_name }}</td>
# We want to insert BP Code cell between them.

# We need to be careful not to double add if we run it multiple times.
# Better approach: check if bp_code is already there after customer_name.

def add_bp_code_cell(match):
    full_match = match.group(0)
    if "bp_code" in full_match:
        return full_match
    # Get indentation from the customer_name line
    indent = match.group(1)
    return f'{indent}<td>{{{{ $order->customer_name }}}}</td>\n{indent}<td>{{{{ $order->bp_code ?? \'-\' }}}}</td>\n{indent}<td>{{{{ $order->product_name }}}}</td>'

content = re.sub(r'(\s+)<td>{{ \$order->customer_name }}</td>\n\s+<td>{{ \$order->product_name }}</td>', add_bp_code_cell, content)

# 2. Update headers for remaining sections (Rejected, Overdue, For Approval, Completed)
def add_bp_code_header(match):
    full_match = match.group(0)
    if "BP Code" in full_match:
        return full_match
    indent = match.group(1)
    return f'{indent}<th>Customer Name</th>\n{indent}<th>BP Code</th>\n{indent}<th>Product Name</th>'

content = re.sub(r'(\s+)<th>Customer Name</th>\n\s+<th>Product Name</th>', add_bp_code_header, content)

with open(path, "w", encoding="utf-8") as f:
    f.write(content)

print("Successfully updated admin/work-order/index.blade.php")
