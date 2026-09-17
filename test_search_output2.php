<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$request = Illuminate\Http\Request::create('/admin/global-search', 'GET', ['search' => 'a']);
$request->headers->set('X-Requested-With', 'XMLHttpRequest');

$controller = app(App\Http\Controllers\Admin\GlobalSearchController::class);
$response = $controller->index($request);

$data = json_decode($response->getContent(), true);
if (isset($data['results'])) {
    foreach ($data['results'] as $groupTitle => $group) {
        echo $groupTitle . ":\n";
        foreach (array_slice($group['items'], 0, 2) as $item) {
            echo "  - " . $item['display'] . " | Image: " . ($item['image'] ?? 'NULL') . "\n";
        }
    }
} else {
    echo "NO RESULTS. response was:\n";
    echo substr($response->getContent(), 0, 500);
}
