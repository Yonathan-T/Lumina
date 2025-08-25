<?php

namespace App\Services;

use App\Models\Entry;
use App\Models\Tag;
use App\Services\StreakService;
use App\Livewire\Dashboard\DashboardStats;
use App\Livewire\Insights;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class UserDataService
{
    public function getRecentEntries(int $limit = 10): Collection
    {
        return Entry::where('user_id', Auth::id())
            ->with('tags')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getFirstEntry(): ?Entry
    {
        $firstEntry = Entry::where('user_id', Auth::id())
            ->with('tags')
            ->orderBy('created_at', 'asc')
            ->first();

        if ($firstEntry) {
            \Log::info('First entry query result:', [
                'id' => $firstEntry->id,
                'title' => $firstEntry->title,
                'created_at_raw' => $firstEntry->created_at,
                'created_at_string' => $firstEntry->created_at->toDateTimeString(),
                'user_id' => $firstEntry->user_id
            ]);
        }

        return $firstEntry;
    }

    public function getLastEntry(): ?Entry
    {
        return Entry::where('user_id', Auth::id())
            ->with('tags')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function searchEntries(string $query, int $limit = 5): Collection
    {
        return Entry::where('user_id', Auth::id())
            ->with('tags')
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('content', 'LIKE', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getEntriesByTag(string $tagName, int $limit = 5): Collection
    {
        return Entry::where('user_id', Auth::id())
            ->with('tags')
            ->whereHas('tags', function ($query) use ($tagName) {
                $query->where('name', 'LIKE', "%{$tagName}%");
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function formatEntriesForAI(Collection $entries): string
    {
        if ($entries->isEmpty()) {
            return "No previous journal entries found.";
        }

        $formattedEntries = $entries->map(function ($entry) {
            $tags = $entry->tags->pluck('name')->join(', ');
            $date = $entry->created_at->format('M j, Y');
            $time = $entry->created_at->format('g:i A');
            $fullDateTime = $entry->created_at->format('M j, Y \a\t g:i A');

            return "Entry from {$fullDateTime}" . ($tags ? " (Tags: {$tags})" : "") . ":\n" .
                "Title: {$entry->title}\n" .
                "Content: " . $entry->content . "\n";
        })->join("\n---\n");

        return "Recent journal entries:\n\n{$formattedEntries}";
    }

    public function getContextualEntries(string $message, int $limit = 5): Collection
    {
        // Extract keywords and search for relevant entries
        $keywords = $this->extractKeywords($message);

        if (empty($keywords)) {
            return $this->getRecentEntries($limit);
        }

        $relevantEntries = collect();

        foreach ($keywords as $keyword) {
            $entries = $this->searchEntries($keyword, 3);
            $relevantEntries = $relevantEntries->merge($entries);
        }

        return $relevantEntries->unique('id')->take($limit);
    }

    public function getAllEntriesForContext(): Collection
    {
        return Entry::where('user_id', Auth::id())
            ->with('tags')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function extractKeywords(string $message): array
    {
        $cleanMessage = strtolower(preg_replace('/[^\w\s]/', ' ', $message));

        $words = array_filter(explode(' ', $cleanMessage));

        $stopWords = [
            'i',
            'me',
            'my',
            'myself',
            'we',
            'our',
            'ours',
            'ourselves',
            'you',
            'your',
            'yours',
            'yourself',
            'yourselves',
            'he',
            'him',
            'his',
            'himself',
            'she',
            'her',
            'hers',
            'herself',
            'it',
            'its',
            'itself',
            'they',
            'them',
            'their',
            'theirs',
            'themselves',
            'what',
            'which',
            'who',
            'whom',
            'this',
            'that',
            'these',
            'those',
            'am',
            'is',
            'are',
            'was',
            'were',
            'be',
            'been',
            'being',
            'have',
            'has',
            'had',
            'having',
            'do',
            'does',
            'did',
            'doing',
            'a',
            'an',
            'the',
            'and',
            'but',
            'if',
            'or',
            'because',
            'as',
            'until',
            'while',
            'of',
            'at',
            'by',
            'for',
            'with',
            'through',
            'during',
            'before',
            'after',
            'above',
            'below',
            'up',
            'down',
            'in',
            'out',
            'on',
            'off',
            'over',
            'under',
            'again',
            'further',
            'then',
            'once',
            'here',
            'there',
            'when',
            'where',
            'why',
            'how',
            'all',
            'any',
            'both',
            'each',
            'few',
            'more',
            'most',
            'other',
            'some',
            'such',
            'no',
            'nor',
            'not',
            'only',
            'own',
            'same',
            'so',
            'than',
            'too',
            'very',
            'can',
            'will',
            'just',
            'should',
            'now',
            'feel',
            'feeling',
            'today',
            'yesterday',
            'tomorrow'
        ];

        $keywords = array_filter($words, function ($word) use ($stopWords) {
            return !in_array($word, $stopWords) && strlen($word) > 2;
        });

        return array_unique(array_values($keywords));
    }

    private function truncateText(string $text, int $length): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . '...';
    }


    public function getUserInsights(): array
    {
        $userId = Auth::id();

        $currentStreak = StreakService::getCurrentStreak($userId);
        $longestStreak = StreakService::getLongestStreak($userId);

        $totalEntries = Entry::where('user_id', $userId)->count();

        $mostUsedTag = Tag::select('tags.*')
            ->join('entry_tag', 'tags.id', '=', 'entry_tag.tag_id')
            ->join('entries', 'entry_tag.entry_id', '=', 'entries.id')
            ->where('entries.user_id', $userId)
            ->groupBy('tags.id')
            ->orderByRaw('COUNT(*) DESC')
            ->first();

        $lastEntry = Entry::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->first();

        $entriesThisMonth = Entry::where('user_id', $userId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        return [
            'total_entries' => $totalEntries,
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
            'most_used_tag' => $mostUsedTag ? $mostUsedTag->name : 'None',
            'last_entry_date' => $lastEntry ? $lastEntry->created_at->diffForHumans() : 'Never',
            'entries_this_month' => $entriesThisMonth,
        ];
    }
}
