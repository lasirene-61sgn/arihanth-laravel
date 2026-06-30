<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageWatermarkService
{
    /**
     * Adds the ajlogo.png watermark.
     * @param string $imagePath
     * @param bool $isPublicPath Set to true for Work Orders (public/ folder)
     */
    public function addWatermark($imagePath, $isPublicPath = false)
    {
        // Determine the full system path
        if ($isPublicPath) {
            // Used for Work Orders: public/images/work-orders/...
            $fullPath = public_path($imagePath);
        } else {
            // Used for Products: storage/app/public/products/...
            $fullPath = storage_path('app/public/' . $imagePath);
        }

        $watermarkPath = public_path('images/ajlogo.png');

        // Safety check to ensure files exist before processing
        if (!file_exists($fullPath) || !file_exists($watermarkPath)) {
            Log::warning("Watermark file missing at: " . $fullPath);
            return $imagePath;
        }

        try {
            // Initialize Manager (Intervention v3)
            $manager = new ImageManager(new Driver());
            $img = $manager->read($fullPath);
            $watermark = $manager->read($watermarkPath);

            // Scale logo to 10% of image width
            $watermark->scale(width: $img->width() * 0.1);

            // Place logo at top-left with padding
            $img->place($watermark, 'top-left', 20, 20);

            // Save the result
            $img->save($fullPath);
        } catch (\Exception $e) {
            Log::error("Watermark failed for {$imagePath}: " . $e->getMessage());
        }

        return $imagePath;
    }
}
