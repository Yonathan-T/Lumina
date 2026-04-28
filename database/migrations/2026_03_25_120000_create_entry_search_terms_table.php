<?php

use App\Models\Entry;
use App\Services\EntrySearchService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entry_search_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('term', 64);

            $table->unique(['entry_id', 'term']);
            $table->index(['user_id', 'term']);
        });

        $searchService = app(EntrySearchService::class);

        Entry::with('tags')
            ->select(['id', 'user_id', 'title', 'content'])
            ->chunkById(100, function ($entries) use ($searchService) {
                foreach ($entries as $entry) {
                    $terms = $searchService->buildTermsForEntry($entry);

                    if (empty($terms)) {
                        continue;
                    }

                    $rows = array_map(function (string $term) use ($entry) {
                        return [
                            'entry_id' => $entry->id,
                            'user_id' => $entry->user_id,
                            'term' => $term,
                        ];
                    }, $terms);

                    foreach (array_chunk($rows, 500) as $chunk) {
                        DB::table('entry_search_terms')->insert($chunk);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('entry_search_terms');
    }
};
