<?php

// Check syntax of modified files
$files = [
    'app/Http/Controllers/API/Common/ProductController.php',
    'app/Models/Product.php',
];

foreach ($files as $file) {
    $path = "d:/Company Projects/Lasirene/erp/erp/" . $file;
    exec("php -l \"$path\"", $output, $return);
    if ($return !== 0) {
        echo "Syntax error in $file\n";
        echo implode("\n", $output) . "\n";
    } else {
        echo "Syntax OK: $file\n";
    }
    $output = [];
}

echo "Verification complete.\n";
