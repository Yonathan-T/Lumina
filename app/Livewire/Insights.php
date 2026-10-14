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
    }

    private function allPeriods(): array
    {
        $service = app(InsightsService::class);
        $userId = auth()->id();

        $periods = [];
        foreach (InsightsService::PERIODS as $period) {
            $data = $service->forPeriod($userId, $period);
            $periods[$period] = [
                'summary' => $data['summary'],
                'periodLabel' => $data['periodLabel'],
                'charts' => $this->buildCharts($data),
                'entriesSubtitle' => match ($period) {
                    'week' => 'How many entries you wrote each day',
                    'year' => 'How many entries you wrote each month',
                    'all' => 'How many entries you wrote each year',
                    default => 'How many entries you wrote each day',
                },
                'streakSubtitle' => match ($period) {
                    'week' => 'Your consecutive-day streak this past week',
                    'month' => 'Your consecutive-day streak over the last 30 days',
                    'year' => 'Your consecutive-day streak over the last year',
                    default => 'Your consecutive-day streak across your whole history',
                },
                'dowSubtitle' => 'Which days you write most, ' . ($data['periodLabel'] ?? ''),
                'todSubtitle' => 'When during the day you tend to journal, ' . ($data['periodLabel'] ?? ''),
                'tagSubtitle' => 'What you write about most, ' . ($data['periodLabel'] ?? ''),
            ];
        }

        return $periods;
    }

    private function insights(): array
    {
        return app(InsightsService::class)->forPeriod(auth()->id(), $this->selectedPeriod);
    }

    /**
     * Build complete Chart.js configuration objects.
     * Uses Lumina brand blue shades differentiated by intensity/luminosity/opacity
     * so that peak and active periods visually pop.
     */
    private function buildCharts(array $d): array
    {
        $blueMonochrome = ['#93C5FD', '#60A5FA', '#3B82F6', '#2563EB', '#1D4ED8', '#1E40AF', '#172554', '#0F172A'];
        $tick = '#9CA3AF';
        $grid = 'rgba(255,255,255,0.06)';

        // Dynamic intensity calculator based on peak value in series
        $getIntensityColors = function (array $data) {
            $max = !empty($data) ? max($data) : 0;
            if ($max <= 0) {
                return array_fill(0, count($data), 'rgba(59, 130, 246, 0.35)');
            }
            return array_map(function ($val) use ($max) {
                if ($val <= 0) {
                    return 'rgba(255, 255, 255, 0.05)';
                }
                $ratio = $val / $max;
                if ($ratio >= 0.85) {
                    return '#93C5FD'; // radiant peak ice blue
                } elseif ($ratio >= 0.55) {
                    return '#60A5FA'; // luminous sky blue
                } elseif ($ratio >= 0.25) {
                    return '#3B82F6'; // lumina signature blue
                } else {
                    return 'rgba(59, 130, 246, 0.45)'; // soft deep blue
                }
            }, $data);
        };

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
            // Entries over time — bar with intensity-scaled Lumina blue
            'entriesChart' => [
                'type' => 'bar',
                'data' => [
                    'labels' => $d['entriesSeries']['labels'],
                    'datasets' => [[
                        'label' => 'Entries',
                        'data' => $d['entriesSeries']['data'],
                        'backgroundColor' => $getIntensityColors($d['entriesSeries']['data']),
                        'hoverBackgroundColor' => '#93C5FD',
                        'borderRadius' => 6,
                        'maxBarThickness' => 28,
                    ]],
                ],
                'options' => [
                    'responsive' => true, 'maintainAspectRatio' => false,
                    'plugins' => $noLegend, 'scales' => $linearAxes,
                ],
            ],

            // Words written over time — smooth line with ambient glow
            'wordChart' => [
                'type' => 'line',
                'data' => [
                    'labels' => $d['wordSeries']['labels'],
                    'datasets' => [[
                        'label' => 'Words',
                        'data' => $d['wordSeries']['data'],
                        'borderColor' => '#60A5FA',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.16)',
                        'borderWidth' => 2.5, 'fill' => true, 'tension' => 0.4,
                        'pointRadius' => 2, 'pointHoverRadius' => 6,
                        'pointBackgroundColor' => '#93C5FD',
                        'pointBorderColor' => '#0F172A',
                        'pointBorderWidth' => 2,
                    ]],
                ],
                'options' => [
                    'responsive' => true, 'maintainAspectRatio' => false,
                    'plugins' => $noLegend, 'scales' => $linearAxes,
                    'interaction' => ['intersect' => false, 'mode' => 'index'],
                ],
            ],

            // Day-of-week — bar with intensity-scaled Lumina blue highlighting peak weekday
            'dowChart' => [
                'type' => 'bar',
                'data' => [
                    'labels' => $d['dayOfWeek']['labels'],
                    'datasets' => [[
                        'label' => 'Entries',
                        'data' => $d['dayOfWeek']['data'],
                        'backgroundColor' => $getIntensityColors($d['dayOfWeek']['data']),
                        'hoverBackgroundColor' => '#93C5FD',
                        'borderRadius' => 6, 'maxBarThickness' => 34,
                    ]],
                ],
                'options' => [
                    'responsive' => true, 'maintainAspectRatio' => false,
                    'plugins' => $noLegend, 'scales' => $linearAxes,
                ],
            ],

            // Time-of-day — sleek horizontal bar chart with intensity shading (replaces ugly polar area)
            'todChart' => [
                'type' => 'bar',
                'data' => [
                    'labels' => $d['timeOfDay']['labels'],
                    'datasets' => [[
                        'label' => 'Entries',
                        'data' => $d['timeOfDay']['data'],
                        'backgroundColor' => $getIntensityColors($d['timeOfDay']['data']),
                        'hoverBackgroundColor' => '#93C5FD',
                        'borderRadius' => 6,
                        'maxBarThickness' => 24,
                    ]],
                ],
                'options' => [
                    'indexAxis' => 'y',
                    'responsive' => true, 'maintainAspectRatio' => false,
                    'plugins' => $noLegend,
                    'scales' => [
                        'x' => [
                            'beginAtZero' => true,
                            'ticks' => ['color' => $tick, 'precision' => 0],
                            'grid' => ['color' => $grid, 'drawBorder' => false],
                        ],
                        'y' => [
                            'ticks' => ['color' => '#E2E8F0', 'font' => ['size' => 12, 'weight' => 500]],
                            'grid' => ['display' => false],
                        ],
                    ],
                ],
            ],

            // Tag distribution — fancy segmented doughnut with Lumina blue monochromatic gradient
            'tagChart' => [
                'type' => 'doughnut',
                'data' => [
                    'labels' => $d['tags']['labels'],
                    'datasets' => [[
                        'data' => $d['tags']['data'],
                        'backgroundColor' => $blueMonochrome,
                        'borderWidth' => 0,
                        'spacing' => 4,
                        'borderRadius' => 5,
                        'hoverOffset' => 8,
                    ]],
                ],
                'options' => [
                    'responsive' => true,
                    'maintainAspectRatio' => false,
                    'cutout' => '72%',
                    'plugins' => [
                        'legend' => [
                            'position' => 'right',
                            'labels' => [
                                'color' => $tick,
                                'usePointStyle' => true,
                                'pointStyle' => 'circle',
                                'padding' => 12,
                                'font' => ['size' => 12],
                            ],
                        ],
                    ],
                ],
            ],

            // Streak over the selected period — smooth line with cyan-glow accent
            'streakChart' => [
                'type' => 'line',
                'data' => [
                    'labels' => $d['streak']['labels'],
                    'datasets' => [[
                        'label' => 'Streak',
                        'data' => $d['streak']['data'],
                        'borderColor' => '#38BDF8',
                        'backgroundColor' => 'rgba(56, 189, 248, 0.12)',
                        'borderWidth' => 2.5, 'fill' => true, 'tension' => 0.35,
                        'pointRadius' => 0, 'pointHoverRadius' => 5,
                        'pointBackgroundColor' => '#38BDF8',
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
        $allPeriods = $this->allPeriods();
        $selectedPeriod = in_array($this->selectedPeriod, InsightsService::PERIODS, true) ? $this->selectedPeriod : 'week';
        $current = $allPeriods[$selectedPeriod] ?? $allPeriods['week'];
        $data = $this->insights();

        return view('livewire.insights', [
            'data' => $data,
            'allPeriods' => $allPeriods,
            'charts' => $current['charts'] ?? $this->buildCharts($data),
            'selectedPeriod' => $selectedPeriod,
        ]);
    }
}
