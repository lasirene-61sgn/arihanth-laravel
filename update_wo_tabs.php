<?php
$file = 'd:/pulic_html/resources/views/admin/work-order/index.blade.php';
$lines = file($file);
$currentTab = 'new-orders'; // Default

foreach ($lines as $i => $line) {
    if (preg_match('/x-show="activeTab === \'([^\']+)\'"/', $line, $matches)) {
        $currentTab = $matches[1];
    }
    
    if (strpos($line, 'return_url\' => url()->full()') !== false) {
        $lines[$i] = str_replace(
            "url()->full()",
            "route('admin.work-order.index', array_merge(request()->query(), ['tab' => '$currentTab']))",
            $line
        );
    }
}

file_put_contents($file, implode("", $lines));
echo "Updated work-order index return_url logic\n";
