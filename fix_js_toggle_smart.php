<?php
$files = [
    'd:/pulic_html/resources/views/super-admin/design/index.blade.php',
    'd:/pulic_html/resources/views/super-admin/catalogue/index.blade.php'
];

$oldJS = "el.classList.toggle('hidden');\n                el.classList.toggle('tw-hidden');";
$newJS = "if (el.classList.contains('hidden') || el.classList.contains('tw-hidden')) {\n                    el.classList.remove('hidden', 'tw-hidden');\n                } else {\n                    el.classList.add('hidden', 'tw-hidden');\n                }";

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace($oldJS, $newJS, $content);
        
        // Just in case oldJS had different spacing:
        $content = preg_replace("/el\.classList\.toggle\('hidden'\);\s*el\.classList\.toggle\('tw-hidden'\);/s", $newJS, $content);
        
        file_put_contents($file, $content);
        echo "Fixed JS in " . $file . "\n";
    }
}
