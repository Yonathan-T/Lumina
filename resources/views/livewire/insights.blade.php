<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-white">Insights</h1>
            <p class="text-muted-foreground">Analyze your journaling patterns and habits</p>
        </div>
        <div class="relative">
            <select wire:model.live="selectedPeriod"
                class="bg-gradient-dark border border-white/20 text-white rounded-md px-3 py-2 text-sm">
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="year">This Year</option>
                <option value="all">All Time</option>
            </select>
        </div>
    </div>

    <!-- Debug Info -->
    @if(config('app.debug'))
        <!-- <div class="bg-gray-800 p-4 rounded-lg mb-4">
                <h3 class="text-white font-semibold mb-2">Debug Info:</h3>
                <pre class="text-xs text-gray-300">{{ json_encode($this->debugData(), JSON_PRETTY_PRINT) }}</pre>
            </div>
            <button wire:click="loadSummaryStats">Refresh Streak Data</button> -->

    @endif

    <!-- Summary Cards -->
    <div class="grid gap-4 md:grid-cols-4">
        <div class="card-highlight bg-gradient-dark border border-white/5 rounded-lg p-4">
            <div class="text-sm font-medium text-gray-400">Total Words Written</div>
            <div class="text-2xl font-bold text-white">{{ number_format($totalWords) }}</div>
            <p class="text-xs text-gray-500">
                @if($totalWordsChange > 0)
                    +{{ number_format($totalWordsChange) }} more than last {{ $this->getPeriodName() }}
                @elseif($totalWordsChange < 0)
                    {{ number_format(abs($totalWordsChange)) }} fewer than last {{ $this->getPeriodName() }}
                @else
                    Same as last {{ $this->getPeriodName() }}
                @endif
            </p>
        </div>

        <div class="card-highlight bg-gradient-dark border border-white/5 rounded-lg p-4">
            <div class="text-sm font-medium text-gray-400">Average Entry Length</div>
            <div class="text-2xl font-bold text-white">{{ $avgLength }} words</div>
            <p class="text-xs text-gray-500">Based on
                {{ $selectedPeriod == 'week' ? 'this week' : ($selectedPeriod == 'month' ? 'this month' : ($selectedPeriod == 'year' ? 'this year' : 'all time')) }}
            </p>
        </div>

        <div class="card-highlight bg-gradient-dark border border-white/5 rounded-lg p-4">
            <div class="text-sm font-medium text-gray-400">Most Reflective Day</div>
            <div class="text-2xl font-bold text-white">{{ $mostReflectiveDay }}</div>
            <p class="text-xs text-gray-500">{{ $mostReflectiveDayEntries }} entries on average</p>
        </div>

        <div class="card-highlight bg-gradient-dark border border-white/5 rounded-lg p-4">
            <div class="text-sm font-medium text-gray-400">Longest Streak</div>
            <div class="text-2xl font-bold text-white">{{ $longestStreak }} days</div>
            <p class="text-xs text-gray-500">Current streak: {{ $currentStreak }} days</p>
        </div>
    </div>
    <!-- <pre>{{ json_encode($tagData) }}</pre>
    <pre>{{ json_encode($streakData) }}</pre> -->
    <!-- Charts -->

    <div class="grid gap-4 md:grid-cols-2">
        <div class="bg-gradient-dark border border-white/5 rounded-lg p-6 card-highlight" wire:ignore>

            <h3 class="text-lg font-semibold text-white mb-2">Entries per Week</h3>
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

        <div class="col-span-2 bg-gradient-dark border border-white/5 rounded-lg p-6 card-highlight" wire:ignore> {{--
            ADD wire:ignore HERE --}}
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
    </div>
</div>

{{-- Include Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // This script runs within the main Livewire component's scope
    document.addEventListener('livewire:init', () => {
        Livewire.on('chart-data-updated', () => { // No chartId parameter needed here, we'll trigger all
            console.log('PARENT: chart-data-updated event received. Re-initializing all charts.');

            // Call the debounced initialization function for each chart by its ID
            if (typeof debouncedInitializeChart_weeklyChart === 'function') {
                debouncedInitializeChart_weeklyChart();
            } else {
                console.warn('debouncedInitializeChart_weeklyChart function not found.');
            }

            if (typeof debouncedInitializeChart_tagChart === 'function') {
                debouncedInitializeChart_tagChart();
            } else {
                console.warn('debouncedInitializeChart_tagChart function not found.');
            }

            if (typeof debouncedInitializeChart_streakChart === 'function') {
                debouncedInitializeChart_streakChart();
            } else {
                console.warn('debouncedInitializeChart_streakChart function not found.');
            }
        });
    });
</script>