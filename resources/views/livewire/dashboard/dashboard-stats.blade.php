<div>
    <h1 class="font-inter text-3xl  font-bold tracking-tight">Hey, {{ auth()->user()->name }}</h1>
    <p class="text-muted font-inter ">Welcome back to your journal. How are you feeling today?</p>
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mt-5">
        <div class="rounded-lg border border-white/15 text-card-foreground shadow-sm card-highlight bg-gradient-dark ">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Total Memos</h3>
                <x-icon name="book-outline" class="w-4 h-4" />
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold">{{ $totalEntries }}</div>
                <p class="text-xs text-muted font-inter">
                    @if($entriesFromLastWeek > 0)
                        +{{ $entriesFromLastWeek }} from last week
                    @elseif($entriesFromLastWeek < 0)
                        {{ $entriesFromLastWeek }} from last week
                    @else
                        Same as last week
                    @endif
                </p>
            </div>
        </div>
        <div class="rounded-lg border border-white/15 text-card-foreground shadow-sm card-highlight bg-gradient-dark ">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Most Used Tag</h3>
                <x-icon name="tag" class="w-4 h-4" />
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold">{{ $mostUsedTag ? " # " . $mostUsedTag->name : 'No tags' }}</div>
                <p class="text-xs text-muted font-inter">Used in {{ $mostUsedTagCount }} entries</p>
            </div>
        </div>
        <div class="rounded-lg border border-white/15 text-card-foreground shadow-sm card-highlight bg-gradient-dark ">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Longest Entry</h3>
                <x-iconpark-writingfluently-o class="h-4 w-4" />
            </div>
            <div class="p-6 pt-0">
                @if(isset($longestEntryCharCount) && isset($longestEntryDate))
                    <div class="text-2xl font-bold">{{ $longestEntryCharCount }}</div>
                    <p class="text-xs text-muted font-inter">
                        characters on {{ $longestEntryDate }}
                    </p>
                @else
                    <div class="text-2xl font-bold">0</div>
                    <p class="text-xs text-muted font-inter">No entries</p>
                @endif
            </div>

        </div>
        <div class="rounded-lg border border-white/15 text-card-foreground shadow-sm card-highlight bg-gradient-dark ">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Daily Streak</h3>
                <x-icon name="flash-outline" class="w-4 h-4" />
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold">{{ $currentStreak }}</div>
                <p class="text-xs text-muted font-inter">{{ $streakMessage }}</p>
            </div>
        </div>
    </div>
    <!-- Quick Start Section goes here baby -->
    <div class="mt-8">
        <h2 class="text-xl font-semibold mb-4">Quick Start</h2>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
            <button wire:click="startGuidedReflection"
                class="inline-flex items-center border border-white/5 justify-center rounded-md text-sm font-medium transition-colors quick-start-btn hover:quick-start-btn-hover h-auto flex-col gap-2 p-4 disabled:opacity-50">
                <x-icon name="chatbubbles-outline" class="w-4 h-4" />
                <span>Start Guided Reflection</span>
            </button>
            <button wire:click="summarizePastWeek"
                class="inline-flex items-center border border-white/5 justify-center rounded-md text-sm font-medium transition-colors quick-start-btn hover:quick-start-btn-hover h-auto flex-col gap-2 p-4 disabled:opacity-50">
                <x-icon name="wand-sparkles" class="w-4 h-4" />
                <span>Summarize My Past Week</span>
            </button>
            <button wire:click="convertToPoem"
                class="inline-flex items-center border border-white/5 justify-center rounded-md text-sm font-medium transition-colors quick-start-btn hover:quick-start-btn-hover h-auto flex-col gap-2 p-4 disabled:opacity-50">
                <x-icon name="newspaper-outline" class="w-4 h-4" />
                <span>Convert Entry to Poem</span>
            </button>
            <button wire:click="generateAudioSummary"
                class="inline-flex items-center border border-white/5 justify-center rounded-md text-sm font-medium transition-colors quick-start-btn hover:quick-start-btn-hover h-auto flex-col gap-2 p-4 disabled:opacity-50">
                <x-icon name="file-audio" class="w-4 h-4" />
                <span>Generate Audio Summary</span>
            </button>
            <button wire:click="reviewPastMemos"
                class="inline-flex items-center border border-white/5 justify-center rounded-md text-sm font-medium transition-colors quick-start-btn hover:quick-start-btn-hover h-auto flex-col gap-2 p-4 disabled:opacity-50">
                <x-icon name="sparkles" class="w-4 h-4" />
                <span>Review Past Memos</span>
            </button>
        </div>
    </div>
    <!-- Recent section goes here -->
    <div class="mt-8">
        <h2 class="text-xl font-semibold mb-4">Recent Entry</h2>
        @foreach($recentEntries as $entry)
            <a href="{{ route('entries.show', $entry) }}">
                <div
                    class="rounded-lg bg-card text-card-foreground shadow-md card-highlight bg-gradient-dark border border-white/10">
                    <div class="flex flex-col space-y-1.5 p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-2xl font-semibold leading-none tracking-tight">{{ $entry->title }}</h3>
                            <div class="text-sm text-muted-foreground">{{ $entry->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    <div class="p-6 pt-0">
                        <p class="line-clamp-3">{{ $entry->content }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($entry->tags as $tag)
                                <div
                                    class="inline-flex items-center rounded-full quick-start-btn border px-2.5 py-0.5 text-xs font-semibold transition-colors  border-transparent bg-secondary text-secondary-foreground hover:/80">
                                    #{{ $tag->name }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>