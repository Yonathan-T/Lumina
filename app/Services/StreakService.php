<?php

namespace App\Services;

use App\Models\Entry;
use Carbon\Carbon;

class StreakService
{
    public static function getCurrentStreak($userId)
    {
        $today = Carbon::now()->startOfDay();
        $yesterday = Carbon::yesterday()->startOfDay();
        $streak = 0;

        $entryDates = Entry::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->pluck('created_at')
            ->map(fn ($d) => $d->copy()->startOfDay())
            ->unique(fn ($d) => $d->toDateString())
            ->values();

        $hasEntryToday = $entryDates->contains(fn ($d) => $d->eq($today));
        $hasEntryYesterday = $entryDates->contains(fn ($d) => $d->eq($yesterday));

        // If neither today nor yesterday has an entry, streak is broken
        if (!$hasEntryToday && !$hasEntryYesterday) {
            return 0;
        }

        // Start checking from today if written today, otherwise from yesterday
        $currentDateChecker = $hasEntryToday ? $today->copy() : $yesterday->copy();
        foreach ($entryDates as $entryDate) {
            if ($entryDate->eq($currentDateChecker)) {
                $streak++;
                $currentDateChecker->subDay();
            } elseif ($entryDate->lt($currentDateChecker)) {
                break;
            }
        }

        return $streak;
    }

    public static function getStreakInJeopardy($userId): int
    {
        $today = Carbon::now()->startOfDay();
        $yesterday = Carbon::yesterday()->startOfDay();

        $entryDates = Entry::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->pluck('created_at')
            ->map(fn ($d) => $d->copy()->startOfDay())
            ->unique(fn ($d) => $d->toDateString())
            ->values();

        // If they already wrote today, their streak is safe today!
        $hasEntryToday = $entryDates->contains(fn ($d) => $d->eq($today));
        if ($hasEntryToday) {
            return 0;
        }

        // Must have written yesterday to have an active streak ending yesterday
        $hasEntryYesterday = $entryDates->contains(fn ($d) => $d->eq($yesterday));
        if (!$hasEntryYesterday) {
            return 0;
        }

        $streak = 0;
        $checker = $yesterday->copy();
        foreach ($entryDates as $entryDate) {
            if ($entryDate->eq($checker)) {
                $streak++;
                $checker->subDay();
            } elseif ($entryDate->lt($checker)) {
                break;
            }
        }

        return $streak;
    }

    public static function getLongestStreak($userId)
    {
        $entryDates = Entry::where('user_id', $userId)
            ->selectRaw('DATE(created_at) as entry_date')
            ->distinct()
            ->orderBy('entry_date', 'asc')
            ->get()
            ->pluck('entry_date')
            ->map(function ($date) {
                return Carbon::parse($date)->startOfDay();
            });

        \Log::info('Longest Streak - Distinct Entry Dates', [
            'userId' => $userId,
            'distinct_dates' => $entryDates->map(fn($date) => $date->toDateString())->toArray()
        ]);

        if ($entryDates->isEmpty()) {
            \Log::info('No entries for longest streak', ['userId' => $userId]);
            return 0;
        }

        $longestStreak = 0;
        $currentStreak = 0;
        $lastDate = null;

        foreach ($entryDates as $entryDate) {
            if ($lastDate === null) {
                $currentStreak = 1;
            } elseif ($entryDate->eq($lastDate->copy()->addDay())) {
                $currentStreak++;
            } else {
                $currentStreak = 1;
            }

            $longestStreak = max($longestStreak, $currentStreak);
            $lastDate = $entryDate;
        }

        \Log::info('Longest streak calculated', ['userId' => $userId, 'longestStreak' => $longestStreak]);
        return $longestStreak;
    }

    public static function getStreakChartData($userId, $days = 30)
    {
        $today = Carbon::now()->startOfDay();

        $entryDateStrings = Entry::where('user_id', $userId)
            ->whereBetween('created_at', [
                $today->copy()->subDays($days - 1)->startOfDay(),
                $today->endOfDay()
            ])
            ->selectRaw('DATE(created_at) as entry_date')
            ->distinct()
            ->get()
            ->pluck('entry_date')
            ->toArray();

        $entryMap = array_flip($entryDateStrings);

        $dailyStreaks = [];
        $dailyLabels = [];
        $currentRunningStreak = 0;

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $dateString = $date->toDateString();

            if (isset($entryMap[$dateString])) {
                $currentRunningStreak++;
            } else {
                $currentRunningStreak = 0;
            }
            $dailyStreaks[] = $currentRunningStreak;
            $dailyLabels[] = $date->format('M d');
        }

        \Log::info('Streak chart data generated', ['userId' => $userId, 'chart' => $dailyStreaks, 'labels' => $dailyLabels]);
        return [
            'labels' => $dailyLabels,
            'data' => $dailyStreaks
        ];
    }
    public static function getLastEntryDate($userId)
    {
        $lastEntry = Entry::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastEntry) {
            return Carbon::parse($lastEntry->created_at);
        }

        return null;
    }

}