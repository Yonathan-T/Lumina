<?php

namespace App\Livewire\Charts;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Entry;

class TagChart extends Component
{
    public $tagData = [];
    public $selectedPeriod = 'week';

    public function mount($selectedPeriod = 'week')
    {
        $this->selectedPeriod = $selectedPeriod;
        $this->loadTagData();
    }

    #[On('period-changed')]
    public function updatePeriod($period)
    {
        $this->selectedPeriod = $period;
        $this->loadTagData();
        $this->dispatch('chart-data-updated', [
            'chartId' => 'tagChart',
            'labels' => array_keys($this->tagData),
            'data' => array_values($this->tagData),
        ]);
    }

    private function loadTagData()
    {
        $query = Entry::where('user_id', auth()->id())
            ->join('entry_tag', 'entries.id', '=', 'entry_tag.entry_id')
            ->join('tags', 'entry_tag.tag_id', '=', 'tags.id');

        // Apply period filter if not 'all'
        if ($this->selectedPeriod !== 'all') {
            $dateRange = $this->getDateRangeForPeriod();
            if ($dateRange) {
                $query->whereBetween('entries.created_at', $dateRange);
            }
        }

        $this->tagData = $query
            ->selectRaw('tags.name, COUNT(*) as count')
            ->groupBy('tags.name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->pluck('count', 'name')
            ->toArray();
    }

    private function getDateRangeForPeriod()
    {
        return match ($this->selectedPeriod) {
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => null
        };
    }

    public function render()
    {
        return view('livewire.charts.tag-chart');
    }
}