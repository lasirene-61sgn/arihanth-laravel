<?php
$path = 'e:/public_html/app/Http/Controllers/SuperAdmin/PurchaseOrderController.php';
$c = file_get_contents($path);

$search = '            \'status\' => \'created\',';

$replace = '            \'status\' => \'created\',
            \'created_by\' => Auth::guard(\'super_admin\')->id(),
            \'creator_type\' => \'super_admin\',';

$c = str_replace(str_replace("\n", "\r\n", $search), str_replace("\n", "\r\n", $replace), $c);
$c = str_replace($search, $replace, $c);

file_put_contents($path, $c);
echo "Fixed SuperAdmin store.\n";
