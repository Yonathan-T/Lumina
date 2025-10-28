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
        @if($recentEntries->count() > 0)
            @foreach($recentEntries as $entry)
                <a href="{{ route('entries.show', $entry) }}" class="block">
                    <div
                        class="flex items-stretch rounded-lg card-highlight  shadow-md  bg-gradient-dark border border-white/10 overflow-hidden mb-4">
                        <!-- Date Square -->
                        <div
                            class="flex flex-col justify-center items-center 
                                                                                                                    bg-gradient-dark  border border-white/10 
                                                                                                                    w-20 min-w-30 h-30 
                                                                                                                    rounded-xl shadow-lg 
                                                                                                                    m-4 card-highlight
                                                                                                                    self-center">
                            <span class="text-lg font-bold">{{ strtoupper($entry->date_month) }}</span>
                            <span class="text-2xl font-extrabold leading-none">{{ $entry->date_day }}</span>
                        </div>
                        <!-- Content -->
                        <div class="flex-1 flex flex-col justify-between p-6">
                            <div class="flex items-center justify-between mb-2">
                                <div class="justify-start">
                                    <h3 class="text-2xl font-semibold leading-none tracking-tight">{{ $entry->title }}</h3>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button onclick="event.preventDefault(); event.stopPropagation(); "
                                        wire:click="Reflect({{ $entry->id }})" @disabled($isProcessing === 'reflection' && $processingEntryId === $entry->id)
                                        class="cursor-pointer text-gray-400 hover:text-blue-400 hover:bg-blue-500/10 px-2 py-1 rounded text-sm flex items-center gap-1 transition-colors disabled:opacity-50">
                                        @if($isProcessing === 'reflection' && $processingEntryId === $entry->id)
                                            <x-icon name="rotate-ccw" class="h-4 w-4 animate-spin" />
                                            Starting...
                                        @else
                                            <x-icon name="stethoscope" class="h-4 w-4" />
                                            Reflect
                                        @endif
                                    </button>

                                    <div class="text-sm text-muted whitespace-nowrap">
                                        {{ $entry->diff }}
                                    </div>
                                </div>
                            </div>
                            {{-- <p class="line-clamp-3 mb-2"> {!! nl2br(e($entry->content)) !!}</p> --}}
                            <p class="line-clamp-3 mb-2">{!! $entry->content_html !!}</p>
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
                <p class="text-gray-500 dark:text-gray-400 mb-6">Start your journaling journey by creating your first entry
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
<script>

    document.addEventListener('DOMContentLoaded', () => {
        new SearchModal('/search');
    });
</script>