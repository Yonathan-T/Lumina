<div>
    {{-- If your happiness depends on money, you will never be happy with yourself. --}}
    {{-- Floating notification toast --}}
    <div class="fixed top-6 right-6 z-50 flex flex-col gap-3 max-w-md w-full px-4 sm:px-0 pointer-events-none">
        @if (session('message'))
            <div id="success-message"
                x-data="{
                    show: true,
                    timeout: null,
                    startTimer() {
                        this.clearTimer();
                        this.timeout = setTimeout(() => { this.show = false; }, 4000);
                    },
                    clearTimer() {
                        if (this.timeout) {
                            clearTimeout(this.timeout);
                            this.timeout = null;
                        }
                    }
                }"
                x-init="startTimer()"
                @mouseenter="clearTimer()"
                @mouseleave="startTimer()"
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-2"
                class="pointer-events-auto bg-[#0a1813]/95 border border-emerald-500/40 rounded-xl px-5 py-4 shadow-2xl shadow-emerald-950/50 backdrop-blur-md transition-all duration-300">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center flex-shrink-0 text-emerald-400 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-emerald-100 leading-snug">{{ session('message') }}</span>
                    </div>
                    <button @click="show = false"
                        class="text-gray-400 hover:text-white transition-colors p-1 rounded-md hover:bg-white/10 flex-shrink-0 cursor-pointer"
                        aria-label="Dismiss notification">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div id="error-message"
                x-data="{
                    show: true,
                    timeout: null,
                    startTimer() {
                        this.clearTimer();
                        this.timeout = setTimeout(() => { this.show = false; }, 6000);
                    },
                    clearTimer() {
                        if (this.timeout) {
                            clearTimeout(this.timeout);
                            this.timeout = null;
                        }
                    }
                }"
                x-init="startTimer()"
                @mouseenter="clearTimer()"
                @mouseleave="startTimer()"
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-2"
                class="pointer-events-auto bg-[#1b0d13]/95 border border-red-500/30 rounded-xl px-5 py-4 shadow-2xl backdrop-blur-md transition-all duration-300">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center flex-shrink-0 text-red-400 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-medium text-white leading-snug">{{ session('error') }}</span>
                            @if(str_contains(session('error'), 'Gemini API key'))
                                <a href="{{ route('settings.index', ['tab' => 'account']) }}" wire:navigate
                                    class="text-xs text-blue-400 hover:text-blue-300 underline font-medium mt-0.5">
                                    Configure key in Settings &rarr;
                                </a>
                            @endif
                        </div>
                    </div>
                    <button @click="show = false"
                        class="text-gray-400 hover:text-white transition-colors p-1 rounded-md hover:bg-white/10 flex-shrink-0 cursor-pointer"
                        aria-label="Dismiss notification">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif
    </div>

    <div>
        <h1 class="text-3xl font-bold tracking-tight">Tags</h1>
        <p class="text-muted">Browse, organize and search your journal entries by tags</p>
    </div>
    <div class="mt-6 flex flex-col gap-4 sm:flex-row">
        <div class="relative flex-1">
            <x-search-trigger />
            <x-search-modal />
        </div>
        <div class="relative w-44 ml-auto">
            <select wire:model.live="sort" class="flex h-10 w-full rounded-md border border-white/15 shadow-sm bg-background px-3 py-2 text-sm
                text-white">
                <option value="most">Most Used</option>
                <option value="recent">Recently Used</option>
                <option value="alphabetic">Alphabetical</option>
            </select>

            <!--HERE, I NEED TO MAKE A BETTER UI FOR THE SELECT ITEM!!!
         
         SO HERE GOES A BIG COMMENT SECTION!!!
         -->
        </div>
    </div>
    <div class="w-full max-w-6xl mx-auto pt-8">
        @if($tagList->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"
                wire:loading.class="opacity-60 transition-opacity"
                wire:target="sort">
                @foreach ($tagList as $tag)
                    <button wire:click="showTagEntries({{ $tag->id }})"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-60 cursor-wait"
                        wire:target="showTagEntries"
                        @class([
                            // Always apply these:
                            'card-highlight inline-flex items-center gap-2 rounded-md text-sm font-medium transition-colors border-white/5 border h-10 px-4 py-2 justify-between w-full focus-visible:outline-hidden focus-visible:ring-ring ring-offset-background focus-visible:ring-2 focus-visible:ring-offset-2',
                            // Default background:
                            'bg-gradient-dark text-white hover:bg-gray-800' => $selectedTagId !== $tag->id,
                            // Selected tag:
                            'bg-white text-[rgb(15,23,42)]' => $selectedTagId === $tag->id,
                        ])>
                        <span class="inline-flex items-center gap-1.5 font-medium truncate">
                            <svg wire:loading wire:target="showTagEntries({{ $tag->id }})" class="animate-spin -ml-0.5 h-3.5 w-3.5 text-current inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>#{{ $tag->name }}</span>
                        </span>
                        <span
                            class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-rgb(15,23,42)text-white font-semibold shrink-0">
                            {{ $tag->entries_count }}
                        </span>
                    </button>
                @endforeach
            </div>
            <div class="mt-5">
                {{ $tagList->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div
                    class="mx-auto w-24 h-24 mb-6 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                    <x-icon name="tag" class="w-12 h-12 text-gray-400" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No tags yet</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">Start creating entries with tags to organize your journal
                </p>
                <a href="{{ route('entries.create') }}"
                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors  border border-white/10 bg-background backdrop-blur-sm hover:bg-white/5 transition duration-200 hover:text-accent h-10 px-4 py-2">
                    <x-icon name="door-open" class="w-4 h-4 mr-2" />
                    Create your first entry
                </a>
            </div>
        @endif
    </div>

    {{-- Loading Feedback on Tag Selection --}}
    <div wire:loading wire:target="showTagEntries" class="w-full max-w-6xl mx-auto mt-6 flex items-center justify-center py-4">
        <svg class="animate-spin h-6 w-6 text-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
    </div>

    @if($selectedTagId)
        <div class="mt-8" wire:loading.class="opacity-50 pointer-events-none" wire:target="showTagEntries">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold">
                    Entries for #{{ $selectedTagName }}
                </h2>
                <div class="flex items-center gap-2">
                    <span wire:loading wire:target="showTagEntries" class="inline-flex items-center text-xs text-muted">
                        <svg class="animate-spin h-3.5 w-3.5 text-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </span>
                    <p class="text-sm text-muted">
                        {{ $tagEntries->count() }} entries
                    </p>
                </div>
            </div>
            @forelse($tagEntries as $entry)
                <div wire:key="tag-entry-{{ $entry->id }}" class="relative group mb-5">
                    {{-- Top-Right L-Shaped Morph Notch Housing the Reflect Action (Independent from card anchor) --}}
                    <div class="absolute top-0 right-0 z-30 flex items-center justify-end pl-4 sm:pl-5 pr-3 sm:pr-4 pt-2.5 sm:pt-3 pb-2.5 sm:pb-3 rounded-bl-2xl bg-[#080d19] border-b border-l border-white/10 group-hover:border-blue-500/40 group-hover:bg-[#0b1328] transition-all duration-300 shadow-md">
                        <button type="button"
                            wire:click.stop="Reflect({{ $entry->id }})"
                            wire:loading.attr="disabled"
                            wire:target="Reflect({{ $entry->id }})"
                            class="cursor-pointer text-blue-400 hover:text-white bg-blue-500/10 hover:bg-blue-600 border border-blue-500/20 hover:border-blue-400/80 px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold inline-flex items-center gap-1.5 whitespace-nowrap transition-all duration-200 group-hover:scale-105 group-hover:shadow-md group-hover:shadow-blue-500/25 disabled:opacity-50">
                            <x-icon wire:loading.remove wire:target="Reflect({{ $entry->id }})" name="stethoscope" class="h-3.5 w-3.5 sm:h-4 sm:w-4 shrink-0 transition-transform group-hover:rotate-12" />
                            <svg wire:loading wire:target="Reflect({{ $entry->id }})" class="animate-spin h-3.5 w-3.5 sm:h-4 sm:w-4 text-blue-400 shrink-0" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <span>Reflect</span>
                        </button>
                    </div>

                        {{-- Main Entry Card: Entire card is an anchor navigating directly to entries.show --}}
                        <a href="{{ route('entries.show', $entry) }}" wire:navigate.hover
                            class="block relative rounded-xl card-highlight shadow-md bg-gradient-dark border border-white/10 group-hover:border-white/25 transition-all duration-300 overflow-hidden cursor-pointer no-underline text-inherit focus:outline-none focus:ring-1 focus:ring-blue-500/50"
                            aria-label="View entry {{ $entry->title }}">
                            <div class="flex items-stretch">
                                <!-- Date Square (Desktop only) -->
                                <div
                                    class="hidden md:flex flex-col justify-center items-center 
                                        bg-gradient-dark border border-white/10 
                                        w-20 min-w-30 h-30 
                                        rounded-xl shadow-lg 
                                        m-4 card-highlight
                                        self-center shrink-0">
                                    <span class="text-lg font-bold">
                                        {{ strtoupper($entry->created_at->format('M')) }}
                                    </span>
                                    <span class="text-2xl font-extrabold leading-none">
                                        {{ $entry->created_at->format('d') }}
                                    </span>
                                </div>
                                <!-- Content -->
                                <div class="flex-1 min-w-0 flex flex-col justify-between p-4 sm:p-6 pr-28 sm:pr-36">
                                    <div class="mb-2.5">
                                        <div class="min-w-0">
                                            <h3 class="text-xl sm:text-2xl font-semibold leading-snug tracking-tight text-white line-clamp-2 sm:truncate group-hover:text-blue-400 transition-colors">{{ $entry->title }}</h3>
                                            <!-- Mobile & Desktop Date & Relative Time -->
                                            <div class="mt-1 flex items-center gap-1.5 text-xs text-muted">
                                                <span>{{ $entry->created_at->format('M d, Y') }}</span>
                                                <span>•</span>
                                                <span>{{ $entry->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="line-clamp-3 text-sm sm:text-base text-gray-300 mb-2"> {!! nl2br(e($entry->content)) !!}</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($entry->tags as $tag)
                                            <span wire:key="tag-entry-{{ $entry->id }}-tag-{{ $tag->id }}"
                                                class="inline-flex items-center rounded-full quick-start-btn border px-2.5 py-0.5 text-xs font-semibold transition-colors border-transparent bg-secondary text-secondary-foreground hover:/80">
                                                #{{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
            @empty
                <div class="text-muted">No entries found for this tag.</div>
            @endforelse
        </div>
    @endif
</div>