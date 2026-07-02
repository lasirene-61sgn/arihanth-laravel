<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Mock Auth
$user = new App\Models\User();
$user->id = 1;
// Mock hasPermission using macro if possible, or just mock the class
// Instead of full render, let's just render the content block or grep the JS we care about!
$content = file_get_contents('resources/views/admin/work-order/edit.blade.php');
// just run a regex to extract initializeSubcategory function block
preg_match('/function initializeSubcategory\(\).*?^\s*}/sm', $content, $matches);
echo "JS block:\n";
echo $matches[0] ?? "Not found";
