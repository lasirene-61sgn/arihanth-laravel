<?php
$file = 'd:/pulic_html/app/Http/Controllers/SuperAdmin/DesignController.php';
$content = file_get_contents($file);
$content = str_replace("compact('products', 'categories', 'statusCounts', 'activeTab')", "compact('products', 'categories', 'statusCounts', 'activeTab', 'buyers', 'craftsmen', 'subCategories')", $content);
file_put_contents($file, $content);

$file = 'd:/pulic_html/app/Http/Controllers/SuperAdmin/CatalogueController.php';
$content = file_get_contents($file);
$content = str_replace("compact('products', 'categories', 'bpCodes')", "compact('products', 'categories', 'bpCodes', 'buyers', 'craftsmen', 'subCategories')", $content);
file_put_contents($file, $content);

echo "Fixed compact calls.";
