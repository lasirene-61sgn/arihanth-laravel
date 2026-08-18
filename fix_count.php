<?php
$files = ['d:/pulic_html/resources/views/super-admin/freeze-account/index.blade.php', 'd:/pulic_html/resources/views/admin/freeze-account/index.blade.php'];
foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    $content = str_replace('allBuyers->count()', 'allBuyers->total()', $content);
    $content = str_replace('allCraftsmen->count()', 'allCraftsmen->total()', $content);
    $content = str_replace('allCraftsmanStaff->count()', 'allCraftsmanStaff->total()', $content);
    $content = str_replace('allAdmins->count()', 'allAdmins->total()', $content);
    $content = str_replace('allKeyUsers->count()', 'allKeyUsers->total()', $content);
    $content = str_replace('allUsers->count()', 'allUsers->total()', $content);
    file_put_contents($file, $content);
}
echo "Done.";
