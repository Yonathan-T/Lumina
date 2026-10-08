@php
    $s = $data['summary'] ?? [];
    $asciiArt = \App\Services\AsciiArtService::frames();
@endphp

<script>
    window.__asciiArt = @json($asciiArt);
</script>
<div class="space-y-6" x-data="{
        dayOpen: false,
        dayLoading: false,
        dayLabel: '',
        dayEntries: [],
        badgeOpen: false,
        activeBadgeIdx: 0,
        badges: {{ \Illuminate\Support\Js::from($data['achievements'] ?? []) }},
        frameIdx: 0,
        init() {
            setInterval(() => {
                this.frameIdx = (this.frameIdx + 1) % 4;
            }, 280);
        },
        openBadge(idx) {
            this.activeBadgeIdx = idx;
            this.badgeOpen = true;
        },
        nextBadge() {
            if (!this.badges || !this.badges.length) return;
            this.activeBadgeIdx = (this.activeBadgeIdx + 1) % this.badges.length;
        },
        prevBadge() {
            if (!this.badges || !this.badges.length) return;
            this.activeBadgeIdx = (this.activeBadgeIdx - 1 + this.badges.length) % this.badges.length;
        },
        getArt(piece) {
            return (window.__asciiArt && window.__asciiArt[piece]) ? (window.__asciiArt[piece][this.frameIdx] || '') : '';
        },
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
                                ? 'bg-blue-600 text-white shadow shadow-blue-500/20'
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
                <span class="h-3 w-3 rounded-sm bg-blue-950/70 border border-blue-900/50"></span>
                <span class="h-3 w-3 rounded-sm bg-blue-800/80"></span>
                <span class="h-3 w-3 rounded-sm bg-blue-600"></span>
                <span class="h-3 w-3 rounded-sm bg-blue-400"></span>
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
                                        4 => 'bg-blue-400',
                                        3 => 'bg-blue-600',
                                        2 => 'bg-blue-800/80',
                                        1 => 'bg-blue-950/70 border border-blue-900/50',
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

    <!-- Milestones Section -->
    <div class="card-highlight rounded-lg border border-white/5 bg-gradient-dark p-6">
        <div class="mb-4">
            <h3 class="mb-1 text-lg font-semibold text-white">Milestones</h3>
            <p class="text-sm text-gray-400">Badges you unlock as your journaling journey grows</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @foreach($data['achievements'] ?? [] as $idx => $a)
                <div @click="openBadge({{ $idx }})"
                    role="button"
                    tabindex="0"
                    @keydown.enter="openBadge({{ $idx }})"
                    @keydown.space.prevent="openBadge({{ $idx }})"
                    title="{{ $a['done'] ? 'Click to view ' . $a['title'] . ' in full screen' : $a['title'] . ' (Locked)' }}"
                    class="card-highlight group relative flex flex-col justify-between rounded-lg border p-3 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 select-none bg-gradient-dark text-left {{ $a['done'] ? 'border-blue-500/30 hover:border-blue-400/60' : 'border-white/5 opacity-75 hover:opacity-100 hover:border-white/10' }}"
                >
                    <!-- Animation Preview Box -->
                    <div class="h-20 w-full rounded bg-black/40 border border-white/5 flex items-center justify-center overflow-hidden p-1 transition-colors group-hover:border-white/20">
                        @if($a['done'])
                            <pre class="m-0 font-mono text-[5.5px] leading-[6.5px] text-blue-300 group-hover:text-white select-none text-center pointer-events-none transition-colors"
                                 x-text="getArt('{{ $a['piece'] }}')"></pre>
                        @else
                            <div class="flex flex-col items-center justify-center text-zinc-500 gap-1 pointer-events-none">
                                <svg class="w-4 h-4 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span class="text-[9px] font-mono tracking-wider uppercase text-zinc-600">Locked</span>
                            </div>
                        @endif
                    </div>

                    <!-- Card Title & Status -->
                    <div class="mt-2.5 space-y-1">
                        <div class="text-xs font-semibold tracking-tight truncate {{ $a['done'] ? 'text-white' : 'text-gray-400' }}">
                            {{ $a['title'] }}
                        </div>

                        @if($a['done'])
                            <div class="flex items-center justify-between text-[10px]">
                                <span class="text-blue-400 font-medium flex items-center gap-1">
                                    <svg class="w-2.5 h-2.5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Unlocked
                                </span>
                                <span class="text-[9px] text-gray-500 group-hover:text-gray-300">View</span>
                            </div>
                        @else
                            <div class="space-y-1">
                                <div class="h-1 w-full overflow-hidden rounded-full bg-white/10">
                                    <div class="h-full rounded-full bg-blue-600 transition-all duration-300" style="width: {{ $a['progressPct'] }}%"></div>
                                </div>
                                <div class="flex items-center justify-between text-[9px] text-zinc-500 font-mono">
                                    <span>{{ $a['progressLabel'] }}</span>
                                    <span>{{ $a['progressPct'] }}%</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Dedicated Large-Screen Milestone Artwork Modal --}}
    <div x-show="badgeOpen" x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        x-on:keydown.escape.window="badgeOpen = false"
        x-on:keydown.arrow-left.window="if (badgeOpen) prevBadge()"
        x-on:keydown.arrow-right.window="if (badgeOpen) nextBadge()"
    >
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" 
            x-on:click="badgeOpen = false"
            x-show="badgeOpen" 
            x-transition.opacity>
        </div>

        {{-- Large Screen Modal Card --}}
        <div class="relative z-10 w-full max-w-2xl overflow-hidden rounded-xl border border-white/10 bg-gradient-dark shadow-2xl p-5 sm:p-6 text-left"
            x-show="badgeOpen"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            {{-- Top Bar --}}
            <div class="flex items-center justify-between border-b border-white/10 pb-3 mb-4">
                <div class="flex items-center gap-2">
                    <span class="text-xs uppercase font-semibold tracking-wider text-blue-400" x-text="badges[activeBadgeIdx]?.artName || 'Art'"></span>
                    <span class="text-zinc-600">•</span>
                    <span class="text-xs text-zinc-400" x-text="badges[activeBadgeIdx]?.title"></span>
                </div>
                <button type="button" @click="badgeOpen = false"
                    class="rounded-md p-1.5 text-gray-400 transition hover:bg-white/10 hover:text-white"
                    aria-label="Close"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <template x-if="badges[activeBadgeIdx]">
                <div class="space-y-4">
                    {{-- Large ASCII Screen Viewport --}}
                    <div class="w-full min-h-[260px] sm:min-h-[300px] rounded-lg bg-black/50 border border-white/10 p-6 flex items-center justify-center shadow-inner relative overflow-hidden">
                        <template x-if="badges[activeBadgeIdx]?.done">
                            <pre class="m-0 font-mono text-xs sm:text-sm md:text-base leading-snug text-blue-300 select-none text-center whitespace-pre transition-colors duration-200"
                                 x-text="getArt(badges[activeBadgeIdx]?.piece)"></pre>
                        </template>
                        <template x-if="!badges[activeBadgeIdx]?.done">
                            <div class="flex flex-col items-center justify-center text-center py-8 text-zinc-400 space-y-3">
                                <div class="w-12 h-12 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-zinc-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-white">Artwork Locked</p>
                                    <p class="text-xs text-zinc-400 mt-1 max-w-xs">Write more entries to unlock and animate this art piece.</p>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Lore / Description & Progress --}}
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 border-t border-white/10 pt-4">
                        <div class="space-y-1.5 max-w-md">
                            <h4 class="text-lg font-bold text-white tracking-tight" x-text="badges[activeBadgeIdx]?.title"></h4>
                            <p class="text-sm text-gray-300 leading-relaxed"
                                x-text="badges[activeBadgeIdx]?.done ? badges[activeBadgeIdx]?.doneMsg : badges[activeBadgeIdx]?.todoMsg"
                            ></p>
                        </div>

                        {{-- Progress Meter or Unlocked Badge in completed space --}}
                        <div class="shrink-0 text-left sm:text-right">
                            <template x-if="badges[activeBadgeIdx]?.done">
                                <span class="inline-flex items-center gap-1.5 text-xs text-blue-400 font-medium bg-blue-950/60 border border-blue-500/30 px-3 py-1 rounded-full">
                                    <svg class="w-3.5 h-3.5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Unlocked
                                </span>
                            </template>
                            <template x-if="!badges[activeBadgeIdx]?.done">
                                <div class="space-y-1.5 min-w-[140px]">
                                    <div class="text-xs text-zinc-400 flex items-center justify-between sm:justify-end gap-2 font-mono">
                                        <span x-text="badges[activeBadgeIdx]?.progressLabel"></span>
                                        <span class="text-white" x-text="badges[activeBadgeIdx]?.progressPct + '%'"></span>
                                    </div>
                                    <div class="w-full sm:w-36 h-1.5 rounded-full bg-white/10 overflow-hidden sm:ml-auto">
                                        <div class="h-full rounded-full bg-blue-600 transition-all duration-300"
                                            :style="'width: ' + badges[activeBadgeIdx]?.progressPct + '%'"
                                        ></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Footer Navigation: Prev / Dots / Next --}}
                    <div class="flex items-center justify-between border-t border-white/10 pt-4">
                        <button type="button" @click="prevBadge()"
                            class="rounded-md border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-gray-300 hover:text-white hover:bg-white/10 flex items-center gap-2 transition active:scale-95 font-medium"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            <span>Previous</span>
                        </button>

                        {{-- Pagination Dots --}}
                        <div class="flex items-center gap-1.5">
                            <template x-for="(b, i) in badges" :key="i">
                                <button type="button" @click="activeBadgeIdx = i"
                                    class="transition-all duration-300 rounded-full"
                                    :class="activeBadgeIdx === i ? 'w-5 h-1.5 bg-blue-500' : 'w-1.5 h-1.5 bg-white/20 hover:bg-white/40'"
                                    :aria-label="'Go to milestone ' + (i + 1)"
                                ></button>
                            </template>
                        </div>

                        <button type="button" @click="nextBadge()"
                            class="rounded-md border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-gray-300 hover:text-white hover:bg-white/10 flex items-center gap-2 transition active:scale-95 font-medium"
                        >
                            <span>Next</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
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
                        class="block rounded-lg border border-white/5 bg-white/5 p-3 transition hover:border-blue-500/50 hover:bg-white/10">
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
