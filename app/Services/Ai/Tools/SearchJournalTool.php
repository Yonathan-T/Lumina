<?php

namespace App\Services\Ai\Tools;

use App\Models\Entry;
use App\Models\User;
use App\Services\EntrySearchService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SearchJournalTool implements ToolInterface
{
    protected EntrySearchService $searchService;

    public function __construct(?EntrySearchService $searchService = null)
    {
        $this->searchService = $searchService ?? app(EntrySearchService::class);
    }

    public function name(): string
    {
        return 'search_journal';
    }

    public function definition(): array
    {
        return [
            'name' => 'search_journal',
            'description' => 'Search the user\'s past journal entries and memories by keyword, topic, emotion, or time period to recall specific events, feelings, or entries.',
            'parameters' => [
                'type' => 'OBJECT',
                'properties' => [
                    'query' => [
                        'type' => 'STRING',
                        'description' => 'Keywords, feelings, person names, or topics to search for in past journal entries.',
                    ],
                    'days_ago' => [
                        'type' => 'INTEGER',
                        'description' => 'Optional filter to search only within the last N days (e.g. 7 for past week, 30 for past month).',
                    ],
                ],
                'required' => ['query'],
            ],
        ];
    }

    public function execute(array $args, User $user): array
    {
        $query = trim((string) ($args['query'] ?? ''));
        $daysAgo = isset($args['days_ago']) ? (int) $args['days_ago'] : null;

        if ($query === '') {
            return [
                'status' => 'empty_query',
                'entries' => [],
                'message' => 'No search query provided.',
            ];
        }

        try {
            // Find matches using existing encrypted search index & decryption
            $matchedIds = $this->searchService->findMatchingEntryIds($user->id, $query, 10);

            $queryBuilder = Entry::where('user_id', $user->id)->with('tags:id,name');

            if ($matchedIds->isNotEmpty()) {
                $queryBuilder->whereIn('id', $matchedIds);
            } else {
                // Fallback: check recent entries if search index produced no hits
                $queryBuilder->latest();
            }

            if ($daysAgo && $daysAgo > 0) {
                $queryBuilder->where('created_at', '>=', now()->subDays($daysAgo));
            }

            $entries = $queryBuilder->limit(3)->get();

            if ($entries->isEmpty()) {
                return [
                    'status' => 'not_found',
                    'count' => 0,
                    'message' => "No entries found matching '{$query}'.",
                ];
            }

            $formatted = $entries->map(function (Entry $entry) {
                $content = (string) $entry->content;
                $cleanContent = Str::limit(strip_tags($content), 350, '...');

                return [
                    'id' => $entry->id,
                    'title' => (string) $entry->title,
                    'date' => $entry->created_at ? $entry->created_at->format('M j, Y \a\t g:i A') : 'Unknown',
                    'snippet' => $cleanContent,
                    'tags' => $entry->tags->pluck('name')->all(),
                ];
            })->all();

            return [
                'status' => 'success',
                'count' => count($formatted),
                'entries' => $formatted,
            ];
        } catch (\Throwable $e) {
            Log::error('SearchJournalTool execution failed: ' . $e->getMessage());

            return [
                'status' => 'error',
                'message' => 'Unable to search journal entries at this moment.',
            ];
        }
    }
}
