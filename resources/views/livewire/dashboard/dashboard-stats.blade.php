<div>
    <h1>Hello {{ auth()->user()->name }}</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Stats Card -->
        <div class="bg-[#1a1a1a] rounded-lg p-6 shadow-lg">
            <h3 class="text-lg font-semibold text-[#c3beb6]">Total Entries</h3>
            <p class="text-3xl font-bold text-[#c2b68e]">{{ $totalEntries }}</p>
        </div>

        <!-- Recent Entries -->
        <div class="col-span-2 bg-[#1a1a1a] rounded-lg p-6 shadow-lg">
            <h3 class="text-lg font-semibold text-[#c3beb6] mb-4">Recent Entries</h3>
            @if($loading)
                <div class="animate-pulse">
                    <div class="h-4 bg-[#2a2a2a] rounded w-3/4 mb-2"></div>
                    <div class="h-4 bg-[#2a2a2a] rounded w-1/2"></div>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($recentEntries as $entry)
                        <div class="border-b border-[#2a2a2a] pb-2">
                            <h4 class="text-[#c2b68e]">{{ $entry->title }}</h4>
                            <p class="text-sm text-gray-400">{{ $entry->created_at->diffForHumans() }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>