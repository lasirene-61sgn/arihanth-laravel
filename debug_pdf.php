<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
if ($user) Auth::login($user);

$controller = new \App\Http\Controllers\API\Common\WorkOrderController();

// Get the latest work order that was imported
$wo = App\Models\WorkOrder::whereNotNull('product_image')
    ->latest()
    ->first();

// call private method using reflection
$reflection = new \ReflectionClass($controller);
$method = $reflection->getMethod('transformWorkOrderResponse');
$method->setAccessible(true);
$workOrder = $method->invoke($controller, $wo);

$images = [];
// Collect images from gallery_images (already strings)
if (isset($workOrder['gallery_images']) && is_array($workOrder['gallery_images']) && !empty($workOrder['gallery_images'])) {
    $images = array_merge($images, $workOrder['gallery_images']);
}

// Collect images from completion_proof_images (already strings)
if (isset($workOrder['completion_proof_images']) && is_array($workOrder['completion_proof_images']) && !empty($workOrder['completion_proof_images'])) {
    $images = array_merge($images, $workOrder['completion_proof_images']);
}

// Fallback to legacy images array if the above are empty
if (empty($images) && isset($workOrder['images']) && is_array($workOrder['images'])) {
    foreach ($workOrder['images'] as $img) {
        if (is_string($img)) {
            $images[] = $img;
        } elseif (is_array($img) && isset($img['image_url'])) {
            $images[] = $img['image_url'];
        }
    }
}

// Final fallback to single image fields
if (empty($images)) {
    if (!empty($workOrder['preview_image_url']) && is_string($workOrder['preview_image_url'])) {
        $images[] = $workOrder['preview_image_url'];
    } elseif (!empty($workOrder['product_image_url']) && is_string($workOrder['product_image_url'])) {
        $images[] = $workOrder['product_image_url'];
    } elseif (!empty($workOrder['product_image']) && is_string($workOrder['product_image'])) {
        $images[] = $workOrder['product_image'];
    }
}

$images = array_unique($images);

echo "Initial images array:\n";
print_r($images);

$base64Images = [];
foreach ($images as $imgUrl) {
    if (!$imgUrl || !is_string($imgUrl)) continue;

    if (str_ends_with(strtolower($imgUrl), '.pdf')) continue;

    $fullPath = '';

    if (file_exists($imgUrl) && is_file($imgUrl)) {
        $fullPath = $imgUrl;
    } else {
        $assetUrl = asset('');
        $relativePath = str_replace($assetUrl, '', $imgUrl);
        
        if (filter_var($imgUrl, FILTER_VALIDATE_URL)) {
            $parsedUrl = parse_url($imgUrl);
            $relativePath = ltrim($parsedUrl['path'] ?? '', '/');
        }

        $fullPath = public_path(ltrim($relativePath, '/'));

        if (!file_exists($fullPath) || !is_file($fullPath)) {
            $storagePath = str_replace('storage/', '', ltrim($relativePath, '/'));
            $fullPath = storage_path('app/public/' . ltrim($storagePath, '/'));
        }

        if (!file_exists($fullPath) || !is_file($fullPath)) {
            $fullPath = public_path('storage/' . ltrim($relativePath, '/'));
        }
    }

    echo "Checking full path: $fullPath\n";

    if (file_exists($fullPath) && is_file($fullPath) && @getimagesize($fullPath)) {
        echo "Found local file!\n";
        $type = pathinfo($fullPath, PATHINFO_EXTENSION);
        $data = file_get_contents($fullPath);
        $base64Images[] = 'data:image/' . $type . ';base64,' . base64_encode(substr($data, 0, 10)); // Print just a snippet to check
    } elseif (filter_var($imgUrl, FILTER_VALIDATE_URL)) {
        echo "Trying remote download for $imgUrl\n";
        try {
            $context = stream_context_create(['http' => ['ignore_errors' => true]]);
            $data = @file_get_contents($imgUrl, false, $context);
            if ($data && @imagecreatefromstring($data)) {
                echo "Downloaded remote file!\n";
                $type = pathinfo(parse_url($imgUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
                if (!$type) $type = 'jpg';
                $base64Images[] = 'data:image/' . $type . ';base64,' . base64_encode(substr($data, 0, 10));
            }
        } catch (\Exception $e) {}
    } else {
        echo "Failed to resolve image.\n";
    }
}

echo "Resulting base64 images count: " . count($base64Images) . "\n";
