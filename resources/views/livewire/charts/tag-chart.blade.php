<div class="bg-gradient-dark border border-white/5 rounded-lg p-6 card-highlight" wire:ignore>
    <h3 class="text-lg font-semibold text-white mb-2">Tag Usage</h3>
    <p class="text-sm text-gray-400 mb-4">Distribution of tags across your entries</p>
    <x-chart id="tagChart" height="320px" :config="json_encode([
        'type' => 'doughnut',
        'data' => [
            'labels' => array_keys($tagData),
            'datasets' => [
                [
                    'data' => array_values($tagData),
                    'backgroundColor' => [
                        '#3B82F6', // Blue
                        '#10B981', // Green
                        '#F59E0B', // Yellow
                        '#EF4444', // Red
                        '#8B5CF6'  // Purple
                    ],
                    'borderWidth' => 0,
                    'cutout' => '60%'
                ]
            ]
        ],
        'options' => [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'labels' => [
                        'color' => '#9CA3AF',
                        'usePointStyle' => true,
                        'padding' => 20
                    ]
                ]
            ]
        ]
    ])" />
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('chart-data-updated', (event) => {
            if (event.chartId === 'tagChart') {
                console.log('TagChart: chart-data-updated event received', event);
                if (typeof debouncedInitializeChart_tagChart === 'function') {
                    debouncedInitializeChart_tagChart(event.labels, event.data);
                } else {
                    console.warn('debouncedInitializeChart_tagChart function not found.');
                }
            }
        });
    });
</script>