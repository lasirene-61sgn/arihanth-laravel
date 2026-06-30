<?php

$files = [
    'resources/views/super-admin/work-order/index.blade.php',
    'resources/views/super-admin/purchase-order/index.blade.php',
    'resources/views/super-admin/stock-order/index.blade.php',
    'resources/views/admin/work-order/index.blade.php',
    'resources/views/admin/purchase-order/index.blade.php',
    'resources/views/admin/stock-order/index.blade.php',
    'resources/views/craftsman/work-order/index.blade.php',
    'resources/views/craftsman/purchase-order/index.blade.php',
    'resources/views/craftsman/stock-order/index.blade.php',
];

foreach ($files as $file) {
    $fullPath = 'E:/arihanth/' . $file;
    if (!file_exists($fullPath)) continue;
    
    $content = file_get_contents($fullPath);
    $content = str_replace('hover:">', '">', $content);
    $content = str_replace('hover: ">', '">', $content);
    
    file_put_contents($fullPath, $content);
}
echo "Fixed.\n";
