<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\CraftsmanStaff::first();
if (!$user) {
    echo "No craftsman staff found.\n";
    exit;
$app->instance('Illuminate\Foundation\Http\Middleware\VerifyCsrfToken', new class {
    public function handle($request, $next) { return $next($request); }
});
auth()->guard('craftsman_staff')->login($user);

$req = Illuminate\Http\Request::create('/craftsman-staff/global-search', 'POST', []);
// Simulate file upload
$file = new Illuminate\Http\UploadedFile(
    __FILE__, // just use a dummy file
    'dummy.jpg',
    'image/jpeg',
    null,
    true // test mode
);
$req->files->set('image_search', $file);

$req->headers->set('X-Requested-With', 'XMLHttpRequest');
$req->headers->set('Accept', 'application/json');
$req->setMethod('POST');

$res = app()->handle($req);

echo "Status: " . $res->getStatusCode() . "\n";
if ($res->getStatusCode() !== 200) {
    echo substr($res->getContent(), 0, 1000);
} else {
    echo "Success: " . substr($res->getContent(), 0, 500);
}
