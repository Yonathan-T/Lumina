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

    <!-- Charts -->
    <div class="grid gap-4 md:grid-cols-2">

        <!-- Entries per Week -->
        <div class="bg-gradient-dark border border-white/5 rounded-lg p-6 card-highlight">
            <h3 class="text-lg font-semibold text-white mb-2">Entries per Week</h3>
            <p class="text-sm text-gray-400 mb-4">Number of journal entries written each day</p>

            @if(empty($weeklyData) || count(array_filter($weeklyData)) === 0)
                <div class="h-80 flex items-center justify-center text-gray-500 text-sm text-center">
                    📓 No data yet.<br> Start journaling to see your daily and weekly patterns!
                </div>
            @else
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
                        'plugins' => ['legend' => ['display' => false]],
                        'scales' => [
                            'y' => [
                                'beginAtZero' => true,
                                'ticks' => ['color' => '#9CA3AF', 'stepSize' => 1],
                                'grid' => ['color' => '#374151', 'drawBorder' => false]
                            ],
                            'x' => [
                                'ticks' => ['color' => '#9CA3AF'],
                                'grid' => ['display' => false]
                            ]
                        ]
                    ]
                ])" />
            @endif
        </div>

        <!-- Tag Usage -->
        <div class="bg-gradient-dark border border-white/5 rounded-lg p-6 card-highlight">
            <h3 class="text-lg font-semibold text-white mb-2">Tag Usage</h3>
            <p class="text-sm text-gray-400 mb-4">Distribution of tags across your entries</p>

            @if(empty($tagData) || count(array_filter($tagData)) === 0)
                <div class="h-60 flex items-center justify-center text-gray-500 text-sm text-center">
                    🏷️ No tags yet.<br> Add tags to your entries to see them here!
                </div>
            @else
                    <x-chart id="tagChart" height="200px" :config="json_encode([
                    'type' => 'pie',
                    'data' => [
                        'labels' => array_keys($tagData),
                        'datasets' => [
                            [
                                'data' => array_values($tagData),
                                'backgroundColor' => collect(array_keys($tagData))->map(function ($tag, $index) {
                                    $colors = [
                                        '#3B82F6',
                                        '#10B981',
                                        '#F59E0B',
                                        '#EF4444',
                                        '#8B5CF6',
                                        '#EC4899',
                                        '#14B8A6',
                                        '#F97316',
                                        '#84CC16',
                                        '#06B6D4',
                                        '#A855F7',
                                        '#D946EF'
                                    ];
                                    return $colors[$index % count($colors)];
                                })->toArray(),
                                'borderWidth' => 0
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
                                    'padding' => 8
                                ]
                            ]
                        ]
                    ]
                ])" />

            @endif
        </div>


        <!-- Writing Streak -->
        <div class="col-span-2 bg-gradient-dark border border-white/5 rounded-lg p-6 card-highlight">
            <h3 class="text-lg font-semibold text-white mb-2">Writing Streak</h3>
            <p class="text-sm text-gray-400 mb-4">Your daily writing streak over time</p>

            @if(empty($streakData) || count(array_filter($streakData)) === 0)
                <div class="h-80 flex items-center justify-center text-gray-500 text-sm text-center">
                    🔥 No streak yet.<br> Write daily to build your streak!
                </div>
            @else
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
                                'labels' => ['color' => '#9CA3AF', 'usePointStyle' => true]
                            ]
                        ],
                        'scales' => [
                            'y' => [
                                'beginAtZero' => true,
                                'ticks' => ['color' => '#9CA3AF', 'stepSize' => 3],
                                'grid' => ['color' => '#374151', 'drawBorder' => false]
                            ],
                            'x' => [
                                'ticks' => ['color' => '#9CA3AF'],
                                'grid' => ['display' => false]
                            ]
                        ],
                        'interaction' => ['intersect' => false, 'mode' => 'index']
                    ]
                ])" />
            @endif
        </div>
    </div>
</div>