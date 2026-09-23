<?php

namespace App\Traits;

use App\Models\ImageHash;
use Illuminate\Support\Facades\Storage;
use Jenssegers\ImageHash\ImageHash as Hasher;
use Jenssegers\ImageHash\Implementations\DifferenceHash;

trait HasImageHash
{
    public static function bootHasImageHash()
    {
        static::saved(function ($model) {
            // Generate hashes after sending the response to keep the UI extremely fast
            if (function_exists('dispatch')) {
                dispatch(function () use ($model) {
                    $model->generateImageHashes();
                })->afterResponse();
            } else {
                $model->generateImageHashes();
            }
        });
    }

    public function generateImageHashes()
    {
        $hasher = new Hasher(new DifferenceHash());

        $hashImage = function($imagePath) use ($hasher) {
            if (empty($imagePath)) return;

            $exists = ImageHash::where('hashable_type', get_class($this))
                ->where('hashable_id', $this->id)
                ->where('file_path', $imagePath)
                ->exists();

            if (!$exists) {
                try {
                    $fullPath = null;
                    if (Storage::disk('public')->exists($imagePath)) {
                        $fullPath = Storage::disk('public')->path($imagePath);
                    } elseif (file_exists(public_path($imagePath))) {
                        $fullPath = public_path($imagePath);
                    } elseif (file_exists(public_path('storage/' . $imagePath))) {
                        $fullPath = public_path('storage/' . $imagePath);
                    }
                    
                    if ($fullPath && file_exists($fullPath)) {
                        $hash = $hasher->hash($fullPath);
                        
                        ImageHash::create([
                            'hashable_type' => get_class($this),
                            'hashable_id' => $this->id,
                            'hash' => $hash->toHex(),
                            'file_path' => $imagePath
                        ]);
                    }
                } catch (\Exception $e) {
                    // Ignore background hashing errors
                }
            }
        };

        $class = get_class($this);
        
        if ($class === \App\Models\PurchaseOrder::class) {
            $poItems = $this->items ?? [];
            if (is_string($poItems)) { $poItems = json_decode($poItems, true); }
            if (is_array($poItems)) {
                foreach ($poItems as $poItem) {
                    if (!empty($poItem['image'])) {
                        $hashImage($poItem['image']);
                    } else if (!empty($poItem['product_id'])) {
                        $product = \App\Models\Product::find($poItem['product_id']);
                        if ($product && !empty($product->product_image)) {
                            $hashImage($product->product_image);
                        }
                    }
                }
            }
        } elseif ($class === \App\Models\Product::class || $class === \App\Models\WorkOrder::class) {
            if (!empty($this->product_image)) $hashImage($this->product_image);
            if ($this->images && $this->images->count() > 0) {
                foreach ($this->images as $img) {
                    if (!empty($img->path)) $hashImage($img->path);
                }
            }
        } elseif ($class === \App\Models\Catalogue::class) {
            if (!empty($this->add_image)) $hashImage($this->add_image);
        } elseif ($class === \App\Models\Repair::class) {
            if (!empty($this->image_proof)) $hashImage($this->image_proof);
        } else {
            if (!empty($this->image)) $hashImage($this->image);
        }
    }
}
