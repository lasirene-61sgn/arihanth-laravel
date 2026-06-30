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

    public function __construct($tempPath, $forcedBPCode = null){
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
        elseif (Auth::check())                        $creatorId = Auth::id(); // Fallback for Sanctum/API

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
        if(!empty($row['image_name'])){
            $imageFileName = $row['image_name'];
            $filePath = $this->tempPath . '/' . $imageFileName;


            if(file_exists($filePath)){
                $watermarkService = new ImageWatermarkService();

                $newFileName = time() . '_' . $imageFileName;
                $storagePath = 'products/' . $newFileName;
                Storage::disk('public')->put($storagePath, file_get_contents($filePath));
                    $watermarkPath = $watermarkService->addWatermark($storagePath);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $watermarkPath,
                    ]);
            }
        }
        return $product;
    }
}
