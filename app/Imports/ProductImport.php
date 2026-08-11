<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageWatermarkService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImport implements ToModel, WithHeadingRow
{
    protected $tempPath;
    protected $forcedBPCode;

    public function __construct($tempPath, $forcedBPCode = null)
    {
        $this->tempPath = $tempPath;
        $this->forcedBPCode = $forcedBPCode;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $creatorId = null;
        if (Auth::guard('super_admin')->check())      $creatorId = Auth::guard('super_admin')->id();
        elseif (Auth::guard('admin')->check())         $creatorId = Auth::guard('admin')->id();
        elseif (Auth::guard('buyer')->check())         $creatorId = Auth::guard('buyer')->id();
        elseif (Auth::guard('craftsman')->check())     $creatorId = Auth::guard('craftsman')->id();
        elseif (Auth::check())                         $creatorId = Auth::id(); // Fallback for Sanctum/API

        $productcode = !empty($row['product_code']) ? $row['product_code'] : Product::generateProductCode();

        $product = Product::create([
            'product_code' => $productcode,
            'product_name' => $row['product_name'],
            'bp_code' => $this->forcedBPCode ?? $row['bp_code'] ?? $row['craftman_code'] ?? null,
            'product_category_id' => $row['category_id'],
            'product_subcategory_id' => $row['subcategory_id'] ?? null,
            'type' => $row['type'],
            'size' => $row['size'] ?? null,
            'weight_from' => $row['weight_from'],
            'weight_to' => $row['weight_to'] ?? null,
            'created_by' => $creatorId,
        ]);

        // 1. Determine the image filename base (either from image_name column or product_code)
        $targetFileName = !empty($row['image_name']) ? $row['image_name'] : $productcode;

        // 2. Find the actual file in the unzipped directory
        $foundFilePath = $this->findImageFile($targetFileName);

        if ($foundFilePath && file_exists($foundFilePath)) {
            $watermarkService = new ImageWatermarkService();

            $extension = pathinfo($foundFilePath, PATHINFO_EXTENSION);
            $newFileName = time() . '_' . $productcode . '.' . $extension;
            $storagePath = 'products/' . $newFileName;

            // Save original image to public disk
            Storage::disk('public')->put($storagePath, file_get_contents($foundFilePath));

            // Apply watermark service
            $watermarkPath = $watermarkService->addWatermark($storagePath);

            // Create record in product_images table
            ProductImage::create([
                'product_id' => $product->id,
                'path'       => $watermarkPath,
            ]);
        }

        return $product;
    }

    /**
     * Helper method to locate the image file even if extension is omitted in Excel
     */
    private function findImageFile($fileName)
    {
        // Search recursively inside $this->tempPath for the image file
        $dirIterator = new \RecursiveDirectoryIterator($this->tempPath, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($dirIterator);

        $extensions = ['jpg', 'jpeg', 'png', 'webp', 'JPG', 'JPEG', 'PNG', 'WEBP'];

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filenameWithoutExt = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $ext = pathinfo($file->getFilename(), PATHINFO_EXTENSION);

                // Match exact filename (with or without extension)
                if ($filenameWithoutExt === $fileName && in_array($ext, $extensions)) {
                    return $file->getRealPath();
                }
            }
        }

        return null;
    }
}
