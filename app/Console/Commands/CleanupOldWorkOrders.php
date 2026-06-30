<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\WorkOrder;
use Carbon\Carbon;

class CleanupOldWorkOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-old-work-orders {--dry-run : Only show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete work orders older than one year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoffDate = Carbon::now()->subYear();
        $query = WorkOrder::where('created_at', '<', $cutoffDate);
        
        $count = $query->count();
        
        if ($count === 0) {
            $this->info("No work orders found older than one year ($cutoffDate).");
            return;
        }

        if ($this->option('dry-run')) {
            $this->info("DRY RUN: $count work orders would be deleted.");
            return;
        }

        $this->info("Deleting $count work orders older than one year...");
        $query->delete();
        $this->info("Successfully deleted $count work orders.");
    }
}
