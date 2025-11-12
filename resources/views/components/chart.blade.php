<div class="chart-container"
    style="position: relative; height: {{ $height ?? '400px' }}; width: 100%; min-height: 200px;">
    <canvas id="{{ $id }}" style="width: 100% !important; height: 100% !important;"></canvas>
</div>

<script>
    console.log('Chart component script loaded for {{ $id }}');

    window.chartInstances = window.chartInstances || {};
    window.chartDebounceTimeouts = window.chartDebounceTimeouts || {};

    // Modify to accept newLabels and newData
    function initializeChart_{{ $id }}(newLabels = null, newData = null) {
        const canvas = document.getElementById('{{ $id }}');
        if (!canvas) {
            console.warn('Canvas element not found for chart {{ $id }}');
            return;
        }

        console.log('Canvas {{ $id }} found:', {
            width: canvas.width,
            height: canvas.height,
            offsetWidth: canvas.offsetWidth,
            offsetHeight: canvas.offsetHeight
        });

        if (typeof Chart === 'undefined') {
            console.warn('Chart.js not loaded, retrying...');
            setTimeout(() => initializeChart_{{ $id }}(newLabels, newData), 100); // Pass params on retry
            return;
        }

        try {
            if (window.chartInstances['{{ $id }}']) {
                console.log('Destroying existing chart {{ $id }}');
                window.chartInstances['{{ $id }}'].destroy();
                delete window.chartInstances['{{ $id }}'];
            }

            const ctx = canvas.getContext('2d');
            let config = {!! $config !!}; // Keep original config for default options

            // OVERRIDE labels and data if new ones are provided
            if (newLabels && newData) {
                config.data.labels = newLabels;
                config.data.datasets[0].data = newData;
                console.log('--- Chart {{ $id }} updated with new event data ---');
            } else {
                console.log('--- Chart {{ $id }} new config data (from initial render) ---');
            }
            console.log('Labels:', config.data.labels);
            if (config.data.datasets && config.data.datasets.length > 0) {
                console.log('Data (first dataset):', config.data.datasets[0].data);
            } else {
                console.log('No datasets found for this chart.');
            }
            console.log('------------------------------------');

            if (!config?.data?.labels || !config?.data?.datasets) {
                console.warn('Invalid chart config for {{ $id }}:', config);
                return;
            }

            if (config.data.labels.length === 0) {
                console.warn('Empty data for chart {{ $id }}');
                return;
            }

            console.log('Creating chart {{ $id }} with', config.data.labels.length, 'labels');
            const chart = new Chart(ctx, config);
            window.chartInstances['{{ $id }}'] = chart;

            console.log('Chart {{ $id }} initialized');
        } catch (error) {
            console.error('Error initializing chart {{ $id }}:', error);
        }
    }

    // Modify to accept newLabels and newData
    function debouncedInitializeChart_{{ $id }}(newLabels = null, newData = null) {
        clearTimeout(window.chartDebounceTimeouts['{{ $id }}']);
        // Pass parameters to the initialize function
        window.chartDebounceTimeouts['{{ $id }}'] = setTimeout(() => initializeChart_{{ $id }}(newLabels, newData), 100);
    }

    // Initial load will use the config from Blade (newLabels/newData will be null)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', debouncedInitializeChart_{{ $id }});
    } else {
        debouncedInitializeChart_{{ $id }}();
    }

    // `livewire:init` listener is now in the parent component
    // `livewire:navigated` is for full page navigation, also keep here
    document.addEventListener('livewire:navigated', debouncedInitializeChart_{{ $id }});
</script>