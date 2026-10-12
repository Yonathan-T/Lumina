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
        $cleanQuery = trim($query);
        if (strlen($cleanQuery) < 2) {
            return collect();
        }

        $cleanTagQuery = ltrim($cleanQuery, '#');
        $tokens = $this->extractSearchTokens($cleanQuery, 8);

        try {
            $candidateIds = collect();

            if (!empty($tokens)) {
                $termIds = DB::table('entry_search_terms')
                    ->where('user_id', $userId)
                    ->whereIn('term', $tokens)
                    ->pluck('entry_id');
                $candidateIds = $candidateIds->merge($termIds);
            }

            // Also find candidates by tags (tags are not encrypted)
            $tagIds = Entry::where('user_id', $userId)
                ->whereHas('tags', function ($tq) use ($cleanTagQuery) {
                    $tq->where('name', 'LIKE', "%{$cleanTagQuery}%");
                })
                ->pluck('id');
            $candidateIds = $candidateIds->merge($tagIds);

            // Check recent entries to ensure newly created or unindexed entries are always found
            $recentIds = Entry::where('user_id', $userId)
                ->latest()
                ->limit(50)
                ->pluck('id');
            $candidateIds = $candidateIds->merge($recentIds)->unique();

            if ($candidateIds->isEmpty()) {
                return collect();
            }

            // Load candidates - Eloquent decrypts 'title' and 'content' automatically
            $entries = Entry::where('user_id', $userId)
                ->whereIn('id', $candidateIds)
                ->with(['tags:id,name'])
                ->get();

            $lowerQuery = mb_strtolower($cleanQuery);
            $lowerTagQuery = mb_strtolower($cleanTagQuery);

            // Filter to entries that actually contain the search query in decrypted title, content, or tags
            $matchingEntries = $entries->filter(function ($entry) use ($lowerQuery, $lowerTagQuery) {
                $lowerTitle = mb_strtolower((string) $entry->title);
                $lowerContent = mb_strtolower((string) $entry->content);

                if (str_contains($lowerTitle, $lowerQuery) || str_contains($lowerContent, $lowerQuery)) {
                    return true;
                }

                return $entry->tags->contains(function ($tag) use ($lowerTagQuery) {
                    return str_contains(mb_strtolower($tag->name), $lowerTagQuery);
                });
            });

            if ($matchingEntries->isEmpty()) {
                return collect();
            }

            // Strict Relevance Weighting:
            // 1. Title match (100 - 150 points)
            // 2. Content match (50 points)
            // 3. Tag-only match (10 points)
            // Secondary sort: created_at timestamp
            return $matchingEntries->sortByDesc(function ($entry) use ($lowerQuery, $lowerTagQuery) {
                $score = 0;
                $lowerTitle = mb_strtolower((string) $entry->title);
                $lowerContent = mb_strtolower((string) $entry->content);

                if ($lowerTitle === $lowerQuery) {
                    $score += 150;
                } elseif (str_starts_with($lowerTitle, $lowerQuery)) {
                    $score += 120;
                } elseif (str_contains($lowerTitle, $lowerQuery)) {
                    $score += 100;
                }

                if (str_contains($lowerContent, $lowerQuery)) {
                    $score += 50;
                }

                $hasTagMatch = $entry->tags->contains(function ($tag) use ($lowerTagQuery) {
                    return str_contains(mb_strtolower($tag->name), $lowerTagQuery);
                });

                if ($hasTagMatch) {
                    $score += 10;
                }

                $timestamp = $entry->created_at ? $entry->created_at->timestamp : 0;

                return ($score * 10000000000) + $timestamp;
            })
            ->take($limit)
            ->pluck('id')
            ->values();

        } catch (\Throwable $e) {
            \Log::warning('Relevance search query error: ' . $e->getMessage());
            return collect();
        }
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
