<?php

namespace App\Services\Ai\Tools;

use App\Models\Entry;
use App\Models\Tag;
use App\Models\User;
use App\Services\StreakService;
use Illuminate\Support\Facades\Log;

class GetUserStatsTool implements ToolInterface
{
    public function name(): string
    {
        return 'get_user_stats';
    }

    public function definition(): array
    {
        return [
            'name' => 'get_user_stats',
            'description' => 'Retrieve the user\'s journaling statistics including current writing streak, longest streak, total entry count, writing milestones (first/earliest entry title and date, longest entry title and word count), average words per entry, and most frequent topics/tags.',
            'parameters' => [
                'type' => 'OBJECT',
                'properties' => [
                    'focus' => [
                        'type' => 'STRING',
                        'description' => 'Optional area of focus: "streak", "totals", "milestones", or "all". Default is "all".',
                    ],
                ],
            ],
        ];
    }

    public function execute(array $args, User $user): array
    {
        try {
            $userId = $user->id;

            $currentStreak = StreakService::getCurrentStreak($userId);
            $longestStreak = StreakService::getLongestStreak($userId);
            $totalEntries = Entry::where('user_id', $userId)->count();

            $userTags = Tag::whereHas('entries', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
                ->withCount(['entries' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }])
                ->orderByDesc('entries_count')
                ->get();

            $totalTagsCount = $userTags->count();
            $topTags = $userTags->take(3)->pluck('name')->all();

            $entriesThisMonth = Entry::where('user_id', $userId)
                ->where('created_at', '>=', now()->startOfMonth())
                ->count();

            $lastEntry = Entry::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->first();

            $firstEntry = Entry::where('user_id', $userId)
                ->orderBy('created_at', 'asc')
                ->first();

            $allEntries = Entry::where('user_id', $userId)->get();
            $maxWordCount = 0;
            $longestEntryTitle = 'None';
            $totalWords = 0;

            foreach ($allEntries as $e) {
                $wCount = str_word_count(strip_tags((string) $e->content));
                $totalWords += $wCount;
                if ($wCount > $maxWordCount) {
                    $maxWordCount = $wCount;
                    $longestEntryTitle = (string) $e->title;
                }
            }

            $avgWords = $totalEntries > 0 ? (int) round($totalWords / $totalEntries) : 0;

            return [
                'current_streak_days' => $currentStreak,
                'longest_streak_days' => $longestStreak,
                'total_journal_entries' => $totalEntries,
                'total_unique_tags' => $totalTagsCount,
                'top_tags' => ! empty($topTags) ? implode(', ', $topTags) : 'None',
                'top_tag' => $userTags->first() ? $userTags->first()->name : 'None',
                'entries_written_this_month' => $entriesThisMonth,
                'last_active' => $lastEntry && $lastEntry->created_at ? $lastEntry->created_at->diffForHumans() : 'Never',
                'first_entry_title' => $firstEntry ? (string) $firstEntry->title : 'None',
                'first_entry_date' => $firstEntry && $firstEntry->created_at ? $firstEntry->created_at->format('M j, Y') : 'Never',
                'longest_entry_title' => $longestEntryTitle,
                'longest_entry_word_count' => $maxWordCount,
                'average_words_per_entry' => $avgWords,
            ];
        } catch (\Throwable $e) {
            Log::error('GetUserStatsTool execution failed: ' . $e->getMessage());

            return [
                'status' => 'error',
                'message' => 'Unable to calculate user statistics at this time.',
            ];
        }
    }
}
