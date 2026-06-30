import re

file_path = r'd:\Company Projects\Lasirene\erp\erp\resources\views\craftsman\work-order\index.blade.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Fix backslashes in ?? '-'
content = content.replace("\\'-\\'", "'-'")

# 2. Fix Reject Reason column visibility
# First, remove it from all headers
content = content.replace('<th>Reject Reason</th>\n                                                <th>Actions</th>', '<th>Actions</th>')

# Then, explicitly add it back to the Rejected tab header
rejected_tab_pattern = r'(<h4>Rejected Work Orders</h4>.*?<thead>.*?<th>Due Date</th>\s+)(<th>Actions</th>)'
# This might be tricky because of the structure. Let's try a simpler approach.

# Let's find the Rejected tab section and add it there specifically.
parts = content.split('<div class="tab-pane fade" id="rejected" role="tabpanel">')
if len(parts) > 1:
    header_part, rest = parts[1].split('<thead>', 1)
    thead, tail = rest.split('</thead>', 1)
    thead = thead.replace('<th>Due Date</th>', '<th>Due Date</th>\n                                                <th>Reject Reason</th>')
    content = parts[0] + '<div class="tab-pane fade" id="rejected" role="tabpanel">' + header_part + '<thead>' + thead + '</thead>' + tail

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixes complete.")
