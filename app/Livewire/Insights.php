<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Entry;
use Carbon\Carbon;
use App\Services\StreakService;

class Insights extends Component
{
    public $totalWords = 0;
    public $avgLength = 0;
    public $mostReflectiveDay = null;
    public $longestStreak = 0;
    public $currentStreak = 0;
    public $selectedPeriod = 'week';
    public $streakMessage = '';
    public $totalWordsChange = 0;
    public $mostReflectiveDayEntries = 0;

    public function mount()
    {
        $this->loadSummaryStats();
    }

    public function updatedSelectedPeriod()
    {
        $this->loadSummaryStats();
        // Dispatch event to child components to update their data
        $this->dispatch('period-changed', period: $this->selectedPeriod);
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