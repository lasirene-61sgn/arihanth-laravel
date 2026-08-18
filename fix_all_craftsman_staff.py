import os
import re

directories = [
    'resources/views/super-admin/work-order',
    'resources/views/super-admin/purchase-order',
    'resources/views/admin/work-order',
    'resources/views/admin/purchase-order',
    'resources/views/craftsman/work-order',
    'resources/views/craftsman/purchase-order',
    'resources/views/craftsman_staff/work-order',
    'resources/views/craftsman_staff/purchase-order',
]

def process_file(filepath):
    if not os.path.exists(filepath):
        return
        
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
        
    original = content
    
    # Work Orders specific patterns (e.g., admin/work-order/index.blade.php)
    # Pattern: {{ $order->craftsman->craftman_code ?? 'N/A' }}
    pattern1 = r"(\{\{\s*\$order->craftsman->craftman_code\s*\?\?\s*'N/A'\s*\}\})"
    replacement1 = r"""\1
@if(isset($order) && $order->staff_completed_at && $order->craftsmanStaff)
    <br><span style="font-size: 11px; color: #7e22ce; font-weight: bold;">Staff(C): {{ $order->craftsmanStaff->name }}</span>
@elseif(isset($order) && $order->staff_accepted_at && $order->acceptedByStaff)
    <br><span style="font-size: 11px; color: #2563eb; font-weight: bold;">Staff(A): {{ $order->acceptedByStaff->name }}</span>
@endif"""
    
    # Pattern for purchase orders: <td>{{ $po->allocated_craftsman_code ?? 'N/A' }}</td>
    pattern2 = r"(<td>\s*\{\{\s*\$po->allocated_craftsman_code\s*\?\?\s*'N/A'\s*\}\}\s*)(</td>)"
    replacement2 = r"""\1
@if(isset($po) && $po->staff_completed_at && $po->craftsmanStaff)
    <br><span style="font-size: 11px; color: #7e22ce; font-weight: bold;">Staff(C): {{ $po->craftsmanStaff->name }}</span>
@elseif(isset($po) && $po->staff_accepted_at && $po->acceptedByStaff)
    <br><span style="font-size: 11px; color: #2563eb; font-weight: bold;">Staff(A): {{ $po->acceptedByStaff->name }}</span>
@endif\2"""
    
    # Try applying patterns
    content = re.sub(pattern1, replacement1, content)
    content = re.sub(pattern2, replacement2, content)
    
    if content != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated: {filepath}")
    else:
        print(f"No changes: {filepath}")

for d in directories:
    index_file = os.path.join('d:/pulic_html', d, 'index.blade.php')
    process_file(index_file)
