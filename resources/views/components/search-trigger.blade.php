<button id="searchTrigger" type="button"
    class="relative flex items-center justify-between w-full md:w-40 lg:w-64 rounded-md border border-white/10 bg-gradient-dark px-4 py-2 text-sm text-muted  hover:text-white focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">

    <div class="flex items-center gap-2">
        <x-icon name="search" class="w-4 h-4 text-muted" />
        <span class="hidden lg:inline text-muted">Search...</span>
        <span class="inline lg:hidden">Search</span>
    </div>

    <div class="ml-auto pl-2">
        <kbd class="flex items-center gap-1 rounded bg-white/10 px-2 py-1 font-mono text-xs font-medium">
            <x-icon name="command" class="w-3 h-3 text-muted" />
            <span class="text-muted">K</span>
        </kbd>
    </div>
</button>