<?php
$content = file_get_contents('e:/pulic_html/resources/views/admin/work-order/index.blade.php');
$lines = explode("\n", $content);
foreach ($lines as $index => $line) {
    if (strpos($line, "route('admin.work-order.edit") !== false) {
        echo "Line " . ($index + 1) . ": " . trim($line) . "\n";
        // Print 5 lines before
        for ($i = max(0, $index - 5); $i < $index; $i++) {
            echo "  " . trim($lines[$i]) . "\n";
        }
    }
}
