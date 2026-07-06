<?php

namespace App\Services;

use App\Models\Entry;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Computes every number and chart series the Insights page needs.
 *
 * Design goals:
 *  - Cheap: date-only work never decrypts entry content. Only word-count
 *    metrics load `content`, and the whole payload is cached per
 *    user + period, keyed by a signature that changes when entries do.
 *  - Portable: all bucketing happens in PHP (no SQLite/Postgres-specific
 *    date functions), so it behaves the same on local SQLite and prod Postgres.
 */
class InsightsService
{
    public const PERIODS = ['week', 'month', 'year', 'all'];

    /**
     * Full insights payload for a user + period. Cached until the user's
     * entries change (signature = count + last-updated timestamp).
     */
    public function forPeriod(int $userId, string $period): array
    {
        $period = in_array($period, self::PERIODS, true) ? $period : 'week';

        $signature = $this->signature($userId);
        $key = "insights:{$userId}:{$period}:{$signature}";

        return Cache::remember($key, now()->addHour(), fn () => $this->compute($userId, $period));
    }

    /**
     * Cheap fingerprint of the user's entries so the cache self-busts on any
     * create/update/delete without needing model events.
     */
    private function signature(int $userId): string
    {
        $row = Entry::where('user_id', $userId)
            ->selectRaw('COUNT(*) as c, MAX(updated_at) as m')
            ->first();

        return ($row->c ?? 0).'-'.($row->m ?? '0');
    }

    private function compute(int $userId, string $period): array
    {
        // All-time timestamps — one cheap column, no decryption. Powers streaks,
        // the calendar heatmap and all-time aggregates.
        $allDates = Entry::where('user_id', $userId)
            ->orderBy('created_at')
            ->pluck('created_at');

        $totalEntries = $allDates->count();

        [$start, $end] = $this->periodWindow($period);
        [$prevStart, $prevEnd] = $this->previousWindow($period);

        // Period-scoped timestamps for day-of-week / time-of-day / counts.
        $periodDates = $allDates->filter(
            fn ($d) => $d->betweenIncluded($start, $end)
        )->values();

        // Word metrics need decrypted content — scoped to the period, loaded once.
        $wordStats = $this->wordStats($userId, $start, $end, $period);
        $prevWords = $this->periodWordCount($userId, $prevStart, $prevEnd);

        return [
            'period' => $period,
            'periodLabel' => $this->periodLabel($period),
            'generatedAt' => now()->toIso8601String(),

            'summary' => $this->summary($allDates, $periodDates, $wordStats, $prevWords),
            'entriesSeries' => $this->entriesOverTime($periodDates, $period),
            'wordSeries' => $wordStats['series'],
            'dayOfWeek' => $this->dayOfWeekDistribution($periodDates),
            'timeOfDay' => $this->timeOfDayDistribution($periodDates),
            'heatmap' => $this->heatmap($allDates),
            'tags' => $this->tagDistribution($userId, $start, $end),
            'streak' => $this->streakSeries($allDates, $period),
            'achievements' => $this->achievements($allDates),
        ];
    }

    /* ---------------------------------------------------------------------
     | Summary stat cards
     * ------------------------------------------------------------------- */

    private function summary(Collection $allDates, Collection $periodDates, array $wordStats, int $prevWords): array
    {
        $distinctDays = $this->distinctDays($allDates);
        [$current, $longest] = $this->streaks($distinctDays);

        $first = $allDates->first();
        $last = $allDates->last();

        // Most-active weekday over the selected period.
        $dow = $this->dayOfWeekDistribution($periodDates);
        $peakDay = null;
        $peakDayCount = 0;
        foreach ($dow['data'] as $i => $count) {
            if ($count > $peakDayCount) {
                $peakDayCount = $count;
                $peakDay = $dow['labels'][$i];
            }
        }

        // Peak hour of day over the selected period.
        $peakHour = $this->peakHour($periodDates);

        return [
            'totalEntries' => $allDates->count(),
            'periodEntries' => $periodDates->count(),
            'totalWords' => $wordStats['total'],
            'wordsChange' => $wordStats['total'] - $prevWords,
            'avgWords' => $wordStats['avg'],
            'longestEntryWords' => $wordStats['longest'],
            'currentStreak' => $current,
            'longestStreak' => $longest,
            'activeDays' => $distinctDays->count(),
            'peakDay' => $peakDay,
            'peakDayCount' => $peakDayCount,
            'peakHourLabel' => $peakHour,
            'firstEntry' => $first?->toDateString(),
            'firstEntryHuman' => $first?->diffForHumans(),
            'lastEntryHuman' => $last?->diffForHumans(),
            'streakMessage' => $this->streakMessage($current),
        ];
    }

    private function streakMessage(int $current): string
    {
        return match (true) {
            $current === 0 => 'Start your streak today!',
            $current === 1 => 'First day of your streak — keep going!',
            default => "🔥 {$current} days strong — keep it up!",
        };
    }

    /* ---------------------------------------------------------------------
     | Entries over time (adapts to the selected period)
     * ------------------------------------------------------------------- */

    private function entriesOverTime(Collection $dates, string $period): array
    {
        [$labels, $keyFor] = $this->buckets($period);
        $counts = array_fill_keys(array_keys($labels), 0);

        foreach ($dates as $d) {
            $k = $keyFor($d);
            if (array_key_exists($k, $counts)) {
                $counts[$k]++;
            }
        }

        return [
            'labels' => array_values($labels),
            'data' => array_values($counts),
        ];
    }

    /**
     * Returns [labelsByKey, fn(Carbon):key] describing how to bucket a date
     * for the given period. Same buckets are reused by the word-trend series.
     */
    private function buckets(string $period): array
    {
        switch ($period) {
            case 'week':
                $labels = [];
                $start = Carbon::now()->startOfWeek();
                for ($i = 0; $i < 7; $i++) {
                    $labels[$i] = $start->copy()->addDays($i)->format('D');
                }

                return [$labels, fn (CarbonInterface $d) => $d->dayOfWeekIso - 1];

            case 'month':
                $days = Carbon::now()->daysInMonth;
                $labels = [];
                for ($i = 1; $i <= $days; $i++) {
                    $labels[$i] = (string) $i;
                }

                return [$labels, fn (CarbonInterface $d) => $d->day];

            case 'year':
                $labels = [];
                for ($m = 1; $m <= 12; $m++) {
                    $labels[$m] = Carbon::create(null, $m, 1)->format('M');
                }

                return [$labels, fn (CarbonInterface $d) => $d->month];

            case 'all':
            default:
                // Fixed 12-year window keyed by year keeps buckets stable/cacheable.
                $labels = [];
                $thisYear = (int) Carbon::now()->year;
                for ($y = $thisYear - 11; $y <= $thisYear; $y++) {
                    $labels[$y] = (string) $y;
                }

                return [$labels, fn (CarbonInterface $d) => (int) $d->year];
        }
    }

    /* ---------------------------------------------------------------------
     | Word metrics (the only part that decrypts content)
     * ------------------------------------------------------------------- */

    private function wordStats(int $userId, Carbon $start, Carbon $end, string $period): array
    {
        [$labels, $keyFor] = $this->buckets($period);
        $bucketWords = array_fill_keys(array_keys($labels), 0);

        $total = 0;
        $count = 0;
        $longest = 0;

        Entry::where('user_id', $userId)
            ->whereBetween('created_at', [$start, $end])
            ->select(['title', 'content', 'created_at'])
            ->chunk(200, function ($entries) use (&$total, &$count, &$longest, &$bucketWords, $keyFor) {
                foreach ($entries as $entry) {
                    $words = str_word_count(trim(($entry->title ?? '').' '.($entry->content ?? '')));
                    $total += $words;
                    $count++;
                    $longest = max($longest, $words);

                    $k = $keyFor($entry->created_at);
                    if (array_key_exists($k, $bucketWords)) {
                        $bucketWords[$k] += $words;
                    }
                }
            });

        return [
            'total' => $total,
            'avg' => $count > 0 ? (int) round($total / $count) : 0,
            'longest' => $longest,
            'series' => [
                'labels' => array_values($labels),
                'data' => array_values($bucketWords),
            ],
        ];
    }

    private function periodWordCount(int $userId, Carbon $start, Carbon $end): int
    {
        $total = 0;

        Entry::where('user_id', $userId)
            ->whereBetween('created_at', [$start, $end])
            ->select(['title', 'content'])
            ->chunk(200, function ($entries) use (&$total) {
                foreach ($entries as $entry) {
                    $total += str_word_count(trim(($entry->title ?? '').' '.($entry->content ?? '')));
                }
            });

        return $total;
    }

    /* ---------------------------------------------------------------------
     | Behavioural distributions
     * ------------------------------------------------------------------- */

    private function dayOfWeekDistribution(Collection $dates): array
    {
        $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $data = array_fill(0, 7, 0);

        foreach ($dates as $d) {
            $data[$d->dayOfWeekIso - 1]++;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function timeOfDayDistribution(Collection $dates): array
    {
        // Night 0-5, Morning 6-11, Afternoon 12-17, Evening 18-23.
        $labels = ['Night', 'Morning', 'Afternoon', 'Evening'];
        $data = array_fill(0, 4, 0);

        foreach ($dates as $d) {
            $data[intdiv($d->hour, 6)]++;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function peakHour(Collection $dates): ?string
    {
        if ($dates->isEmpty()) {
            return null;
        }

        $hours = array_fill(0, 24, 0);
        foreach ($dates as $d) {
            $hours[$d->hour]++;
        }

        $peak = array_keys($hours, max($hours))[0];

        return Carbon::createFromTime($peak)->format('g A');
    }

    /* ---------------------------------------------------------------------
     | Calendar heatmap (GitHub-style, last ~53 weeks)
     * ------------------------------------------------------------------- */

    private function heatmap(Collection $allDates): array
    {
        $weeks = 53;
        $end = Carbon::now()->endOfWeek(CarbonInterface::SUNDAY);
        $start = $end->copy()->subWeeks($weeks - 1)->startOfWeek(CarbonInterface::MONDAY);

        // Count entries per calendar day within the window.
        $perDay = [];
        foreach ($allDates as $d) {
            if ($d->betweenIncluded($start, $end)) {
                $key = $d->toDateString();
                $perDay[$key] = ($perDay[$key] ?? 0) + 1;
            }
        }

        $max = empty($perDay) ? 0 : max($perDay);

        $grid = [];
        $cursor = $start->copy();
        for ($w = 0; $w < $weeks; $w++) {
            $column = [];
            for ($day = 0; $day < 7; $day++) {
                $key = $cursor->toDateString();
                $count = $perDay[$key] ?? 0;
                $column[] = [
                    'date' => $key,
                    'label' => $cursor->format('M j, Y'),
                    'count' => $count,
                    'level' => $this->heatLevel($count, $max),
                    'future' => $cursor->isFuture(),
                ];
                $cursor->addDay();
            }
            $grid[] = $column;
        }

        // Month labels aligned to the first week each month appears.
        $monthLabels = [];
        $cursor = $start->copy();
        $lastMonth = null;
        for ($w = 0; $w < $weeks; $w++) {
            $m = $cursor->format('M');
            $monthLabels[$w] = ($m !== $lastMonth) ? $m : '';
            $lastMonth = $m;
            $cursor->addWeek();
        }

        return [
            'grid' => $grid,
            'monthLabels' => $monthLabels,
            'totalInRange' => array_sum($perDay),
            'max' => $max,
        ];
    }

    private function heatLevel(int $count, int $max): int
    {
        if ($count <= 0 || $max <= 0) {
            return 0;
        }

        $ratio = $count / $max;

        return match (true) {
            $ratio > 0.75 => 4,
            $ratio > 0.5 => 3,
            $ratio > 0.25 => 2,
            default => 1,
        };
    }

    /* ---------------------------------------------------------------------
     | Tags
     * ------------------------------------------------------------------- */

    private function tagDistribution(int $userId, Carbon $start, Carbon $end): array
    {
        $rows = Entry::query()
            ->where('entries.user_id', $userId)
            ->whereBetween('entries.created_at', [$start, $end])
            ->join('entry_tag', 'entries.id', '=', 'entry_tag.entry_id')
            ->join('tags', 'entry_tag.tag_id', '=', 'tags.id')
            ->selectRaw('tags.name as name, COUNT(*) as count')
            ->groupBy('tags.name')
            ->orderByDesc('count')
            ->limit(8)
            ->pluck('count', 'name')
            ->toArray();

        return [
            'labels' => array_keys($rows),
            'data' => array_values($rows),
        ];
    }

    /* ---------------------------------------------------------------------
     | Streaks
     * ------------------------------------------------------------------- */

    /**
     * Running consecutive-day streak plotted over a window that scales with the
     * selected period, so "All Time" reflects the full history instead of a
     * fixed (often empty) last-30-days window.
     *
     *  - week/month  → daily points
     *  - longer spans → weekly, then monthly, buckets holding the peak streak,
     *    so we never plot thousands of points.
     */
    private function streakSeries(Collection $allDates, string $period): array
    {
        if ($allDates->isEmpty()) {
            return ['labels' => [], 'data' => []];
        }

        $today = Carbon::now()->startOfDay();
        $first = $allDates->first()->copy()->startOfDay();
        $present = $this->distinctDays($allDates)
            ->mapWithKeys(fn ($d) => [$d->toDateString() => true]);

        // Running streak for every day from the first entry to today.
        $daily = [];
        $run = 0;
        $cursor = $first->copy();
        while ($cursor->lte($today)) {
            $run = isset($present[$cursor->toDateString()]) ? $run + 1 : 0;
            $daily[$cursor->toDateString()] = $run;
            $cursor->addDay();
        }

        $windowStart = match ($period) {
            'week' => $today->copy()->subDays(6),
            'month' => $today->copy()->subDays(29),
            'year' => $today->copy()->subDays(364),
            default => $first->copy(), // all
        };
        if ($windowStart->lt($first)) {
            $windowStart = $first->copy();
        }

        $spanDays = $windowStart->diffInDays($today) + 1;

        // Small span → one point per day.
        if ($spanDays <= 45) {
            $labels = [];
            $data = [];
            $c = $windowStart->copy();
            while ($c->lte($today)) {
                $labels[] = $c->format('M j');
                $data[] = $daily[$c->toDateString()] ?? 0;
                $c->addDay();
            }

            return ['labels' => $labels, 'data' => $data];
        }

        // Large span → bucket by week (or month past ~13 months) holding the
        // best streak reached within each bucket.
        $groupByMonth = $spanDays > 400;
        $buckets = [];
        $c = $windowStart->copy();
        while ($c->lte($today)) {
            $label = $groupByMonth
                ? $c->format('M Y')
                : $c->copy()->startOfWeek()->format('M j');
            $val = $daily[$c->toDateString()] ?? 0;
            $buckets[$label] = max($buckets[$label] ?? 0, $val);
            $c->addDay();
        }

        return ['labels' => array_keys($buckets), 'data' => array_values($buckets)];
    }

    /**
     * @return array{0:int,1:int} [current, longest]
     */
    private function streaks(Collection $distinctDays): array
    {
        if ($distinctDays->isEmpty()) {
            return [0, 0];
        }

        $set = $distinctDays->mapWithKeys(fn ($d) => [$d->toDateString() => true]);

        // Longest run of consecutive days.
        $longest = 0;
        $run = 0;
        $prev = null;
        foreach ($distinctDays as $d) {
            if ($prev !== null && $d->equalTo($prev->copy()->addDay())) {
                $run++;
            } else {
                $run = 1;
            }
            $longest = max($longest, $run);
            $prev = $d;
        }

        // Current run ending today (or yesterday, so it survives a not-yet-written day).
        $current = 0;
        $cursor = Carbon::now()->startOfDay();
        if (! isset($set[$cursor->toDateString()])) {
            $cursor->subDay();
        }
        while (isset($set[$cursor->toDateString()])) {
            $current++;
            $cursor->subDay();
        }

        return [$current, $longest];
    }

    /* ---------------------------------------------------------------------
     | Achievements / milestones
     * ------------------------------------------------------------------- */

    private function achievements(Collection $allDates): array
    {
        $total = $allDates->count();
        [$current, $longest] = $this->streaks($this->distinctDays($allDates));

        $defs = [
            ['icon' => '🌱', 'title' => 'First Steps', 'threshold' => 1, 'value' => $total, 'unit' => 'entries',
                'doneMsg' => 'You planted the seed. Every great journal starts with a single entry. 🌟',
                'todoMsg' => 'Write your very first entry to sprout this badge.'],
            ['icon' => '📖', 'title' => 'Getting Going', 'threshold' => 10, 'value' => $total, 'unit' => 'entries',
                'doneMsg' => 'Ten entries deep — journaling is becoming a habit!',
                'todoMsg' => 'Reach 10 entries to unlock. You’ve got this!'],
            ['icon' => '✍️', 'title' => 'Wordsmith', 'threshold' => 50, 'value' => $total, 'unit' => 'entries',
                'doneMsg' => '50 entries! Your story is really taking shape. ✨',
                'todoMsg' => 'Reach 50 entries to earn your Wordsmith stripes.'],
            ['icon' => '📚', 'title' => 'Centurion', 'threshold' => 100, 'value' => $total, 'unit' => 'entries',
                'doneMsg' => '100 entries strong — a true chronicle of your life. 🏛️',
                'todoMsg' => 'Reach 100 entries to join the Centurions.'],
            ['icon' => '🔥', 'title' => 'Week Warrior', 'threshold' => 7, 'value' => $longest, 'unit' => 'day streak',
                'doneMsg' => 'Seven days in a row — unstoppable momentum! 🔥',
                'todoMsg' => 'Write 7 days in a row to feel the fire.'],
            ['icon' => '🏆', 'title' => 'Monthly Master', 'threshold' => 30, 'value' => $longest, 'unit' => 'day streak',
                'doneMsg' => 'A 30-day streak! Journaling is part of who you are now. 🏆',
                'todoMsg' => 'Keep a 30-day streak to claim the trophy.'],
        ];

        return array_map(function ($d) {
            $done = $d['value'] >= $d['threshold'];
            $capped = min($d['value'], $d['threshold']);

            return [
                'icon' => $d['icon'],
                'title' => $d['title'],
                'done' => $done,
                'progressLabel' => $capped.'/'.$d['threshold'].' '.$d['unit'],
                'progressPct' => $d['threshold'] > 0 ? min(100, (int) round($d['value'] / $d['threshold'] * 100)) : 0,
                'tooltip' => $done
                    ? $d['doneMsg']
                    : $d['todoMsg']." ({$capped}/{$d['threshold']})",
            ];
        }, $defs);
    }

    /* ---------------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------------- */

    private function distinctDays(Collection $dates): Collection
    {
        return $dates
            ->map(fn ($d) => $d->copy()->startOfDay())
            ->unique(fn ($d) => $d->toDateString())
            ->sort()
            ->values();
    }

    private function periodWindow(string $period): array
    {
        return match ($period) {
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            'all' => [Carbon::createFromTimestamp(0), Carbon::now()->endOfDay()],
        };
    }

    private function previousWindow(string $period): array
    {
        return match ($period) {
            'week' => [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()],
            'month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
            'year' => [Carbon::now()->subYear()->startOfYear(), Carbon::now()->subYear()->endOfYear()],
            'all' => [Carbon::createFromTimestamp(0), Carbon::createFromTimestamp(0)],
        };
    }

    private function periodLabel(string $period): string
    {
        return match ($period) {
            'week' => 'this week',
            'month' => 'this month',
            'year' => 'this year',
            'all' => 'all time',
            default => 'period',
        };
    }
}
