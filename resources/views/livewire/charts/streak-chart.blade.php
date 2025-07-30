<div class="bg-gradient-dark border border-white/5 rounded-lg p-6 card-highlight" wire:ignore>
    <h3 class="text-lg font-semibold text-white mb-2">Writing Streak</h3>
    <p class="text-sm text-gray-400 mb-4">Your daily writing streak over time</p>
    <x-chart id="streakChart" height="320px" :config="json_encode([
        'type' => 'line',
        'data' => [
            'labels' => $streakLabels,
            'datasets' => [
                [
                    'label' => 'streak',
                    'data' => $streakData,
                    'borderColor' => 'rgba(210, 214, 220, 1)',
                    'backgroundColor' => 'rgba(210, 214, 220, 0.1)',
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.1,
                    'pointBackgroundColor' => 'rgba(210, 214, 220, 1)',
                    'pointBorderColor' => 'rgba(210, 214, 220, 1)',
                    'pointRadius' => 3,
                    'pointHoverRadius' => 5
                ]
            ]
        ],
        'options' => [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'color' => '#9CA3AF',
                        'usePointStyle' => true
                    ]
                ]
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'color' => '#9CA3AF',
                        'stepSize' => 3
                    ],
                    'grid' => [
                        'color' => '#374151',
                        'drawBorder' => false
                    ]
                ],
                'x' => [
                    'ticks' => ['color' => '#9CA3AF'],
                    'grid' => ['display' => false]
                ]
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index'
            ]
        ]
    ])" />
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('chart-data-updated', (event) => {
            if (event.chartId === 'streakChart') {
                console.log('StreakChart: chart-data-updated event received', event);
                if (typeof debouncedInitializeChart_streakChart === 'function') {
                    debouncedInitializeChart_streakChart(event.labels, event.data);
                } else {
                    console.warn('debouncedInitializeChart_streakChart function not found.');
                }
            }
        });
    });
</script>