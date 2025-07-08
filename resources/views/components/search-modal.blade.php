<div id="searchModal"
    class="fixed inset-0 z-50 hidden items-start justify-center pt-[10vh] bg-black/50 backdrop-blur-sm">
    <div class="w-full max-w-2xl mx-4 bg-card/95 backdrop-blur-sm rounded-lg shadow-2xl border border-accent/20">
        <!-- Header with Search Input -->
        <div class="p-4 pb-3">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" id="searchInput" placeholder="{{ $placeholder ?? 'Search entries...' }}"
                    class="w-full pl-10 pr-4 h-12 text-lg bg-transparent border-none focus:ring-0 focus:outline-none text-foreground placeholder-muted-foreground"
                    autocomplete="off">
            </div>
        </div>

        <!-- Results Container -->
        <div class="px-4 pb-4 max-h-96 overflow-y-auto">
            <!-- Empty State -->
            <div id="emptyState" class="text-center py-8 text-muted-foreground">
                <x-icon name="search" class="h-8 w-8 mx-auto mb-2 opacity-50 text-muted" />
                <p>Start typing to search...</p>
                <p class="text-sm mt-1">
                    Press <kbd class="px-2 py-1 bg-muted rounded text-xs">Esc</kbd> to close
                </p>
            </div>

            <!-- No Results -->
            <div id="noResults" class="hidden text-center py-8 text-muted-foreground">
                <p>{{ $emptyMessage ?? 'No results found' }}</p>
            </div>

            <!-- voilà -->
            <div id="searchResults" class="hidden space-y-2"></div>
        </div>
    </div>
</div>