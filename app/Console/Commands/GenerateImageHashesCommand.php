<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WorkOrder;
use App\Models\Product;
use App\Models\Design;
use App\Models\Craftman;
use App\Models\Buyer;
use App\Models\PurchaseOrder;
use App\Models\Catalogue;
use App\Models\ImageHash;
use Jenssegers\ImageHash\ImageHash as Hasher;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use Illuminate\Support\Facades\Storage;

class GenerateImageHashesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-image-hashes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate perceptual hashes for existing images to enable visual search';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hasher = new Hasher(new DifferenceHash());
        
        $this->info('Starting image hash generation...');

        // Define models and their respective image fields
        $models = [
            [
                'class' => Product::class,
                'image_field' => 'product_image',
                'name' => 'Products'
            ],
            [
                'class' => WorkOrder::class,
                'image_field' => 'product_image',
                'name' => 'Work Orders'
            ],
            [
                'class' => Design::class,
                'image_field' => 'image',
                'name' => 'Designs'
            ],
            [
                'class' => Craftman::class,
                'image_field' => 'image',
                'name' => 'Craftsmen'
            ],
            [
                'class' => Buyer::class,
                'image_field' => 'image',
                'name' => 'Buyers'
            ],
            [
                'class' => PurchaseOrder::class,
                'image_field' => 'items', // Special handling
                'name' => 'Purchase Orders'
            ],
            [
                'class' => Catalogue::class,
                'image_field' => 'add_image',
                'name' => 'Catalogues'
            ]
        ];

        $hashImage = function($hasher, $modelClass, $itemId, $imagePath) {
            $exists = ImageHash::where('hashable_type', $modelClass)
                ->where('hashable_id', $itemId)
                ->where('file_path', $imagePath) // Check specific file_path for models with multiple images
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
                            'hashable_type' => $modelClass,
                            'hashable_id' => $itemId,
                            'hash' => $hash->toHex(),
                            'file_path' => $imagePath
                        ]);
                    }
                } catch (\Exception $e) {
                    $this->error("\nFailed to hash image for {$modelClass} ID {$itemId}: " . $e->getMessage());
                }
            }
        };

        foreach ($models as $modelData) {
            $this->info("Processing {$modelData['name']}...");
            
            try {
                if ($modelData['class'] === PurchaseOrder::class) {
                    $items = PurchaseOrder::whereNotNull('items')->get();
                } elseif ($modelData['class'] === Catalogue::class) {
                    try {
                        $items = Catalogue::whereNotNull('add_image')->where('add_image', '!=', '')->get();
                    } catch (\Exception $e) {
                        $items = collect(); // Ignore if table doesn't exist
                    }
                } else {
                    $items = $modelData['class']::whereNotNull($modelData['image_field'])
                        ->where($modelData['image_field'], '!=', '')
                        ->get();
                }
                    
                $bar = $this->output->createProgressBar(count($items));
                
                foreach ($items as $item) {
                    if ($modelData['class'] === PurchaseOrder::class) {
                        $poItems = $item->items ?? [];
                        if (is_string($poItems)) { $poItems = json_decode($poItems, true); }
                        if (is_array($poItems)) {
                            foreach ($poItems as $poItem) {
                                if (isset($poItem['image']) && !empty($poItem['image'])) {
                                    $hashImage($hasher, $modelData['class'], $item->id, $poItem['image']);
                                }
                            }
                        }
                    } else {
                        $imagePath = $item->{$modelData['image_field']};
                        $hashImage($hasher, $modelData['class'], $item->id, $imagePath);
                    }
                    
                    $bar->advance();
                }
                
                $bar->finish();
                $this->newLine();
                
            } catch (\Exception $e) {
                $this->error("\nError processing {$modelData['name']}: " . $e->getMessage());
            }
        }
        
        $this->info('Finished generating image hashes.');
    }
}
