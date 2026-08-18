<?php
$files = [
    'd:/pulic_html/resources/views/super-admin/design/index.blade.php',
    'd:/pulic_html/resources/views/super-admin/catalogue/index.blade.php',
    'd:/pulic_html/resources/views/super-admin/product/index.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $lines = file($file);
    $changed = false;
    for ($i = 0; $i < count($lines); $i++) {
        if (strpos($lines[$i], 'id="filterSection"') !== false) {
            // Found filterSection. Let's find the closing form and the immediate closing div.
            for ($j = $i; $j < count($lines); $j++) {
                if (strpos($lines[$j], '</form>') !== false) {
                    // Check the next lines for closing divs
                    if (isset($lines[$j+1]) && trim($lines[$j+1]) === '</div>') {
                        if (isset($lines[$j+2]) && trim($lines[$j+2]) === '</div>') {
                            unset($lines[$j+2]);
                            $changed = true;
                        }
                    }
                    break;
                }
            }
        }
    }
    if ($changed) {
        file_put_contents($file, implode('', $lines));
        echo "Fixed extra div in $file\n";
    }
}
