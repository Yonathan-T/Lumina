{{-- If you look to others for fulfillment, you will never truly be fulfilled. --}}
<div>
    <div>
        <h1 class="text-3xl font-bold tracking-tight">History</h1>
        <p class="text-muted">Browse and search through your past journal entries</p>
    </div>
    <div class="mt-6 flex flex-col gap-4 sm:flex-row">
        <div class="relative flex-1">
            <x-search-trigger />
            <x-search-modal />
        </div>
        <div class="relative w-44 ml-auto">
            <select wire:model.live="sort" class="flex h-10 w-full rounded-md border border-white/15 shadow-sm bg-background px-3 py-2 text-sm
                text-white">
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="longest">Longest First</option>
                <option value="shortest">Shortest First</option>
            </select>

            <!--HERE, I NEED TO MAKE A BETTER UI FOR THE SELECT ITEM!!!
         
         SO HERE GOES A BIG COMMENT SECTION!!!
         -->
        </div>
    </div>
    <div class="mt-8">
        <h2 class="text-xl font-semibold mb-4">All entries</h2>
        @foreach($recentEntries as $entry)
            <a href="{{ route('entries.show', $entry) }}" class="block">
                <div
                    class="flex items-stretch rounded-lg card-highlight  shadow-md  bg-gradient-dark border border-white/10 overflow-hidden mb-4">
                    <!-- Date Square -->
                    <div class="flex flex-col justify-center items-center 
                                bg-gradient-dark  border border-white/10 
                                w-20 min-w-30 h-30 
                                rounded-xl shadow-lg 
                                m-4 card-highlight
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
                            <div class="text-sm text-muted">
                                {{ $entry->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <p class="line-clamp-3 mb-2"> {!! nl2br(e($entry->content)) !!}</p>
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
            </a>
        @endforeach
    </div>
    <div class="mt-6">

        {{ $recentEntries->links() }}

    </div>

</div>
<script>

    document.addEventListener('DOMContentLoaded', () => {
        new SearchModal('/search');
    });
</script>