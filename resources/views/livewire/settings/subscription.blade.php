@php
    $planStyles = [
        'free' => [
            'tone' => 'from-slate-500/20 to-slate-700/10 border-white/10',
            'accent' => 'text-slate-200',
            'glow' => 'bg-slate-300/10',
        ],
        'standard' => [
            'tone' => 'from-blue-500/20 to-cyan-500/10 border-cyan-400/20',
            'accent' => 'text-cyan-300',
            'glow' => 'bg-cyan-300/10',
        ],
        'pro' => [
            'tone' => 'from-amber-500/20 to-orange-500/10 border-amber-400/20',
            'accent' => 'text-amber-300',
            'glow' => 'bg-amber-300/10',
        ],
    ];

    $current = array_merge(
        $planStyles[$currentPlan] ?? $planStyles['free'],
        $currentPlanMeta ?? []
    );

    $entryLimit = auth()->user()?->getMonthlyEntryLimit() ?? 10;
    $entryLimitLabel = $entryLimit === -1 ? 'Unlimited entries' : $entryLimit . ' entries / month';

    $featureCards = $nextPlan && !empty($nextPlan['benefits'])
        ? collect($nextPlan['benefits'])->take(6)->values()->all()
        : [
            'Everything unlocked',
            'Premium journaling flow',
            'No extra billing steps',
        ];
@endphp

<div class="space-y-8">
    <div class="relative overflow-hidden rounded-2xl border {{ $current['tone'] }} bg-gradient-dark p-8 card-highlight">
        <div class="absolute right-0 top-0 h-40 w-40 rounded-full {{ $current['glow'] }} blur-3xl"></div>

        <div class="relative grid gap-5 lg:grid-cols-[1.6fr_0.9fr]">
            <div class="space-y-4">
                <p class="text-sm uppercase tracking-[0.25em] text-white/45">Current Plan</p>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-3xl font-bold text-white">{{ $current['label'] }}</h2>
                    <span class="rounded-full border border-white/10 px-3 py-1 text-xs font-semibold {{ $current['accent'] }}">
                        {{ ucfirst($currentPlan) }} tier
                    </span>
                </div>
                <p class="max-w-2xl text-sm text-white/65">{{ $current['description'] }}</p>

                <div class="grid gap-3 pt-2 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/40">Billing</p>
                        <p class="mt-2 text-2xl font-bold text-white">{{ $current['price'] ?? '$0/mo' }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/40">Usage</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ $entryLimitLabel }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/40">Status</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ $currentPlan === 'free' ? 'Ready to grow' : 'Active subscription' }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-6 backdrop-blur-sm">
                @if($nextPlan)
                    <p class="text-xs uppercase tracking-[0.22em] text-white/40">Next Plan</p>
                    <h3 class="mt-3 text-2xl font-semibold text-white">{{ $nextPlan['key'] === 'pro' ? 'Pro' : $nextPlan['name'] }}</h3>
                    <p class="mt-2 text-sm text-white/60">{{ $nextPlan['description'] ?: 'The next tier unlocks more depth, more headroom, and more premium reflection tools.' }}</p>
                    <div class="mt-5 rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/40">Upgrade Price</p>
                        <p class="mt-2 text-2xl font-bold text-white">{{ $nextPlan['price'] }}</p>
                    </div>
                    <a href="{{ $nextPlan['checkout_url'] }}"
                        class="mt-5 inline-flex w-full items-center justify-center rounded-xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                        Upgrade to {{ $nextPlan['key'] === 'pro' ? 'Pro' : $nextPlan['name'] }}
                    </a>
                @else
                    <p class="text-xs uppercase tracking-[0.22em] text-white/40">Top Tier</p>
                    <h3 class="mt-3 text-2xl font-semibold text-white">You have the full plan</h3>
                    <p class="mt-2 text-sm text-white/60">There is no higher tier above this one, so everything in Lumina is already unlocked for you.</p>
                    <a href="{{ route('pricing') }}"
                        class="mt-5 inline-flex w-full items-center justify-center rounded-xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                        View all plans
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-white/10 bg-gradient-dark p-8 card-highlight">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">
                    {{ $nextPlan ? 'What unlocks next' : 'What your plan already covers' }}
                </h2>
                <p class="mt-2 text-sm text-white/60">
                    {{ $nextPlan
                        ? 'A cleaner snapshot of the next tier, using your actual subscription ladder instead of a generic promo block.'
                        : 'You are already on the highest tier, so this section reflects what is already included for you.' }}
                </p>
            </div>
        </div>

        <div class="mt-8 rounded-3xl border border-white/10 bg-white/[0.04] p-6">
            @if($nextPlan)
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-xs uppercase tracking-[0.22em] text-white/40">In {{ $nextPlan['key'] === 'pro' ? 'Pro' : $nextPlan['name'] }}</p>
                        <h3 class="mt-3 text-2xl font-semibold text-white">A cleaner step up, without the noise</h3>
                        <p class="mt-2 text-sm text-white/60">
                            Here is what changes when you move up. One clear upgrade path, one checkout button, and a better summary of what you are actually getting.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-5 min-w-[240px]">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/40">Upgrade Price</p>
                        <p class="mt-2 text-2xl font-bold text-white">{{ $nextPlan['price'] }}</p>
                        <p class="mt-2 text-sm text-white/55">{{ $nextPlan['description'] ?: 'More room, better tools, and a fuller Lumina experience.' }}</p>
                        <a href="{{ $nextPlan['checkout_url'] }}"
                            class="mt-5 inline-flex w-full items-center justify-center rounded-xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                            Upgrade to {{ $nextPlan['key'] === 'pro' ? 'Pro' : $nextPlan['name'] }}
                        </a>
                    </div>
                </div>

                <div class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($featureCards as $feature)
                        <div class="flex items-start gap-3 rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-4">
                            <x-icon name="badge-check" class="mt-0.5 h-4 w-4 text-cyan-300" />
                            <span class="text-sm text-white/70">{{ $feature }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="grid gap-3 md:grid-cols-3">
                    @foreach($featureCards as $feature)
                        <div class="rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-4">
                            <div class="flex items-start gap-3">
                                <x-icon name="badge-check" class="mt-0.5 h-4 w-4 text-cyan-300" />
                                <span class="text-sm text-white/70">{{ $feature }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
