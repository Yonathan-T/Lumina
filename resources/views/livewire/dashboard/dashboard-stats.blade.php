<div wire:poll.30s>


    <div class="flex flex-col gap-4 sm:flex-row">
        <div class="relative flex-1">
            <h1 class="font-inter text-3xl font-bold tracking-tight">Hey, {{ auth()->user()->name }}</h1>
            <p class="text-muted font-inter">Welcome back to your journal. How are you feeling today?</p>
        </div>
        <div class="relative ml-auto mr-10" x-data="{ open: @entangle('isModalOpen') }" @click.outside="open = false">
            <button type="button" @click="open = ! open"
                class="cursor-pointer p-2 rounded-full hover:bg-white/10 transition-colors">
                <x-icon name="bell" class="w-8 w-8" />
                @if($unreadCount > 0)
                    <span class="absolute top-1 right-1 h-2 w-2 rounded-full bg-red-500"></span>
                @endif
            </button>

            <!-- Backdrop Overlay -->
            <div x-show="open" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/20 backdrop-blur-sm z-40"
                @click="open = false">
            </div>

            <!-- Notification Modal -->
            <div x-show="open" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                class="absolute right-0 mt-2 w-80 bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border border-gray-200/50 dark:border-gray-700/50 rounded-xl shadow-2xl z-50 origin-top-right"
                style="backdrop-filter: blur(12px);">

                <!-- Header -->
                <div
                    class="p-4 border-b border-gray-100/50 dark:border-gray-800/50 font-semibold flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <x-icon name="bell" class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                        <span class="text-gray-900 dark:text-gray-100">Notifications</span>
                        @if($unreadCount > 0)
                            <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $unreadCount }}</span>
                        @endif
                    </div>
                    @if ($unreadCount > 0)
                        <button wire:click="markAllAsRead"
                            class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                            Mark all as read
                        </button>
                    @endif
                </div>

                <!-- Notifications List -->
                <div class="bg-gradient-dark max-h-60 overflow-y-auto">
                    @forelse($notifications as $notification)
                        <div
                            class="px-4 py-3 hover:bg-blue-300/15 cursor-pointer transition-colors border-b border-gray-100/30 dark:border-gray-800/30 last:border-b-0 @if(!$notification->read_at) bg-blue-50/80 dark:bg-blue-900/20 @endif">
                            <div class="flex justify-between items-start gap-3">
                                <a href="{{ $notification->data['url'] ?? '#' }}"
                                    wire:click="markAsRead('{{ $notification->id }}')" class="flex-1 min-w-0">
                                    <div class="text-sm text-gray-900 dark:text-gray-100 leading-relaxed">
                                        {{ $notification->data['message'] ?? 'New notification' }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </div>
                                </a>
                                <button wire:click="deleteNotification('{{ $notification->id }}')"
                                    class="text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors p-1 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-8 text-center">
                            <div class="text-gray-400 dark:text-gray-500 mb-2">
                                <x-icon name="bell" class="w-8 h-8 mx-auto opacity-50" />
                            </div>
                            <div class="text-gray-500 dark:text-gray-400 text-sm">No notifications yet</div>
                        </div>
                    @endforelse
                </div>

                <!-- Footer -->
                @if($notifications->count() > 0)
                    <div class="p-4 border-t border-gray-100/50 dark:border-gray-800/50">
                        <button wire:click="deleteAllNotifications"
                            class="w-full text-center text-red-500 hover:text-red-700 dark:hover:text-red-400 transition-colors text-sm font-medium py-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">
                            Clear all notifications
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>





    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mt-5">
        <div
            class="rounded-lg text-card-foreground shadow-2xs card-highlight border bg-gradient-to-br from-blue-500/5 to-blue-600/5 border-blue-500/20">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Total Memos</h3>
                <x-icon name="book-outline" class="w-4 h-4 text-blue-500" />
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold">{{ $totalEntries }}</div>
                <p class="text-xs text-muted font-inter">
                    <!--there is a bug here 👀-->
                    @if($entriesFromLastWeek > 0)
                        +{{ $entriesFromLastWeek }} from last week
                    @elseif($entriesFromLastWeek < 0)
                        {{ abs($entriesFromLastWeek) }} less than last week
                    @else
                        Same as last week
                    @endif

                </p>
            </div>
        </div>
        <div
            class="rounded-lg border border-white/15 text-card-foreground shadow-sm card-highlight bg-gradient-to-br from-green-500/10 to-green-600/10 ">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Most Used Tag</h3>
                <x-icon name="tag" class="w-4 h-4 text-green-500" />
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold">{{ $mostUsedTag ? " # " . $mostUsedTag->name : 'No tags' }}</div>
                <p class="text-xs text-muted font-inter">Used in {{ $mostUsedTagCount }} entries</p>
            </div>
        </div>



        <div
            class="rounded-lg border border-white/15 text-card-foreground shadow-sm card-highlight bg-gradient-to-br from-purple-500/10 to-purple-600/10 ">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Longest Entry</h3>
                <x-iconpark-writingfluently-o class="h-4 w-4 text-purple-500" />
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
        <div
            class="rounded-lg border border-white/15 text-card-foreground shadow-sm card-highlight bg-gradient-to-br from-yellow-500/10 to-yellow-600/10 ">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium">Daily Streak</h3>
                <x-icon name="flash-outline" class="w-4 h-4 text-yellow-500" />
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold">{{ $currentStreak }}</div>
                <p class="text-xs text-muted font-inter">{{ $streakMessage }}</p>
            </div>
        </div>
    </div>
    <!-- Quick Start Section goes here baby -->

    <!-- AI-Powered Quick Actions -->
    @php
        $isProcessing = [
            'guided-reflection',
            'weekly-summary',
            'audio-summary',
            'review-memos'
        ];  
     @endphp
    <div class="mt-12 space-y-4">
        <div class="text-center space-y-2">
            <h2 class="text-2xl font-semibold flex items-center justify-center gap-2">
                <x-icon name="sparkles" class="h-6 w-6 text-blue-500" />
                AI-Powered Insights
            </h2>
            <p class="text-muted">Let AI help you explore your thoughts and patterns</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 sm:grid-cols-1">
            <!-- Guided Reflection Card -->
            <div class="card-highlight border cursor-pointer transition-all duration-200 hover:scale-[1.02] bg-blue-500/10 hover:bg-blue-500/20 border-blue-500/20 rounded-lg"
                wire:click="startGuidedReflection">
                <div class="p-4 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-gradient-dark">
                            <x-icon name="chatbubbles-outline" class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">Start Guided Reflection</h3>
                            <p class="text-sm text-muted-foreground">AI-guided self-reflection session</p>
                        </div>
                    </div>
                </div>
                <div class="px-4 pb-4 pt-0">
                    @if($isProcessing === 'guided-reflection')
                        <div class="flex items-center gap-2 text-sm text-blue-500">
                            <div class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin">
                            </div>
                            Processing...
                        </div>
                    @endif
                </div>
            </div>

            <!-- Weekly Summary Card -->
            <div class="card-highlight border cursor-pointer transition-all duration-200 hover:scale-[1.02] bg-green-500/10 hover:bg-green-500/20 border-green-500/20 rounded-lg"
                wire:click="summarizePastWeek">
                <div class="p-4 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-gradient-dark">
                            <x-icon name="wand-sparkles" class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">Summarize Past Week</h3>
                            <p class="text-sm text-muted-foreground">Get insights from your recent entries</p>
                        </div>
                    </div>
                </div>
                <div class="px-4 pb-4 pt-0">
                    @if($isProcessing === 'weekly-summary')
                        <div class="flex items-center gap-2 text-sm text-green-500">
                            <div class="w-4 h-4 border-2 border-green-500 border-t-transparent rounded-full animate-spin">
                            </div>
                            Processing...
                        </div>
                    @endif
                </div>
            </div>

            <!-- Audio Summary Card -->
            <div class="card-highlight border cursor-pointer transition-all duration-200 hover:scale-[1.02] bg-purple-500/10 hover:bg-purple-500/20 border-purple-500/20 rounded-lg"
                wire:click="generateAudioSummary">
                <div class="p-4 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-gradient-dark">
                            <x-icon name="file-audio" class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">Generate Audio Summary</h3>
                            <p class="text-sm text-muted-foreground">Listen to your journal insights</p>
                        </div>
                    </div>
                </div>
                <div class="px-4 pb-4 pt-0">
                    @if($isProcessing === 'audio-summary')
                        <div class="flex items-center gap-2 text-sm text-purple-500">
                            <div class="w-4 h-4 border-2 border-purple-500 border-t-transparent rounded-full animate-spin">
                            </div>
                            Processing...
                        </div>
                    @endif
                </div>
            </div>

            <!-- Review Memos Card -->
            <div class="card-highlight border cursor-pointer transition-all duration-200 hover:scale-[1.02] bg-yellow-500/10 hover:bg-yellow-500/20 border-yellow-500/20 rounded-lg"
                wire:click="reviewPastMemos">
                <div class="p-4 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-gradient-dark">
                            <x-icon name="scroll-text" class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">Review Past Memos</h3>
                            <p class="text-sm text-muted-foreground">Analyze patterns in your writing</p>
                        </div>
                    </div>
                </div>
                <div class="px-4 pb-4 pt-0">
                    @if($isProcessing === 'review-memos')
                        <div class="flex items-center gap-2 text-sm text-yellow-500">
                            <div class="w-4 h-4 border-2 border-yellow-500 border-t-transparent rounded-full animate-spin">
                            </div>
                            Processing...
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Therapy Chat CTA -->
    <!-- Therapy Chat CTA with Livewire -->
    <div class="mt-12 card-highlight border border-blue-500/20 bg-blue-500/15 light-gradient-card rounded-lg">
        <div class="p-6">
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-full bg-blue-500/20">
                    <x-icon name="brain" class="h-6 w-6 text-blue-500" />
                </div>
                <div>
                    <h3 class="text-xl font-semibold">Ready for a deeper conversation?</h3>
                    <p class="text-sm text-muted">
                        I've analyzed your recent entries and noticed some interesting patterns. Let's explore them
                        together.
                    </p>
                </div>
            </div>
        </div>
        <div class="px-6 pb-6">
            <button wire:click="startTherapySession"
                class="cursor-pointer inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors bg-blue-600 hover:bg-blue-700 text-white h-10 px-4 py-2">
                <x-icon name="chatbubbles-outline" class="mr-2 h-4 w-4" />
                Start Therapy Session
            </button>
        </div>
    </div>
    <!-- Recent section goes here -->
    @if($recentEntries && $recentEntries->count() > 0)
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
    @endif
</div>