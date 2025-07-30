<div class="bg-gradient-dark border border-white/5 rounded-lg p-6 card-highlight" wire:ignore>
    <h3 class="text-lg font-semibold text-white mb-2">{{ $chartTitle }}</h3>
    <p class="text-sm text-gray-400 mb-4">Number of journal entries written each day</p>
    <x-chart id="weeklyChart" height="320px" :config="json_encode([
        'type' => 'bar',
        'data' => [
            'labels' => $weeklyLabels,
            'datasets' => [
                [
                    'label' => 'Entries',
                    'data' => $weeklyData,
                    'backgroundColor' => 'rgba(210, 214, 220, 0.9)',
                    'borderColor' => 'rgba(210, 214, 220, 1)',
                    'borderWidth' => 0,
                    'borderRadius' => 4
                ]
            ]
        ],
        'options' => [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => ['display' => false]
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'color' => '#9CA3AF',
                        'stepSize' => 1
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
            ]
        ]
    ])" />
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('chart-data-updated', (event) => {
            if (event.chartId === 'weeklyChart') {
                console.log('WeeklyChart: chart-data-updated event received', event);
                if (typeof debouncedInitializeChart_weeklyChart === 'function') {
                    debouncedInitializeChart_weeklyChart(event.labels, event.data);
                } else {
                    console.warn('debouncedInitializeChart_weeklyChart function not found.');
                }
            }
        });
    });
</script>