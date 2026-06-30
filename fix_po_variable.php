<?php

$files = [
    'resources/views/super-admin/purchase-order/index.blade.php',
    'resources/views/admin/purchase-order/index.blade.php',
    'resources/views/craftsman/purchase-order/index.blade.php',
];

foreach ($files as $file) {
    $fullPath = 'E:/arihanth/' . $file;
    if (!file_exists($fullPath)) continue;
    
    $content = file_get_contents($fullPath);
    
    // We only want to replace $order-> with $po-> in the block we injected.
    // The injected block starts with: $rowBgClass = '';
    
    // Using preg_replace_callback to find the injected @php ... @endphp blocks
    $pattern = '/(@php\s+\$rowBgClass = \'\';.*?@endphp)/is';
    
    $content = preg_replace_callback($pattern, function($matches) {
        $block = $matches[1];
        // Replace $order with $po
        $block = str_replace('$order->', '$po->', $block);
        return $block;
    }, $content);
    
    file_put_contents($fullPath, $content);
    echo "Fixed $file\n";
}
echo "Done.\n";
