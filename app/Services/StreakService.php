<?php

namespace App\Services;

use App\Models\Entry;
use Carbon\Carbon;

class StreakService
{
    public static function getCurrentStreak($userId)
    {
        $today = Carbon::now()->startOfDay();
        $streak = 0;

        $entryDates = Entry::where('user_id', $userId)
            ->selectRaw('DATE(created_at) as entry_date')
            ->distinct()
            ->orderBy('entry_date', 'desc')
            ->get()
            ->pluck('entry_date')
            ->map(function ($date) {
                return Carbon::parse($date)->startOfDay();
            });


        $hasEntryToday = false;
        foreach ($entryDates as $date) {
            if ($date->eq($today)) {
                $hasEntryToday = true;
                break;
            }
        }

        if (!$hasEntryToday) {
            \Log::info('No entry today, current streak is 0', ['userId' => $userId, 'today' => $today->toDateString()]);
            return 0;
        }


        $currentDateChecker = $today->copy();
        foreach ($entryDates as $entryDate) {
            if ($entryDate->eq($currentDateChecker)) {
                $streak++;
                $currentDateChecker->subDay();
            } elseif ($entryDate->lt($currentDateChecker)) {
                break;
            }

        }

        \Log::info('Current streak calculated', ['userId' => $userId, 'streak' => $streak]);
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