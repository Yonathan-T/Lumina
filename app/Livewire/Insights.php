<?php

namespace App\Livewire;

use App\Services\InsightsService;
use Livewire\Attributes\Url;
use Livewire\Component;

class Insights extends Component
{
    /** Kept in the URL so a chosen period survives refresh/share. */
    #[Url(as: 'range')]
    public string $selectedPeriod = 'week';

    public function setPeriod(string $period): void
    {
        $this->selectedPeriod = in_array($period, InsightsService::PERIODS, true) ? $period : 'week';

        // Charts live in wire:ignore containers owned by JS; hand them the
        // freshly-built configs so they re-render without a full DOM diff.
        $this->dispatch('insights-refreshed', charts: $this->buildCharts($this->insights()));
    }

    private function insights(): array
    {
        return app(InsightsService::class)->forPeriod(auth()->id(), $this->selectedPeriod);
    }

    /**
     * Build complete Chart.js configuration objects. Everything the front-end
     * needs is here — no client-side merging of partial series.
     */
    private function buildCharts(array $d): array
    {
        $palette = ['#8B5CF6', '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#EC4899', '#14B8A6', '#F97316'];
        $tick = '#9CA3AF';
        $grid = 'rgba(255,255,255,0.06)';

        $linearAxes = [
            'y' => [
                'beginAtZero' => true,
                'ticks' => ['color' => $tick, 'precision' => 0],
                'grid' => ['color' => $grid, 'drawBorder' => false],
            ],
            'x' => [
                'ticks' => ['color' => $tick, 'maxRotation' => 0, 'autoSkip' => true],
                'grid' => ['display' => false],
            ],
        ];

        $noLegend = ['legend' => ['display' => false]];

        return [
            // Entries over time — bar
            'entriesChart' => [
                'type' => 'bar',
                'data' => [
                    'labels' => $d['entriesSeries']['labels'],
                    'datasets' => [[
                        'label' => 'Entries',
                        'data' => $d['entriesSeries']['data'],
                        'backgroundColor' => '#8B5CF6',
                        'borderRadius' => 6,
                        'maxBarThickness' => 28,
                    ]],
                ],
                'options' => [
                    'responsive' => true, 'maintainAspectRatio' => false,
                    'plugins' => $noLegend, 'scales' => $linearAxes,
                ],
            ],

            // Words written over time — line
            'wordChart' => [
                'type' => 'line',
                'data' => [
                    'labels' => $d['wordSeries']['labels'],
                    'datasets' => [[
                        'label' => 'Words',
                        'data' => $d['wordSeries']['data'],
                        'borderColor' => '#3B82F6',
                        'backgroundColor' => 'rgba(59,130,246,0.15)',
                        'borderWidth' => 2, 'fill' => true, 'tension' => 0.35,
                        'pointRadius' => 2, 'pointHoverRadius' => 5,
                    ]],
                ],
                'options' => [
                    'responsive' => true, 'maintainAspectRatio' => false,
                    'plugins' => $noLegend, 'scales' => $linearAxes,
                    'interaction' => ['intersect' => false, 'mode' => 'index'],
                ],
            ],

            // Day-of-week — bar
            'dowChart' => [
                'type' => 'bar',
                'data' => [
                    'labels' => $d['dayOfWeek']['labels'],
                    'datasets' => [[
                        'label' => 'Entries',
                        'data' => $d['dayOfWeek']['data'],
                        'backgroundColor' => '#10B981',
                        'borderRadius' => 6, 'maxBarThickness' => 34,
                    ]],
                ],
                'options' => [
                    'responsive' => true, 'maintainAspectRatio' => false,
                    'plugins' => $noLegend, 'scales' => $linearAxes,
                ],
            ],

            // Time-of-day — polar area
            'todChart' => [
                'type' => 'polarArea',
                'data' => [
                    'labels' => $d['timeOfDay']['labels'],
                    'datasets' => [[
                        'data' => $d['timeOfDay']['data'],
                        'backgroundColor' => ['#6366F1', '#F59E0B', '#3B82F6', '#8B5CF6'],
                        'borderWidth' => 0,
                    ]],
                ],
                'options' => [
                    'responsive' => true, 'maintainAspectRatio' => false,
                    'plugins' => ['legend' => ['position' => 'right', 'labels' => ['color' => $tick, 'usePointStyle' => true, 'padding' => 10]]],
                    'scales' => ['r' => ['ticks' => ['display' => false, 'backdropColor' => 'transparent'], 'grid' => ['color' => $grid], 'angleLines' => ['color' => $grid]]],
                ],
            ],

            // Tag distribution — doughnut
            'tagChart' => [
                'type' => 'doughnut',
                'data' => [
                    'labels' => $d['tags']['labels'],
                    'datasets' => [[
                        'data' => $d['tags']['data'],
                        'backgroundColor' => $palette,
                        'borderWidth' => 0,
                    ]],
                ],
                'options' => [
                    'responsive' => true, 'maintainAspectRatio' => false, 'cutout' => '62%',
                    'plugins' => ['legend' => ['position' => 'right', 'labels' => ['color' => $tick, 'usePointStyle' => true, 'padding' => 10]]],
                ],
            ],

            // Streak over the selected period — line
            'streakChart' => [
                'type' => 'line',
                'data' => [
                    'labels' => $d['streak']['labels'],
                    'datasets' => [[
                        'label' => 'Streak',
                        'data' => $d['streak']['data'],
                        'borderColor' => '#F97316',
                        'backgroundColor' => 'rgba(249,115,22,0.12)',
                        'borderWidth' => 2, 'fill' => true, 'tension' => 0.3,
                        'pointRadius' => 0, 'pointHoverRadius' => 5,
                    ]],
                ],
                'options' => [
                    'responsive' => true, 'maintainAspectRatio' => false,
                    'plugins' => $noLegend, 'scales' => $linearAxes,
                    'interaction' => ['intersect' => false, 'mode' => 'index'],
                ],
            ],
        ];
    }

    public function render()
    {
        $data = $this->insights();

        return view('livewire.insights', [
            'data' => $data,
            'charts' => $this->buildCharts($data),
        ]);
    }
}
