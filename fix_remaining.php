<?php

$filepath = 'd:\pulic_html\resources\views\admin\purchase-order\index.blade.php';
$content = file_get_contents($filepath);

$pattern = '/(\{\{\s*\$po->allocated_craftsman_code\s*\?\?\s*\'N\/A\'\s*\}\})/';
$replacement = "$1\n@if(isset(\$po) && \$po->staff_completed_at && \$po->craftsmanStaff)\n    <br><span style=\"font-size: 11px; color: #7e22ce; font-weight: bold;\">Staff(C): {{ \$po->craftsmanStaff->name }}</span>\n@elseif(isset(\$po) && \$po->staff_accepted_at && \$po->acceptedByStaff)\n    <br><span style=\"font-size: 11px; color: #2563eb; font-weight: bold;\">Staff(A): {{ \$po->acceptedByStaff->name }}</span>\n@endif";

$content = preg_replace($pattern, $replacement, $content);
file_put_contents($filepath, $content);
echo 'done admin po';

$filepath = 'd:\pulic_html\resources\views\craftsman\work-order\index.blade.php';
if(file_exists($filepath)){
    $content = file_get_contents($filepath);
    $pattern = '/(\{\{\s*\$order->craftsman->craftman_code\s*\?\?\s*\'N\/A\'\s*\}\})/';
    $content = preg_replace($pattern, $replacement, $content);
    file_put_contents($filepath, $content);
    echo 'done craftsman wo';
}

$filepath = 'd:\pulic_html\resources\views\craftsman\purchase-order\index.blade.php';
if(file_exists($filepath)){
    $content = file_get_contents($filepath);
    $pattern = '/(\{\{\s*\$po->allocated_craftsman_code\s*\?\?\s*\'N\/A\'\s*\}\})/';
    $content = preg_replace($pattern, $replacement, $content);
    file_put_contents($filepath, $content);
    echo 'done craftsman po';
}

$filepath = 'd:\pulic_html\resources\views\craftsman_staff\work-order\index.blade.php';
if(file_exists($filepath)){
    $content = file_get_contents($filepath);
    $pattern = '/(\{\{\s*\$order->craftsman->craftman_code\s*\?\?\s*\'N\/A\'\s*\}\})/';
    $content = preg_replace($pattern, $replacement, $content);
    file_put_contents($filepath, $content);
    echo 'done craftsman_staff wo';
}
