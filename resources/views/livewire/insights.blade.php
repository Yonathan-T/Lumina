@php($s = $data['summary'] ?? [])
<div class="space-y-6" x-data="{
        dayOpen: false,
        dayLoading: false,
        dayLabel: '',
        dayEntries: [],
        async openDay(date) {
            this.dayOpen = true;
            this.dayLoading = true;
            this.dayEntries = [];
            this.dayLabel = '';
            try {
                const res = await fetch('{{ url('/insights/day') }}/' + date, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error('request failed');
                const data = await res.json();
                this.dayLabel = data.label;
                this.dayEntries = data.entries;
            } catch (e) {
                this.dayLabel = 'Could not load entries';
                this.dayEntries = [];
            } finally {
                this.dayLoading = false;
            }
        }
    }">
    <!-- Header + period selector -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-white">Insights</h1>
            <p class="text-muted-foreground">Analyze your journaling patterns and habits</p>
        </div>

        <div class="flex items-center gap-3">
            <span wire:loading class="text-xs text-gray-400 animate-pulse">Updating…</span>
            <div class="inline-flex rounded-lg border border-white/10 bg-white/5 p-1">
                @foreach(['week' => 'Week', 'month' => 'Month', 'year' => 'Year', 'all' => 'All Time'] as $val => $label)
                    <button type="button" wire:click="setPeriod('{{ $val }}')" wire:loading.attr="disabled"
                        class="rounded-md px-3 py-1.5 text-sm font-medium transition
                            {{ $selectedPeriod === $val
                                ? 'bg-violet-600 text-white shadow'
                                : 'text-gray-400 hover:text-white' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Summary stat cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card-highlight rounded-lg border border-white/5 bg-gradient-dark p-4">
            <div class="text-sm font-medium text-gray-400">Words Written</div>
            <div class="text-2xl font-bold text-white">{{ number_format($s['totalWords'] ?? 0) }}</div>
            <p class="text-xs text-gray-500">
                @php($chg = $s['wordsChange'] ?? 0)
                @if($chg > 0)
                    <span class="text-emerald-400">▲ {{ number_format($chg) }}</span> vs last {{ $data['periodLabel'] ?? 'period' }}
                @elseif($chg < 0)
                    <span class="text-rose-400">▼ {{ number_format(abs($chg)) }}</span> vs last {{ $data['periodLabel'] ?? 'period' }}
                @else
                    Same as last {{ $data['periodLabel'] ?? 'period' }}
                @endif
            </p>
        </div>

        <div class="card-highlight rounded-lg border border-white/5 bg-gradient-dark p-4">
            <div class="text-sm font-medium text-gray-400">Avg Entry Length</div>
            <div class="text-2xl font-bold text-white">{{ number_format($s['avgWords'] ?? 0) }} <span class="text-base font-normal text-gray-400">words</span></div>
            <p class="text-xs text-gray-500">Longest: {{ number_format($s['longestEntryWords'] ?? 0) }} words</p>
        </div>

        <div class="card-highlight rounded-lg border border-white/5 bg-gradient-dark p-4">
            <div class="text-sm font-medium text-gray-400">Current Streak</div>
            <div class="text-2xl font-bold text-white">{{ $s['currentStreak'] ?? 0 }} <span class="text-base font-normal text-gray-400">days</span></div>
            <p class="text-xs text-gray-500">{{ $s['streakMessage'] ?? '' }}</p>
        </div>

        <div class="card-highlight rounded-lg border border-white/5 bg-gradient-dark p-4">
            <div class="text-sm font-medium text-gray-400">Longest Streak</div>
            <div class="text-2xl font-bold text-white">{{ $s['longestStreak'] ?? 0 }} <span class="text-base font-normal text-gray-400">days</span></div>
            <p class="text-xs text-gray-500">{{ number_format($s['activeDays'] ?? 0) }} active days total</p>
        </div>
    </div>

    <!-- Secondary stat strip -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card-highlight rounded-lg border border-white/5 bg-gradient-dark p-4">
            <div class="text-sm font-medium text-gray-400">Total Entries</div>
            <div class="text-2xl font-bold text-white">{{ number_format($s['totalEntries'] ?? 0) }}</div>
            <p class="text-xs text-gray-500">{{ ($s['periodEntries'] ?? 0) }} {{ $data['periodLabel'] ?? '' }}</p>
        </div>
        <div class="card-highlight rounded-lg border border-white/5 bg-gradient-dark p-4">
            <div class="text-sm font-medium text-gray-400">Most Active Day</div>
            <div class="text-2xl font-bold text-white">{{ $s['peakDay'] ?? '—' }}</div>
            <p class="text-xs text-gray-500">{{ $s['peakDayCount'] ?? 0 }} entries {{ $data['periodLabel'] ?? '' }}</p>
        </div>
        <div class="card-highlight rounded-lg border border-white/5 bg-gradient-dark p-4">
            <div class="text-sm font-medium text-gray-400">Peak Writing Time</div>
            <div class="text-2xl font-bold text-white">{{ $s['peakHourLabel'] ?? '—' }}</div>
            <p class="text-xs text-gray-500">when you write most</p>
        </div>
        <div class="card-highlight rounded-lg border border-white/5 bg-gradient-dark p-4">
            <div class="text-sm font-medium text-gray-400">Journaling Since</div>
            <div class="text-2xl font-bold text-white">{{ $s['firstEntry'] ? \Carbon\Carbon::parse($s['firstEntry'])->format('M Y') : '—' }}</div>
            <p class="text-xs text-gray-500">last entry {{ $s['lastEntryHuman'] ?? '—' }}</p>
        </div>
    </div>

    <!-- Calendar heatmap -->
    @php($hm = $data['heatmap'] ?? ['grid' => [], 'monthLabels' => [], 'totalInRange' => 0])
    <div class="card-highlight rounded-lg border border-white/5 bg-gradient-dark p-6">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-white">Activity</h3>
                <p class="text-sm text-gray-400">{{ number_format($hm['totalInRange']) }} entries in the last year</p>
            </div>
            <div class="hidden items-center gap-1.5 text-xs text-gray-500 sm:flex">
                <span>Less</span>
                <span class="h-3 w-3 rounded-sm bg-white/5"></span>
                <span class="h-3 w-3 rounded-sm bg-violet-900/60"></span>
                <span class="h-3 w-3 rounded-sm bg-violet-700/70"></span>
                <span class="h-3 w-3 rounded-sm bg-violet-500"></span>
                <span class="h-3 w-3 rounded-sm bg-violet-400"></span>
                <span>More</span>
            </div>
        </div>

        @if(($hm['totalInRange'] ?? 0) === 0)
            <div class="flex h-32 items-center justify-center text-center text-sm text-gray-500">
                📓 No activity yet.<br>Start journaling to light up your year!
            </div>
        @else
            <div class="overflow-x-auto pb-2">
                <div class="inline-flex flex-col gap-1">
                    <!-- month labels -->
                    <div class="flex gap-[3px] pl-1 text-[10px] text-gray-500">
                        @foreach($hm['monthLabels'] as $m)
                            <div class="w-[13px] shrink-0">{{ $m }}</div>
                        @endforeach
                    </div>
                    <!-- week columns -->
                    <div class="flex gap-[3px]">
                        @foreach($hm['grid'] as $week)
                            <div class="flex flex-col gap-[3px]">
                                @foreach($week as $cell)
                                    @php($color = $cell['future'] ? 'bg-transparent' : match($cell['level']) {
                                        4 => 'bg-violet-400',
                                        3 => 'bg-violet-500',
                                        2 => 'bg-violet-700/70',
                                        1 => 'bg-violet-900/60',
                                        default => 'bg-white/5',
                                    })
                                    @if($cell['count'] > 0 && ! $cell['future'])
                                        <button type="button" x-on:click="openDay('{{ $cell['date'] }}')"
                                            title="{{ $cell['count'] }} {{ \Illuminate\Support\Str::plural('entry', $cell['count']) }} · {{ $cell['label'] }} — click to view"
                                            class="h-[13px] w-[13px] cursor-pointer rounded-sm transition hover:ring-2 hover:ring-white/50 {{ $color }}"></button>
                                    @else
                                        <div title="{{ $cell['future'] ? $cell['label'] : $cell['count'].' entries · '.$cell['label'] }}"
                                            class="h-[13px] w-[13px] rounded-sm {{ $color }}"></div>
                                    @endif
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Charts -->
    <div class="grid gap-4 lg:grid-cols-2">
        <x-insights-chart id="entriesChart" title="Entries Over Time"
            subtitle="How many entries you wrote each {{ $selectedPeriod === 'week' ? 'day' : ($selectedPeriod === 'year' ? 'month' : ($selectedPeriod === 'all' ? 'year' : 'day')) }}"
            class="lg:col-span-2" />

        <x-insights-chart id="wordChart" title="Words Written" subtitle="Your writing volume over time" />
        <x-insights-chart id="dowChart" title="Day of the Week" subtitle="Which days you write most, {{ $data['periodLabel'] ?? '' }}" />

        <x-insights-chart id="todChart" title="Time of Day" subtitle="When during the day you tend to journal" height="260px" />
        <x-insights-chart id="tagChart" title="Top Tags" subtitle="What you write about most, {{ $data['periodLabel'] ?? '' }}" height="260px" />

        <x-insights-chart id="streakChart" title="Writing Streak"
            subtitle="Your consecutive-day streak {{ $selectedPeriod === 'week' ? 'this past week' : ($selectedPeriod === 'month' ? 'over the last 30 days' : ($selectedPeriod === 'year' ? 'over the last year' : 'across your whole history')) }}"
            class="lg:col-span-2" />
    </div>

    <!-- Achievements -->
    <div class="card-highlight rounded-lg border border-white/5 bg-gradient-dark p-6">
        <h3 class="mb-1 text-lg font-semibold text-white">Milestones</h3>
        <p class="mb-4 text-sm text-gray-400">Badges you unlock as your journaling journey grows</p>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach($data['achievements'] ?? [] as $a)
                <div class="group relative flex flex-col items-center rounded-lg border p-3 text-center transition
                    {{ $a['done'] ? 'border-violet-500/30 bg-white/5 hover:border-violet-500/60' : 'border-white/5 hover:border-white/20' }}">
                    <div class="text-3xl transition group-hover:scale-110 {{ $a['done'] ? '' : 'opacity-50 grayscale' }}">{{ $a['icon'] }}</div>
                    <div class="mt-1 text-sm font-medium {{ $a['done'] ? 'text-white' : 'text-gray-400' }}">{{ $a['title'] }}</div>

                    @if($a['done'])
                        <div class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-emerald-400">✓ Unlocked</div>
                    @else
                        <div class="mt-2 h-1 w-full overflow-hidden rounded-full bg-white/10">
                            <div class="h-full rounded-full bg-violet-500 transition-all" style="width: {{ $a['progressPct'] }}%"></div>
                        </div>
                        <div class="mt-1 text-[10px] text-gray-500">{{ $a['progressLabel'] }}</div>
                    @endif

                    {{-- Fun hover tooltip --}}
                    <div class="pointer-events-none absolute bottom-full left-1/2 z-30 mb-2 w-48 -translate-x-1/2 translate-y-1 rounded-lg border border-white/10 bg-gray-900/95 px-3 py-2 text-xs leading-snug text-gray-200 opacity-0 shadow-xl transition-all duration-150 group-hover:translate-y-0 group-hover:opacity-100">
                        {{ $a['tooltip'] }}
                        <div class="absolute left-1/2 top-full h-0 w-0 -translate-x-1/2 border-4 border-transparent border-t-gray-900/95"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Heatmap day drill-down modal (client-side fetch: opens instantly) --}}
    <div x-show="dayOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-on:keydown.escape.window="dayOpen = false">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" x-on:click="dayOpen = false"
            x-show="dayOpen" x-transition.opacity></div>

        <div class="relative z-10 w-full max-w-lg overflow-hidden rounded-xl border border-white/10 bg-gradient-dark shadow-2xl"
            x-show="dayOpen"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-start justify-between border-b border-white/10 p-4">
                <div>
                    <h3 class="text-lg font-semibold text-white" x-text="dayLabel">&nbsp;</h3>
                    <p class="text-xs text-gray-400" x-show="!dayLoading">
                        <span x-text="dayEntries.length"></span>
                        <span x-text="dayEntries.length === 1 ? 'entry' : 'entries'"></span> on this day
                    </p>
                </div>
                <button type="button" x-on:click="dayOpen = false"
                    class="rounded-md p-1.5 text-gray-400 transition hover:bg-white/10 hover:text-white" aria-label="Close">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="max-h-[60vh] space-y-3 overflow-y-auto p-4">
                <template x-if="dayLoading">
                    <div class="flex items-center justify-center gap-2 py-8 text-sm text-gray-400">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        Loading…
                    </div>
                </template>

                <template x-for="entry in dayEntries" :key="entry.url">
                    <a :href="entry.url"
                        class="block rounded-lg border border-white/5 bg-white/5 p-3 transition hover:border-violet-500/50 hover:bg-white/10">
                        <div class="flex items-center justify-between gap-2">
                            <h4 class="truncate font-medium text-white" x-text="entry.title"></h4>
                            <span class="shrink-0 text-xs text-gray-500" x-text="entry.time"></span>
                        </div>
                        <p class="mt-1 text-sm text-gray-400" x-show="entry.snippet" x-text="entry.snippet"></p>
                    </a>
                </template>

                <template x-if="!dayLoading && dayEntries.length === 0">
                    <p class="py-6 text-center text-sm text-gray-500">No entries found for this day.</p>
                </template>
            </div>
        </div>
    </div>
</div>

@script
<script>
    const renderInsightsCharts = (charts) => {
        if (typeof window.Chart === 'undefined') {
            setTimeout(() => renderInsightsCharts(charts), 100);
            return;
        }

        window.__insightsCharts = window.__insightsCharts || {};

        Object.entries(charts || {}).forEach(([id, config]) => {
            const canvas = document.getElementById(id);
            if (!canvas) return;

            const wrap = canvas.closest('[data-chart-wrap]');
            const emptyEl = wrap ? wrap.querySelector('[data-chart-empty]') : null;

            // Treat an all-zero series as "no data" and show the friendly empty state.
            const total = (config.data.datasets || []).reduce(
                (sum, ds) => sum + (ds.data || []).reduce((a, b) => a + (Number(b) || 0), 0), 0
            );

            if (window.__insightsCharts[id]) {
                window.__insightsCharts[id].destroy();
                delete window.__insightsCharts[id];
            }

            if (total <= 0) {
                canvas.style.display = 'none';
                if (emptyEl) emptyEl.style.display = 'flex';
                return;
            }

            canvas.style.display = 'block';
            if (emptyEl) emptyEl.style.display = 'none';
            window.__insightsCharts[id] = new window.Chart(canvas.getContext('2d'), config);
        });
    };

    renderInsightsCharts(@js($charts));

    $wire.on('insights-refreshed', (event) => {
        const payload = Array.isArray(event) ? event[0] : event;
        renderInsightsCharts(payload.charts);
    });
</script>
@endscript
