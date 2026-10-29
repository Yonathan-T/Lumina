<?php

namespace App\Services\Ai\Tools;

use App\Models\Entry;
use App\Models\User;
use App\Services\EmbeddingService;
use App\Services\EntrySearchService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SearchJournalTool implements ToolInterface
{
    protected EntrySearchService $searchService;
    protected EmbeddingService $embeddingService;

    public function __construct(
        ?EntrySearchService $searchService = null,
        ?EmbeddingService $embeddingService = null
    ) {
        $this->searchService = $searchService ?? app(EntrySearchService::class);
        $this->embeddingService = $embeddingService ?? app(EmbeddingService::class);
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
            // 1. Keyword search (exact terms / token index)
            $matchedKeywordIds = $this->searchService->findMatchingEntryIds($user->id, $query, 10);

            // 2. Semantic vector search (conceptual meaning & themes)
            $semanticMatches = collect();
            try {
                $semanticMatches = $this->embeddingService->findSemanticMatches($user->id, $query, 10, 0.40, $user);
            } catch (\Throwable $e) {
                Log::warning('Semantic embedding search fallback in SearchJournalTool: ' . $e->getMessage());
            }

            // 3. Blend into hybrid scored list
            $scoredCandidates = [];

            // Add semantic matches
            foreach ($semanticMatches as $match) {
                $id = (int) $match['entry_id'];
                $scoredCandidates[$id] = [
                    'score' => (float) $match['score'],
                    'semantic_similarity' => (float) $match['score'],
                    'has_keyword' => false,
                ];
            }

            // Boost or insert exact keyword matches
            foreach ($matchedKeywordIds as $id) {
                $id = (int) $id;
                if (isset($scoredCandidates[$id])) {
                    $scoredCandidates[$id]['score'] += 0.35; // Boost combined match
                    $scoredCandidates[$id]['has_keyword'] = true;
                } else {
                    $scoredCandidates[$id] = [
                        'score' => 0.85,
                        'semantic_similarity' => null,
                        'has_keyword' => true,
                    ];
                }
            }

            $queryBuilder = Entry::where('user_id', $user->id)->with('tags:id,name');

            if (!empty($scoredCandidates)) {
                $candidateIds = array_keys($scoredCandidates);
                $queryBuilder->whereIn('id', $candidateIds);
            } else {
                // Fallback: recent entries if neither produced hits
                $queryBuilder->latest();
            }

            if ($daysAgo && $daysAgo > 0) {
                $queryBuilder->where('created_at', '>=', now()->subDays($daysAgo));
            }

            $entries = $queryBuilder->limit(6)->get();

            if ($entries->isEmpty()) {
                return [
                    'status' => 'not_found',
                    'count' => 0,
                    'message' => "No entries found matching '{$query}'.",
                ];
            }

            // Sort loaded entries by hybrid score if candidates existed
            if (!empty($scoredCandidates)) {
                $entries = $entries->sortByDesc(function (Entry $entry) use ($scoredCandidates) {
                    return $scoredCandidates[$entry->id]['score'] ?? 0;
                })->values();
            }

            // Limit to top 3 best matches
            $topEntries = $entries->take(3);

            $formatted = $topEntries->map(function (Entry $entry) use ($scoredCandidates) {
                $content = (string) $entry->content;
                $cleanContent = Str::limit(strip_tags($content), 350, '...');

                $meta = $scoredCandidates[$entry->id] ?? null;
                $matchType = 'recent';
                if ($meta) {
                    if ($meta['has_keyword'] && ($meta['semantic_similarity'] ?? 0) >= 0.65) {
                        $matchType = 'exact_and_concept';
                    } elseif ($meta['has_keyword']) {
                        $matchType = 'keyword_match';
                    } else {
                        $matchType = 'conceptual_similarity';
                    }
                }

                return [
                    'id' => $entry->id,
                    'title' => (string) $entry->title,
                    'date' => $entry->created_at ? $entry->created_at->format('M j, Y \a\t g:i A') : 'Unknown',
                    'snippet' => $cleanContent,
                    'tags' => $entry->tags->pluck('name')->all(),
                    'match_type' => $matchType,
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
