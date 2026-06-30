<?php
$path = 'e:/public_html/app/Http/Controllers/SuperAdmin/PurchaseOrderController.php';
$content = file_get_contents($path);

// Use a simple replace for `Auth::guard` to `\Auth::guard`
$content = str_replace("Auth::guard", "\Auth::guard", $content);
// Also fix any `Auth::id()` just in case it's used elsewhere in the controller without the \
$content = preg_replace('/(?<!\\\\)Auth::id\(\)/', '\Auth::id()', $content);

file_put_contents($path, $content);
echo "Fixed Auth usage.\n";
