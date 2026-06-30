<?php

require __DIR__ . '/../vendor/autoload.php';

$controller = new \ReflectionClass(\App\Http\Controllers\API\Common\DesignController::class);

if ($controller->hasMethod('favourite')) {
    echo "<h2 style='color:green'>✅ DesignController has 'favourite' method</h2>";
} else {
    echo "<h2 style='color:red'>❌ DesignController is MISSING 'favourite' method</h2>";
    echo "<p>You need to upload the updated <b>app/Http/Controllers/API/Common/DesignController.php</b> file.</p>";
}

echo "<hr><h3>All methods in DesignController:</h3><ul>";
foreach ($controller->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
    if ($method->class === 'App\Http\Controllers\API\Common\DesignController') {
        echo "<li>" . $method->getName() . "()</li>";
    }
}
echo "</ul>";
