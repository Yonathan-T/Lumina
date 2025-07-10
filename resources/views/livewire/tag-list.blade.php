<div>
    {{-- If your happiness depends on money, you will never be happy with yourself. --}}
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
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach ($tagList as $tag)
                <button wire:click="showTagEntries({{ $tag->id }})" @class([
                    // Always apply these:
                    'card-highlight inline-flex items-center gap-2 rounded-md text-sm font-medium transition-colors border-white/5 border h-10 px-4 py-2 justify-between w-full focus-visible:outline-hidden focus-visible:ring-ring ring-offset-background focus-visible:ring-2 focus-visible:ring-offset-2',
                    // Default background:
                    'bg-gradient-dark text-white hover:bg-gray-800' => $selectedTagId !== $tag->id,
                    // Selected tag:
                    'bg-white text-[rgb(15,23,42)]' => $selectedTagId === $tag->id,
                ])>
                    <span class="font-medium">#{{ $tag->name }}</span>
                    <span
                        class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-rgb(15,23,42)text-white font-semibold">
                        {{ $tag->entries_count }}
                    </span>
                </button>
            @endforeach
        </div>
        <div class="mt-5">

            {{ $tagList->links() }}

        </div>
    </div>
    @if($selectedTagId)
        <div class="mt-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold">
                    Entries for #{{ $selectedTagName }}
                </h2>
                <p class="text-sm text-muted">
                    {{ $tagEntries->count() }} entries
                </p>
            </div>
            @forelse($tagEntries as $entry)
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
                                <div class="text-sm text-muted">{{ $entry->created_at->diffForHumans() }}</div>
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
            @empty
                <div class="text-muted">No entries found for this tag.</div>
            @endforelse
        </div>
    @endif
</div>