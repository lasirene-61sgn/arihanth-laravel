<?php

$apiFile = __DIR__ . '/../routes/api.php';
$content = file_get_contents($apiFile);

if (str_contains($content, 'designs.favourite')) {
    echo "<h2 style='color:green'>✅ routes/api.php HAS 'designs.favourite' route</h2>";
} else {
    echo "<h2 style='color:red'>❌ routes/api.php is MISSING 'designs.favourite' route</h2>";
    echo "<p>You need to upload the updated <b>routes/api.php</b> file.</p>";
}

echo "<hr><h3>Routes file lines containing 'favourite':</h3><pre>";
$lines = explode("\n", $content);
foreach ($lines as $num => $line) {
    if (stripos($line, 'favourite') !== false) {
        echo "Line " . ($num + 1) . ": " . htmlspecialchars($line) . "\n";
    }
}
echo "</pre>";
