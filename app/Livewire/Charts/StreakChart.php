<?php

namespace App\Livewire\Charts;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\StreakService;

class StreakChart extends Component
{
    public $streakData = [];
    public $streakLabels = [];
    public $selectedPeriod = 'week';

    public function mount($selectedPeriod = 'week')
    {
        $this->selectedPeriod = $selectedPeriod;
        $this->loadStreakData();
    }

    #[On('period-changed')]
    public function updatePeriod($period)
    {
        $this->selectedPeriod = $period;
        $this->loadStreakData();
        $this->dispatch('chart-data-updated', [
            'chartId' => 'streakChart',
            'labels' => $this->streakLabels,
            'data' => $this->streakData,
        ]);
    }

    private function loadStreakData()
    {
        $days = $this->getDaysForPeriod();
        $streakChartInfo = StreakService::getStreakChartData(auth()->id(), $days);
        $this->streakData = $streakChartInfo['data'];
        $this->streakLabels = $streakChartInfo['labels'];
    }

    private function getDaysForPeriod()
    {
        return match ($this->selectedPeriod) {
            'week' => 7,
            'month' => 30,
            'year' => 365,
            'all' => 365, // Show last year for 'all' to keep chart readable
            default => 30
        };
    }

    public function render()
    {
        return view('livewire.charts.streak-chart');
    }
}