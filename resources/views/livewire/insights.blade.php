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

    <!-- Debug Info (remove this after testing) -->
    @if(config('app.debug'))
        <div class="bg-gray-800 p-4 rounded-lg mb-4">
            <h3 class="text-white font-semibold mb-2">Debug Info:</h3>
            <pre class="text-xs text-gray-300">{{ json_encode($this->debugData(), JSON_PRETTY_PRINT) }}</pre>
        </div>
        <button wire:click="loadSummaryStats">Refresh Streak Data</button>
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

    <!-- Charts -->
    <div class="grid gap-4 md:grid-cols-2">
        <!-- Weekly/Period Chart -->
        <livewire:charts.weekly-chart :selectedPeriod="$selectedPeriod" />

        <!-- Tag Usage Chart -->
        <livewire:charts.tag-chart :selectedPeriod="$selectedPeriod" />

        <!-- Writing Streak Chart (spans full width) -->
        <div class="col-span-2">
            <livewire:charts.streak-chart :selectedPeriod="$selectedPeriod" />
        </div>
    </div>
</div>

{{-- Include Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>