{{-- If you look to others for fulfillment, you will never truly be fulfilled. --}}
<div class="p-6" id="mainContent">


    <div>
        <h1 class="text-3xl font-bold tracking-tight">History</h1>
        <p class="text-muted">Browse and search through your past journal entries</p>
    </div>
    <div class="mt-6 flex flex-col gap-4 sm:flex-row">
        <div class="relative flex-1">
            <x-search-trigger />
            <x-search-modal />
        </div>
        <div class="relative w-48 ml-auto flex items-center">
            <select wire:model.change="sort" class="flex h-10 w-full rounded-md border border-white/15 shadow-sm bg-background px-3 py-2 text-sm text-white focus:border-blue-400 focus:outline-none cursor-pointer">
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="longest">Longest First</option>
                <option value="shortest">Shortest First</option>
            </select>
            <div wire:loading wire:target="sort" class="absolute -left-7 flex items-center justify-center">
                <div class="h-4 w-4 animate-spin rounded-full border-2 border-blue-400 border-t-transparent"></div>
            </div>
        </div>
    </div>
    <div class="mt-8">
        <div class="{{ \App\Helpers\FontHelper::getFontClass() }}" data-font-bind data-font-size-bind style="font-size: {{ \App\Helpers\FontHelper::getFontSize() }}px;">

            <h2 class="text-xl font-semibold mb-4">All entries</h2>
            <div wire:loading.class="opacity-60 pointer-events-none transition-opacity duration-200" wire:target="sort">
            @if($recentEntries->count() > 0)
                @foreach($recentEntries as $entry)
                    <div class="relative group rounded-lg card-highlight shadow-md bg-gradient-dark border border-white/10 hover:border-white/20 transition-all overflow-hidden mb-4 cursor-pointer">
                        <a href="{{ route('entries.show', $entry) }}" wire:navigate.hover class="!absolute inset-0 !z-10 cursor-pointer block" aria-label="View entry {{ $entry->title }}"></a>
                        <div
                            class="flex items-stretch pointer-events-none">
                            <!-- Date Square (Desktop only) -->
                            <div
                                class="hidden md:flex flex-col justify-center items-center 
                                    bg-gradient-dark border border-white/10 
                                    w-20 min-w-30 h-30 
                                    rounded-xl shadow-lg 
                                    m-4 card-highlight
                                    self-center shrink-0">
                                <span class="text-lg font-bold">{{ strtoupper($entry->date_month) }}</span>
                                <span class="text-2xl font-extrabold leading-none">{{ $entry->date_day }}</span>
                            </div>
                            <!-- Content -->
                            <div class="flex-1 min-w-0 flex flex-col justify-between p-4 sm:p-6">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
                                    <div class="min-w-0 pointer-events-auto relative !z-20 flex-1">
                                        <a href="{{ route('entries.show', $entry) }}" wire:navigate.hover class="hover:text-blue-400 transition-colors">
                                            <h3 class="text-xl sm:text-2xl font-semibold leading-snug tracking-tight text-white line-clamp-2 sm:truncate">{{ $entry->title }}</h3>
                                        </a>
                                        <!-- Mobile Date & Relative Time -->
                                        <div class="md:hidden mt-1 flex items-center gap-1.5 text-xs text-muted">
                                            <span>{{ $entry->date_formatted ?? ($entry->date_month . ' ' . $entry->date_day) }}</span>
                                            <span>•</span>
                                            <span>{{ $entry->diff }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between sm:justify-end gap-2.5 pointer-events-auto relative !z-20 shrink-0 pt-1 sm:pt-0">
                                        <button type="button"
                                            wire:click.stop="Reflect({{ $entry->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="Reflect({{ $entry->id }})"
                                            class="cursor-pointer text-blue-400 hover:text-blue-300 bg-blue-500/10 hover:bg-blue-500/20 border border-blue-500/20 px-2.5 py-1 rounded-md text-xs sm:text-sm font-medium inline-flex items-center gap-1.5 whitespace-nowrap transition-colors disabled:opacity-50">
                                            <x-icon wire:loading.remove wire:target="Reflect({{ $entry->id }})" name="stethoscope" class="h-3.5 w-3.5 sm:h-4 sm:w-4 shrink-0" />
                                            <svg wire:loading wire:target="Reflect({{ $entry->id }})" class="animate-spin h-3.5 w-3.5 sm:h-4 sm:w-4 text-blue-400 shrink-0" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                            </svg>
                                            <span>Reflect</span>
                                        </button>

                                        <div class="hidden md:block text-sm text-muted whitespace-nowrap">
                                            {{ $entry->diff }}
                                        </div>
                                    </div>
                                </div>
                                <p class="line-clamp-3 text-sm sm:text-base text-gray-300 mb-2">{!! $entry->content_html !!}</p>
                                <div class="mt-2 flex flex-wrap gap-2 pointer-events-auto relative !z-20">
                                    @foreach($entry->tags as $tag)
                                        <div
                                            class="inline-flex items-center rounded-full quick-start-btn border px-2.5 py-0.5 text-xs font-semibold transition-colors border-transparent bg-secondary text-secondary-foreground hover:/80">
                                            #{{ $tag->name }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="mt-6">
                    {{ $recentEntries->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div
                        class="mx-auto w-24 h-24 mb-6 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                        <x-icon name="book-outline" class="w-12 h-12 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No entries yet</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Start your journaling journey by creating your first
                        entry
                    </p>
                    <a href="{{ route('entries.create') }}"
                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors  border border-white/10 bg-background backdrop-blur-sm hover:bg-white/5 transition duration-200 hover:text-accent h-10 px-4 py-2">
                        <x-icon name="door-open" class="w-4 h-4 mr-2" />
                        Create your first entry
                    </a>
                </div>
            @endif
            </div>
        </div>
    </div>

</div>
