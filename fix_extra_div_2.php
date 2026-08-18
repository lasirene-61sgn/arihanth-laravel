<?php
$file = 'd:/pulic_html/resources/views/super-admin/catalogue/index.blade.php';
if (file_exists($file)) {
    $content = file_get_contents($file);
    $content = preg_replace('/<\/form>\s*<\/div>\s*<\/div>/', "</form>\n    </div>", $content);
    file_put_contents($file, $content);
    echo "Fixed catalogue";
}
$file2 = 'd:/pulic_html/resources/views/super-admin/product/index.blade.php';
if (file_exists($file2)) {
    $content = file_get_contents($file2);
    $content = preg_replace('/<\/form>\s*<\/div>\s*<\/div>/', "</form>\n    </div>", $content);
    file_put_contents($file2, $content);
    echo "Fixed product";
}
