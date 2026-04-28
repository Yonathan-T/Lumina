<div class="chart-container" wire:ignore
    style="position: relative; height: {{ $height ?? '400px' }}; width: 100%; min-height: 200px;">
    <canvas id="{{ $id }}" style="width: 100% !important; height: 100% !important;"></canvas>
</div>

<script>
    window.chartInstances = window.chartInstances || {};
    window.chartDebounceTimeouts = window.chartDebounceTimeouts || {};
    window.chartInitializers = window.chartInitializers || {};

    function initializeChart_{{ $id }}(newConfig = null) {
        const canvas = document.getElementById('{{ $id }}');
        if (!canvas) {
            return;
        }

        if (typeof Chart === 'undefined') {
            setTimeout(() => initializeChart_{{ $id }}(newConfig), 100);
            return;
        }

        try {
            if (window.chartInstances['{{ $id }}']) {
                window.chartInstances['{{ $id }}'].destroy();
                delete window.chartInstances['{{ $id }}'];
            }

            const ctx = canvas.getContext('2d');
            const config = newConfig ?? {!! $config !!};

            if (!config?.data?.labels || !config?.data?.datasets) {
                return;
            }

            if (config.data.labels.length === 0) {
                return;
            }

            const chart = new Chart(ctx, config);
            window.chartInstances['{{ $id }}'] = chart;
        } catch (error) {
            console.error('Error initializing chart {{ $id }}:', error);
        }
    }

    function debouncedInitializeChart_{{ $id }}(newConfig = null) {
        clearTimeout(window.chartDebounceTimeouts['{{ $id }}']);
        window.chartDebounceTimeouts['{{ $id }}'] = setTimeout(() => initializeChart_{{ $id }}(newConfig), 60);
    }

    window.chartInitializers['{{ $id }}'] = debouncedInitializeChart_{{ $id }};

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', debouncedInitializeChart_{{ $id }});
    } else {
        debouncedInitializeChart_{{ $id }}();
    }

    document.addEventListener('livewire:init', () => {
        Livewire.on('chart-data-updated', (event) => {
            const payload = Array.isArray(event) ? event[0] : event;
            const chartConfig = payload?.['{{ $id }}'];

            if (chartConfig) {
                debouncedInitializeChart_{{ $id }}({
                    ...{!! $config !!},
                    data: {
                        ...{!! $config !!}.data,
                        labels: chartConfig.labels,
                        datasets: {!! $config !!}.data.datasets.map((dataset, index) => ({
                            ...dataset,
                            data: chartConfig.data[index] ?? chartConfig.data,
                        })),
                    },
                });
            }
        });
    });

    document.addEventListener('livewire:navigated', debouncedInitializeChart_{{ $id }});
</script>
