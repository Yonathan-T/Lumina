<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Entry;
use Carbon\Carbon;
use App\Services\StreakService;

class Insights extends Component
{
    public $weeklyData = [];
    public $weeklyLabels = [];
    public $tagData = [];
    public $streakData = [];
    public $totalWords = 0;
    public $avgLength = 0;
    public $mostReflectiveDay = null;
    public $longestStreak = 0;
    public $currentStreak = 0;
    public $selectedPeriod = 'week';
    public $streakLabels = [];
    public $streakMessage = '';
    public $totalWordsChange = 0;
    public $mostReflectiveDayEntries = 0;

    public function mount()
    {
        $this->loadInsightsData();
    }

    public function updatedSelectedPeriod()
    {
        $this->loadInsightsData();
        \Log::info('Insights data updated', [
            'period' => $this->selectedPeriod,
            'weeklyData' => $this->weeklyData,
            'weeklyLabels' => $this->weeklyLabels,
            'dataCount' => count($this->weeklyData),
            'labelsCount' => count($this->weeklyLabels)
        ]);
        $this->dispatch('chart-data-updated', [
            'weeklyChart' => [
                'labels' => $this->weeklyLabels,
                'data' => $this->weeklyData,
            ],
            'tagChart' => [
                'labels' => array_keys($this->tagData), // Assuming tagData is associative array
                'data' => array_values($this->tagData),
            ],
            'streakChart' => [
                'labels' => $this->streakLabels,
                'data' => $this->streakData,
            ],
        ]);
    }
    private function loadInsightsData()
    {
        $this->loadWeeklyData();
        $this->loadTagData();
        $this->loadStreakData();
        $this->loadSummaryStats();
    }

    private function loadWeeklyData()
    {
        switch ($this->selectedPeriod) {
            case 'week':
                $this->loadWeekData();
                break;
            case 'month':
                $this->loadMonthData();
                break;
            case 'year':
                $this->loadYearData();
                break;
            case 'all':
                $this->loadAllTimeData();
                break;
        }
    }

    private function loadWeekData()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $entries = Entry::where('user_id', auth()->id())
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->get();

        $this->weeklyData = array_fill(0, 7, 0);
        $this->weeklyLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        foreach ($entries as $entry) {
            $dayIndex = Carbon::parse($entry->created_at)->dayOfWeekIso - 1;
            $this->weeklyData[$dayIndex]++;
        }
    }

    private function loadMonthData()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $entries = Entry::where('user_id', auth()->id())
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get();

        $daysInMonth = Carbon::now()->daysInMonth;
        $this->weeklyData = array_fill(0, $daysInMonth, 0);
        $this->weeklyLabels = range(1, $daysInMonth);

        foreach ($entries as $entry) {
            $dayOfMonth = Carbon::parse($entry->created_at)->day - 1;
            if ($dayOfMonth >= 0 && $dayOfMonth < $daysInMonth) {
                $this->weeklyData[$dayOfMonth]++;
            }
        }
    }

    private function loadYearData()
    {
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = Carbon::now()->endOfYear();
        $entries = Entry::where('user_id', auth()->id())
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->get();

        $this->weeklyData = array_fill(0, 12, 0);
        $this->weeklyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        foreach ($entries as $entry) {
            $monthIndex = Carbon::parse($entry->created_at)->month - 1;
            $this->weeklyData[$monthIndex]++;
        }
    }

    private function loadAllTimeData()
    {
        $entries = Entry::where('user_id', auth()->id())->get();

        $yearlyData = [];
        foreach ($entries as $entry) {
            $year = Carbon::parse($entry->created_at)->year;
            $yearlyData[$year] = ($yearlyData[$year] ?? 0) + 1;
        }

        ksort($yearlyData);
        $this->weeklyData = array_values($yearlyData);
        $this->weeklyLabels = array_keys($yearlyData);

        if (empty($yearlyData)) {
            $this->weeklyData = [];
            $this->weeklyLabels = [];
        }
    }

    private function loadTagData()
    {
        $this->tagData = Entry::where('user_id', auth()->id())
            ->join('entry_tag', 'entries.id', '=', 'entry_tag.entry_id')
            ->join('tags', 'entry_tag.tag_id', '=', 'tags.id')
            ->selectRaw('tags.name, COUNT(*) as count')
            ->groupBy('tags.name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->pluck('count', 'name')
            ->toArray();
    }

    private function loadStreakData()
    {
        // $this->streakData = StreakService::getStreakChartData(auth()->id(), 30);
        $streakChartInfo = StreakService::getStreakChartData(auth()->id(), 30);
        $this->streakData = $streakChartInfo['data'];
        $this->streakLabels = $streakChartInfo['labels']; // <-
    }

    private function loadSummaryStats()
    {
        $currentData = $this->getCurrentPeriodData();
        $previousData = $this->getPreviousPeriodData();

        $this->totalWords = $currentData['totalWords'];
        $this->totalWordsChange = $this->calculateChange($currentData['totalWords'], $previousData['totalWords']);
        $this->avgLength = $currentData['avgLength'];

        $reflectiveData = $this->getMostReflectiveDayData();
        $this->mostReflectiveDay = $reflectiveData['day'];
        $this->mostReflectiveDayEntries = $reflectiveData['entries'];

        $this->longestStreak = StreakService::getLongestStreak(auth()->id());
        $this->currentStreak = StreakService::getCurrentStreak(auth()->id());

        $this->streakMessage = $this->currentStreak === 0
            ? "Start your streak today!"
            : ($this->currentStreak === 1
                ? "First day of your streak!"
                : "Keep it going! {$this->currentStreak} days strong!");
    }

    private function getCurrentPeriodData()
    {
        $query = Entry::where('user_id', auth()->id());

        switch ($this->selectedPeriod) {
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'all':
                $entries = Entry::where('user_id', auth()->id())->get();
                return $this->calculateEntryStats($entries);
        }

        $entries = $query->whereBetween('created_at', [$startDate, $endDate])->get();
        return $this->calculateEntryStats($entries);
    }

    private function getPreviousPeriodData()
    {
        $query = Entry::where('user_id', auth()->id());

        switch ($this->selectedPeriod) {
            case 'week':
                $startDate = Carbon::now()->subWeek()->startOfWeek();
                $endDate = Carbon::now()->subWeek()->endOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'year':
                $startDate = Carbon::now()->subYear()->startOfYear();
                $endDate = Carbon::now()->subYear()->endOfYear();
                break;
            case 'all':
                $startDate = Carbon::now()->subYear()->startOfYear();
                $endDate = Carbon::now()->subYear()->endOfYear();
                break;
        }

        $entries = $query->whereBetween('created_at', [$startDate, $endDate])->get();
        return $this->calculateEntryStats($entries);
    }

    private function calculateEntryStats($entries)
    {
        $totalWords = $entries->sum(function ($entry) {
            return str_word_count($entry->title . ' ' . $entry->content);
        });

        return [
            'totalWords' => $totalWords,
            'avgLength' => $entries->count() > 0 ? (int) ($totalWords / $entries->count()) : 0,
            'entryCount' => $entries->count()
        ];
    }

    private function getMostReflectiveDayData()
    {
        $entries = Entry::where('user_id', auth()->id())->get();
        $days = [
            'Monday' => 0,
            'Tuesday' => 0,
            'Wednesday' => 0,
            'Thursday' => 0,
            'Friday' => 0,
            'Saturday' => 0,
            'Sunday' => 0,
        ];

        foreach ($entries as $entry) {
            $dayName = Carbon::parse($entry->created_at)->format('l');
            $days[$dayName]++;
        }

        $maxEntries = max($days);
        $mostReflectiveDay = $maxEntries > 0 ? array_keys($days, $maxEntries)[0] : null;

        return [
            'day' => $mostReflectiveDay,
            'entries' => $maxEntries
        ];
    }

    private function calculateChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? $current : 0;
        }
        return $current - $previous;
    }

    public function getPeriodName()
    {
        return match ($this->selectedPeriod) {
            'week' => 'week',
            'month' => 'month',
            'year' => 'year',
            'all' => 'all time',
            default => 'period'
        };
    }

    public function debugData()
    {
        $currentData = $this->getCurrentPeriodData();
        $previousData = $this->getPreviousPeriodData();

        return [
            'current_period' => $this->selectedPeriod,
            'current_total_words' => $currentData['totalWords'],
            'previous_total_words' => $previousData['totalWords'],
            'word_change' => $this->totalWordsChange,
            'current_avg_length' => $currentData['avgLength'],
            'current_entries' => $currentData['entryCount'],
            'previous_entries' => $previousData['entryCount'],
            'current_streak' => $this->currentStreak,
            'longest_streak' => $this->longestStreak,
            'streak_message' => $this->streakMessage
        ];
    }

    public function render()
    {
        return view('livewire.insights');
    }
}