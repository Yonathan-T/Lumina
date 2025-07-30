<?php

namespace App\Livewire\Charts;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Entry;
use Carbon\Carbon;

class WeeklyChart extends Component
{
    public $weeklyData = [];
    public $weeklyLabels = [];
    public $selectedPeriod = 'week';
    public $chartTitle = 'Entries per Week';

    public function mount($selectedPeriod = 'week')
    {
        $this->selectedPeriod = $selectedPeriod;
        $this->loadWeeklyData();
        $this->updateChartTitle();
    }

    #[On('period-changed')]
    public function updatePeriod($period)
    {
        $this->selectedPeriod = $period;
        $this->loadWeeklyData();
        $this->updateChartTitle();
        $this->dispatch('chart-data-updated', [
            'chartId' => 'weeklyChart',
            'labels' => $this->weeklyLabels,
            'data' => $this->weeklyData,
        ]);
    }

    private function updateChartTitle()
    {
        $this->chartTitle = match ($this->selectedPeriod) {
            'week' => 'Entries per Day',
            'month' => 'Entries per Day',
            'year' => 'Entries per Month',
            'all' => 'Entries per Year',
            default => 'Entries'
        };
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

    public function render()
    {
        return view('livewire.charts.weekly-chart');
    }
}