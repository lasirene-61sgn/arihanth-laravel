<?php
$files = [
    'resources/views/super-admin/purchase-order/index.blade.php',
    'resources/views/super-admin/stock-order/index.blade.php',
    'resources/views/admin/stock-order/index.blade.php',
    'resources/views/craftsman/stock-order/index.blade.php',
    'resources/views/admin/purchase-order/index.blade.php',
    'resources/views/craftsman/purchase-order/index.blade.php',
    'resources/views/admin/work-order/index.blade.php',
    'resources/views/super-admin/work-order/index.blade.php',
    'resources/views/craftsman/work-order/index.blade.php',
];

$styleBlock = "\n<style>\n  tr[style*=\"background-color\"] > td, tr[style*=\"background-color\"] > th {\n      background-color: transparent !important;\n  }\n</style>\n";

foreach ($files as $file) {
    $fullPath = 'E:/arihanth/' . $file;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        if (strpos($content, 'tr[style*="background-color"] > td') === false) {
            $content .= $styleBlock;
            file_put_contents($fullPath, $content);
            echo "Fixed $file\n";
        }
    }
}
