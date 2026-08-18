<?php

$directories = [
    'resources/views/super-admin/work-order',
    'resources/views/super-admin/purchase-order',
    'resources/views/admin/work-order',
    'resources/views/admin/purchase-order',
    'resources/views/craftsman/work-order',
    'resources/views/craftsman/purchase-order',
    'resources/views/craftsman_staff/work-order',
    'resources/views/craftsman_staff/purchase-order',
];

foreach ($directories as $d) {
    $filepath = __DIR__ . '/' . $d . '/index.blade.php';
    if (!file_exists($filepath)) {
        continue;
    }
    
    $content = file_get_contents($filepath);
    $original = $content;
    
    // Pattern 1: {{ $order->craftsman->craftman_code ?? 'N/A' }}
    $pattern1 = "/(\{\{\s*\\\$order->craftsman->craftman_code\s*\?\?\s*'N\/A'\s*\}\})/";
    $replacement1 = "$1\n@if(isset(\$order) && \$order->staff_completed_at && \$order->craftsmanStaff)\n    <br><span style=\"font-size: 11px; color: #7e22ce; font-weight: bold;\">Staff(C): {{ \$order->craftsmanStaff->name }}</span>\n@elseif(isset(\$order) && \$order->staff_accepted_at && \$order->acceptedByStaff)\n    <br><span style=\"font-size: 11px; color: #2563eb; font-weight: bold;\">Staff(A): {{ \$order->acceptedByStaff->name }}</span>\n@endif";
    
    // Pattern 2: <td>{{ $po->allocated_craftsman_code ?? 'N/A' }}</td>
    $pattern2 = "/(<td>\s*\{\{\s*\\\$po->allocated_craftsman_code\s*\?\?\s*'N\/A'\s*\}\}\s*)(<\/td>)/";
    $replacement2 = "$1\n@if(isset(\$po) && \$po->staff_completed_at && \$po->craftsmanStaff)\n    <br><span style=\"font-size: 11px; color: #7e22ce; font-weight: bold;\">Staff(C): {{ \$po->craftsmanStaff->name }}</span>\n@elseif(isset(\$po) && \$po->staff_accepted_at && \$po->acceptedByStaff)\n    <br><span style=\"font-size: 11px; color: #2563eb; font-weight: bold;\">Staff(A): {{ \$po->acceptedByStaff->name }}</span>\n@endif$2";
    
    $content = preg_replace($pattern1, $replacement1, $content);
    $content = preg_replace($pattern2, $replacement2, $content);
    
    if ($content !== $original) {
        file_put_contents($filepath, $content);
        echo "Updated: $filepath\n";
    } else {
        echo "No changes: $filepath\n";
    }
}
