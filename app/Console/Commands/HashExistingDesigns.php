<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Design;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;

class HashExistingDesigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'designs:hash';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and store image hashes for existing designs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hasher = new ImageHash(new DifferenceHash());
        
        $designs = Design::whereNotNull('image')->whereNull('image_hash')->get();
        
        $this->info("Found " . $designs->count() . " designs to hash.");
        
        $bar = $this->output->createProgressBar($designs->count());
        $bar->start();
        
        foreach ($designs as $design) {
            $imagePath = storage_path('app/public/' . $design->image);
            
            if (file_exists($imagePath)) {
                try {
                    $hash = $hasher->hash($imagePath);
                    $design->update(['image_hash' => $hash->toHex()]);
                } catch (\Exception $e) {
                    $this->error("Failed to hash design {$design->id}: " . $e->getMessage());
                }
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Hashing completed!");
    }
}
