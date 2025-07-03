{{-- If you look to others for fulfillment, you will never truly be fulfilled. --}}
<div>
    <div>
        <h1 class="text-3xl font-bold tracking-tight">History</h1>
        <p class="text-muted">Browse and search through your past journal entries</p>
    </div>
    <div class="mt-6 flex flex-col gap-4 sm:flex-row">
        <div class="relative flex-1">
            <x-icon name="search" class="absolute left-2 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted" />
            <input
                class="flex h-10 w-full rounded-md border border-white/15 shadow-sm bg-background px-3 py-2 pl-8 text-sm  placeholder:text-muted focus-visible:ring-0 focus-visible:ring-offset-0 disabled:cursor-not-allowed disabled:opacity-50"
                placeholder="Search entries..." type="search">
        </div>

        <button type="button" role="combobox" aria-controls="radix-«r9»" aria-expanded="false" aria-autocomplete="none"
            dir="ltr" data-state="closed" class="flex h-10 items-center justify-between rounded-md border border-white/15 shadow-sm bg-background px-3 py-2 text-sm placeholder:text-muted focus-visible:ring-0 focus-visible:ring-offset-0
 disabled:cursor-not-allowed disabled:opacity-50 [&>span]:line-clamp-1 w-full sm:w-[180px]">
            <span style="pointer-events: none;">All Tags</span>
            <x-icon name="chevron-down" class="w-5 h-5 text-muted" />
        </button>
        <button type="button" role="combobox" aria-controls="radix-«r9»" aria-expanded="false" aria-autocomplete="none"
            dir="ltr" data-state="closed" class="flex h-10 items-center justify-between rounded-md border border-white/15 shadow-sm bg-background px-3 py-2 text-sm placeholder:text-muted focus-visible:ring-0 focus-visible:ring-offset-0
 disabled:cursor-not-allowed disabled:opacity-50 [&>span]:line-clamp-1 w-full sm:w-[180px]">
            <span style="pointer-events: none;">All Tags</span>
            <x-icon name="chevron-down" class="w-5 h-5 text-muted" />
        </button>
    </div>

    <div class="mt-8">
        <h2 class="text-xl font-semibold mb-4">Recent Entry</h2>
        @foreach($recentEntries as $entry)
            <div
                class="flex items-stretch rounded-lg bg-card text-card-foreground shadow-md card-highlight bg-gradient-dark border border-white/10 overflow-hidden mb-4">
                <!-- Date Square -->
                <!-- Date Square -->
                <div class="flex flex-col justify-center items-center 
                                            bg-gradient-dark border border-white/15 text-secondary-foreground 
                                            w-20 min-w-30 h-30 
                                            rounded-xl shadow-lg 
                                            m-4
                                            self-center">
                    <span class="text-lg font-bold">
                        {{ strtoupper($entry->created_at->format('M')) }}
                    </span>
                    <span class="text-2xl font-extrabold leading-none">
                        {{ $entry->created_at->format('d') }}
                    </span>
                </div>
                <!-- Content -->
                <div class="flex-1 flex flex-col justify-between p-6">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-2xl font-semibold leading-none tracking-tight">{{ $entry->title }}</h3>
                        <div class="text-sm text-muted-foreground">{{ $entry->created_at->diffForHumans() }}</div>
                    </div>
                    <p class="line-clamp-3 mb-2">{{ $entry->content }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($entry->tags as $tag)
                            <div
                                class="inline-flex items-center rounded-full quick-start-btn border px-2.5 py-0.5 text-xs font-semibold transition-colors border-transparent bg-secondary text-secondary-foreground hover:/80">
                                #{{ $tag->name }}
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-6">

        {{ $recentEntries->links('vendor.pagination.tailwind') }}

    </div>

</div>