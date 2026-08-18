<?php
$files = [
    'd:/pulic_html/resources/views/super-admin/design/index.blade.php',
    'd:/pulic_html/resources/views/super-admin/catalogue/index.blade.php'
];
foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace("el.classList.toggle('hidden');", "el.classList.toggle('hidden');\n                el.classList.toggle('tw-hidden');", $content);
        file_put_contents($file, $content);
        echo "Fixed toggleSection in " . $file . "\n";
    }
}
