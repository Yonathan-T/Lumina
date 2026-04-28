<?php

namespace App\Services;

use App\Models\Entry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EntrySearchService
{
    public function updateEntryIndex(Entry $entry): void
    {
        $terms = $this->buildTermsForEntry($entry);

        DB::transaction(function () use ($entry, $terms) {
            DB::table('entry_search_terms')->where('entry_id', $entry->id)->delete();

            if (empty($terms)) {
                return;
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
        });
    }

    public function findMatchingEntryIds(int $userId, string $query, int $limit = 10): Collection
    {
        $tokens = $this->extractSearchTokens($query, 8);

        if (empty($tokens)) {
            return collect();
        }

        return DB::table('entry_search_terms')
            ->where('user_id', $userId)
            ->whereIn('term', $tokens)
            ->select('entry_id', DB::raw('COUNT(DISTINCT term) as matched_terms'))
            ->groupBy('entry_id')
            ->having('matched_terms', '>=', count($tokens))
            ->orderByDesc('matched_terms')
            ->limit($limit)
            ->pluck('entry_id');
    }

    public function buildTermsForEntry(Entry $entry): array
    {
        $segments = [
            (string) $entry->title,
            (string) $entry->content,
            $entry->relationLoaded('tags')
                ? $entry->tags->pluck('name')->implode(' ')
                : $entry->tags()->pluck('name')->implode(' '),
        ];

        $tokens = [];

        foreach ($segments as $segment) {
            foreach ($this->extractSearchTokens($segment, 150) as $token) {
                $tokens[$token] = true;

                $length = strlen($token);
                $prefixMax = min($length, 10);

                for ($i = 2; $i <= $prefixMax; $i++) {
                    $tokens[substr($token, 0, $i)] = true;
                }
            }
        }

        return array_slice(array_keys($tokens), 0, 500);
    }

    public function extractSearchTokens(string $text, int $limit = 8): array
    {
        $normalized = mb_strtolower($text, 'UTF-8');
        $normalized = preg_replace('/[^\pL\pN]+/u', ' ', $normalized) ?? '';

        $parts = preg_split('/\s+/u', trim($normalized)) ?: [];

        $tokens = array_values(array_filter(array_unique($parts), function ($token) {
            $length = strlen($token);

            return $length >= 2 && $length <= 64;
        }));

        return array_slice($tokens, 0, $limit);
    }
}
