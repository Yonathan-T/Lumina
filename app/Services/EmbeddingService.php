<?php

namespace App\Services;

use App\Models\Entry;
use App\Models\EntryEmbedding;
use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    public const DEFAULT_MODEL = 'gemini-embedding-001';
    public const GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    /**
     * Resolve the Gemini API key to use for embeddings.
     */
    public function resolveApiKey(?User $user = null): ?string
    {
        if ($user && !empty($user->api_key)) {
            try {
                return Crypt::decryptString($user->api_key);
            } catch (DecryptException $e) {
                Log::warning("Failed to decrypt user Gemini API key for user {$user->id}: " . $e->getMessage());
            }
        }

        if (auth()->check()) {
            $currentUser = auth()->user();
            if ($currentUser && !empty($currentUser->api_key)) {
                try {
                    return Crypt::decryptString($currentUser->api_key);
                } catch (DecryptException $e) {
                    // ignore
                }
            }
        }

        return config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
    }

    /**
     * Build unified text representation for an entry.
     */
    public function prepareTextForEntry(Entry $entry): string
    {
        $tags = [];
        if ($entry->relationLoaded('tags')) {
            $tags = $entry->tags->pluck('name')->all();
        } else {
            try {
                $tags = $entry->tags()->pluck('name')->all();
            } catch (\Throwable $e) {
                $tags = [];
            }
        }

        $rawTitle = trim((string) $entry->title);
        $cleanContent = trim(strip_tags((string) $entry->content));

        $lines = [];
        if ($rawTitle !== '' && $rawTitle !== 'Untitled Reflection') {
            $lines[] = "Title: {$rawTitle}";
        }

        if (!empty($tags)) {
            $lines[] = "Tags: " . implode(', ', $tags);
        }

        if ($cleanContent !== '') {
            $lines[] = "Content:\n{$cleanContent}";
        }

        return implode("\n", $lines);
    }

    /**
     * Generate embedding vector from Gemini for the given text.
     *
     * @return float[]|null
     */
    public function generateEmbedding(string $text, ?User $user = null): ?array
    {
        $cleanText = trim($text);
        if ($cleanText === '') {
            return null;
        }

        $systemKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        $apiKey = $this->resolveApiKey($user) ?: $systemKey;

        if (empty($apiKey)) {
            Log::warning('EmbeddingService: No Gemini API key available to generate embedding.');
            return null;
        }

        $model = self::DEFAULT_MODEL;

        $fetchVector = function (string $key) use ($model, $cleanText): ?array {
            $url = self::GEMINI_API_URL . "/{$model}:embedContent?key={$key}";
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(10)->post($url, [
                'model' => "models/{$model}",
                'content' => [
                    'parts' => [
                        ['text' => $cleanText],
                    ],
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $values = $data['embedding']['values'] ?? null;
                if (is_array($values) && !empty($values)) {
                    return array_map('floatval', $values);
                }
            }

            Log::warning("EmbeddingService: Call with key failed (status {$response->status()})");
            return null;
        };

        try {
            $vector = $fetchVector($apiKey);
            if ($vector !== null) {
                return $vector;
            }

            // If user key was invalid/expired and system key is available, fallback to system key
            if ($systemKey && $apiKey !== $systemKey) {
                $fallbackVector = $fetchVector($systemKey);
                if ($fallbackVector !== null) {
                    return $fallbackVector;
                }
            }
        } catch (\Throwable $e) {
            Log::error('EmbeddingService: Exception during embedding generation: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Generate and store embedding vector for an entry.
     */
    public function updateEntryEmbedding(Entry $entry): ?EntryEmbedding
    {
        $text = $this->prepareTextForEntry($entry);

        if ($text === '') {
            EntryEmbedding::where('entry_id', $entry->id)->delete();
            return null;
        }

        $user = $entry->relationLoaded('user') ? $entry->user : User::find($entry->user_id);
        $vector = $this->generateEmbedding($text, $user);

        if ($vector === null || empty($vector)) {
            return null;
        }

        return EntryEmbedding::updateOrCreate(
            ['entry_id' => $entry->id],
            [
                'user_id' => $entry->user_id,
                'model' => self::DEFAULT_MODEL,
                'dimensions' => count($vector),
                'embedding' => $vector,
            ]
        );
    }

    /**
     * Compute cosine similarity between two numeric vectors.
     */
    public static function cosineSimilarity(array $vecA, array $vecB): float
    {
        $countA = count($vecA);
        $countB = count($vecB);

        if ($countA === 0 || $countA !== $countB) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $countA; $i++) {
            $a = (float) $vecA[$i];
            $b = (float) $vecB[$i];

            $dotProduct += $a * $b;
            $normA += $a * $a;
            $normB += $b * $b;
        }

        if ($normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }

    /**
     * Search user entries by semantic similarity to a query string.
     *
     * @return Collection<int, array{entry_id: int, score: float}>
     */
    public function findSemanticMatches(int $userId, string $query, int $limit = 5, float $minScore = 0.50, ?User $user = null): Collection
    {
        $cleanQuery = trim($query);
        if ($cleanQuery === '') {
            return collect();
        }

        $targetUser = $user ?? User::find($userId);
        $queryVector = $this->generateEmbedding($cleanQuery, $targetUser);

        if ($queryVector === null || empty($queryVector)) {
            return collect();
        }

        $storedEmbeddings = EntryEmbedding::where('user_id', $userId)
            ->select(['entry_id', 'embedding'])
            ->get();

        if ($storedEmbeddings->isEmpty()) {
            return collect();
        }

        $scored = [];
        foreach ($storedEmbeddings as $record) {
            $entryVector = $record->embedding;
            if (!is_array($entryVector) || empty($entryVector)) {
                continue;
            }

            $sim = self::cosineSimilarity($queryVector, $entryVector);
            if ($sim >= $minScore) {
                $scored[] = [
                    'entry_id' => (int) $record->entry_id,
                    'score' => round($sim, 4),
                ];
            }
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return collect(array_slice($scored, 0, $limit));
    }

    /**
     * Find related entries for a specific entry based on vector similarity.
     *
     * @return Collection<int, Entry>
     */
    public function findRelatedEntries(Entry $entry, int $limit = 4, float $minScore = 0.60): Collection
    {
        $embeddingRecord = $entry->embedding ?? EntryEmbedding::where('entry_id', $entry->id)->first();

        if (!$embeddingRecord) {
            $embeddingRecord = $this->updateEntryEmbedding($entry);
        }

        if (!$embeddingRecord || empty($embeddingRecord->embedding)) {
            return collect();
        }

        $sourceVector = $embeddingRecord->embedding;

        $candidates = EntryEmbedding::where('user_id', $entry->user_id)
            ->where('entry_id', '!=', $entry->id)
            ->select(['entry_id', 'embedding'])
            ->get();

        if ($candidates->isEmpty()) {
            return collect();
        }

        $scored = [];
        foreach ($candidates as $cand) {
            if (!is_array($cand->embedding) || empty($cand->embedding)) {
                continue;
            }

            $sim = self::cosineSimilarity($sourceVector, $cand->embedding);
            if ($sim >= $minScore) {
                $scored[$cand->entry_id] = round($sim, 4);
            }
        }

        if (empty($scored)) {
            return collect();
        }

        arsort($scored);
        $topEntryIds = array_slice(array_keys($scored), 0, $limit);

        $entries = Entry::whereIn('id', $topEntryIds)
            ->with(['tags:id,name'])
            ->get();

        // Attach similarity score to each entry and preserve ranking order
        return $entries->map(function (Entry $e) use ($scored) {
            $e->similarity_score = $scored[$e->id] ?? null;
            return $e;
        })->sortByDesc('similarity_score')->values();
    }
}
