<?php

namespace App\Console\Commands;

use App\Models\Entry;
use App\Models\User;
use App\Services\EmbeddingService;
use Illuminate\Console\Command;

class EmbedEntriesCommand extends Command
{
    protected $signature = 'entries:embed 
                            {--user= : Only embed entries for a specific user ID} 
                            {--force : Re-embed entries that already have an embedding}';

    protected $description = 'Generate and backfill semantic vector embeddings for journal entries';

    public function handle(EmbeddingService $embeddingService): int
    {
        $userId = $this->option('user');
        $force = (bool) $this->option('force');

        $query = Entry::query()->with(['tags', 'user']);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if (!$force) {
            $query->whereDoesntHave('embedding');
        }

        $total = $query->count();

        if ($total === 0) {
            $this->info('No entries require embedding generation.');
            return Command::SUCCESS;
        }

        $this->info("Found {$total} entries to embed.");
        $progressBar = $this->output->createProgressBar($total);

        $successCount = 0;
        $failedCount = 0;

        $query->chunkById(50, function ($entries) use ($embeddingService, $progressBar, &$successCount, &$failedCount) {
            foreach ($entries as $entry) {
                try {
                    $result = $embeddingService->updateEntryEmbedding($entry);
                    if ($result) {
                        $successCount++;
                    } else {
                        $failedCount++;
                    }
                } catch (\Throwable $e) {
                    $failedCount++;
                }

                $progressBar->advance();

                // Gentle delay to respect API rate limits on bulk runs
                usleep(150000); // 150ms
            }
        });

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Completed embedding run: {$successCount} successfully embedded, {$failedCount} skipped or failed.");

        return Command::SUCCESS;
    }
}
