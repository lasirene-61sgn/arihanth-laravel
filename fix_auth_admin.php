<?php
$path = 'e:/public_html/app/Http/Controllers/Admin/PurchaseOrderController.php';
$content = file_get_contents($path);

$content = str_replace("Auth::guard", "\Auth::guard", $content);
$content = preg_replace('/(?<!\\\\)Auth::id\(\)/', '\Auth::id()', $content);

file_put_contents($path, $content);
echo "Fixed Auth usage in Admin.\n";
