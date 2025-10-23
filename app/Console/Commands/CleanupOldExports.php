<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanupOldExports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exports:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete export files older than 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of old export files...');

        $files = Storage::disk('local')->files('exports');
        $deletedCount = 0;
        $cutoffTime = Carbon::now()->subHours(24);

        foreach ($files as $file) {
            $lastModified = Carbon::createFromTimestamp(Storage::disk('local')->lastModified($file));
            
            if ($lastModified->lessThan($cutoffTime)) {
                Storage::disk('local')->delete($file);
                $deletedCount++;
                $this->line("Deleted: {$file}");
            }
        }

        $this->info("Cleanup complete! Deleted {$deletedCount} old export file(s).");
        
        return 0;
    }
}
