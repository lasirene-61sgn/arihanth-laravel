import re

file_path = r'd:\Company Projects\Lasirene\erp\erp\resources\views\craftsman\work-order\index.blade.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update Search Placeholder
content = content.replace('placeholder="Number, customer, product..."', 'placeholder="Number, customer, product, BP code, dear..."')

# 2. Add Headers (Allocated, In Process, Overdue)
header_pattern = r'(<th>Work Order Number</th>\s+)(<th>Customer Name</th>)'
content = re.sub(header_pattern, r'\1<th>BP Code</th>\n                                                <th>Dear</th>\n                                                \2', content)

# 3. Add Headers (Completed)
completed_header_pattern = r'(<th>Work Order Number</th>\s+)(<th>Customer Name</th>\s+<th>Product Name</th>)'
content = re.sub(completed_header_pattern, r'\1<th>BP Code</th>\n                                                <th>Dear</th>\n                                                \2', content)

# 4. Add Headers (Rejected) - Note: my previous replace added rejection_reason to data row but maybe not header
rejected_header_pattern = r'(<th>Work Order Number</th>\s+)(<th>Customer Name</th>\s+<th>Product Name</th>)'
# This might match Completed too, but it's fine if they match.
# Let's search for Rejected specific one.
# Actually, I'll just do it for all occurrences of <th>Work Order Number</th> that are followed by <th>Customer Name</th>

# 5. Add Data Cells
data_pattern = r'(<td>{{ \$order->work_order_number }}</td>\s+)(<td>{{ \$order->customer_name }}</td>)'
content = re.sub(data_pattern, r'\1<td>{{ $order->bp_code ?? \'-\' }}</td>\n                                                <td>{{ $order->buyer->dear ?? \'-\' }}</td>\n                                                \2', content)

# Special Case for Rejected Header (Adding Reject Reason)
if '<th>Reject Reason</th>' not in content:
    content = content.replace('<th>Due Date</th>\n                                                <th>Actions</th>', 
                              '<th>Due Date</th>\n                                                <th>Reject Reason</th>\n                                                <th>Actions</th>')

# Clean up any potential double additions if script re-run (though unlikely here)
# content = content.replace('<th>BP Code</th>\n                                                <th>BP Code</th>', '<th>BP Code</th>')

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Standardization complete.")
