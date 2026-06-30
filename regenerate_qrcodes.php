<?php

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Force refresh config in case of cache issues
config(['app.url' => env('APP_URL')]);

$products = Product::where('design_status', 'Accepted')->get();

echo "Generating QR codes using APP_URL: " . config('app.url') . "\n";
echo "Total designs to process: " . $products->count() . "\n";

if (!Storage::disk('public')->exists('qrcodes')) {
    Storage::disk('public')->makeDirectory('qrcodes');
}

foreach ($products as $product) {
    $designCode = $product->design_code;
    if (!$designCode) continue;

    // Use relative path to avoid host issues
    $qrUrl = rtrim(config('app.url'), '/') . '/super-admin/design/' . $product->id;

    $qrPath = null;
    try {
        // Try PNG first
        try {
            $qrPath = 'qrcodes/' . $designCode . '.png';
            QrCode::format('png')->size(300)->margin(2)
                ->generate($qrUrl, storage_path('app/public/' . $qrPath));
        } catch (\Exception $e) {
            // Fallback to SVG
            $qrPath = 'qrcodes/' . $designCode . '.svg';
            $svgContent = QrCode::format('svg')->size(300)->margin(2)->generate($qrUrl);
            Storage::disk('public')->put($qrPath, $svgContent);
        }

        $product->update(['qr_code' => $qrPath]);
        
        \App\Models\Design::updateOrCreate(
            ['product_id' => $product->id],
            [
                'design_code' => $designCode,
                'design_name' => $product->product_name,
                'image' => $product->images->first() ? $product->images->first()->path : null,
                'qr_code' => $qrPath,
            ]
        );
        
        echo "Updated $designCode -> $qrUrl\n";
    } catch (\Exception $e) {
        echo "Failed for $designCode: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";
