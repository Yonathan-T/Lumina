<div wire:poll.visible.60s="refresh">


    <div class="flex flex-col gap-4 sm:flex-row">
        <div class="relative flex-1">
            <h1 class="font-inter text-3xl font-bold tracking-tight">Hey, {{ auth()->user()->name }}</h1>
            <p class="text-muted font-inter">Welcome back to your journal. How are you feeling today?</p>
        </div>
        <div class="flex items-center gap-3 ml-auto mr-10">
            <!-- Ambient Music Toggle (Dashboard Only, Compact & Sidebar-Themed) -->
            <x-skiper25-music-button />

            <!-- Notifications Dropdown -->
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = ! open"
                    class="cursor-pointer p-2 rounded-full hover:bg-white/10 transition-colors">
                    <x-icon name="bell" class="w-8 h-8" />
                    @if($unreadCount > 0)
                        <span id="notification-bell-dot" x-ref="unreadDot" class="absolute top-1 right-1 h-2 w-2 rounded-full bg-red-500"></span>
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
                x-data="{
                    clearedAll: false,
                    readAll: false,
                    remainingCount: {{ count($notifications) }},
                    unreadNum: {{ $unreadCount }},
                    dismiss(id, wasUnread) {
                        this.remainingCount--;
                        if (wasUnread) {
                            this.unreadNum = Math.max(0, this.unreadNum - 1);
                        }
                        if (this.remainingCount <= 0) {
                            this.clearedAll = true;
                        }
                        if (this.unreadNum <= 0) {
                            let dot = document.getElementById('notification-bell-dot');
                            if (dot) dot.style.display = 'none';
                        }
                        $wire.deleteNotification(id);
                    },
                    markRead(id) {
                        this.unreadNum = Math.max(0, this.unreadNum - 1);
                        if (this.unreadNum <= 0) {
                            let dot = document.getElementById('notification-bell-dot');
                            if (dot) dot.style.display = 'none';
                        }
                        $wire.markAsRead(id);
                    },
                    clear() {
                        this.clearedAll = true;
                        this.remainingCount = 0;
                        this.unreadNum = 0;
                        let dot = document.getElementById('notification-bell-dot');
                        if (dot) dot.style.display = 'none';
                        $wire.deleteAllNotifications();
                    },
                    markAllRead() {
                        this.readAll = true;
                        this.unreadNum = 0;
                        let dot = document.getElementById('notification-bell-dot');
                        if (dot) dot.style.display = 'none';
                        $wire.markAllAsRead();
                    }
                }"
                class="absolute right-0 mt-2 w-80 bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border border-gray-200/50 dark:border-gray-700/50 rounded-xl shadow-2xl z-50 origin-top-right"
                style="backdrop-filter: blur(12px);">

                <!-- Header -->
                <div
                    class="px-3.5 py-3 border-b border-gray-100/50 dark:border-gray-800/50 font-semibold flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <x-icon name="bell" class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                        <span class="text-xs sm:text-sm text-gray-900 dark:text-gray-100">Notifications</span>
                        @if($unreadCount > 0)
                            <span x-show="!readAll && !clearedAll && unreadNum > 0"
                                x-text="unreadNum"
                                class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $unreadCount }}</span>
                        @endif
                    </div>
                    @if ($unreadCount > 0)
                        <button type="button" x-show="!readAll && !clearedAll && unreadNum > 0" @click="markAllRead()"
                            class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors cursor-pointer">
                            Mark all as read
                        </button>
                    @endif
                </div>

                <!-- Notifications List -->
                <div class="bg-gradient-dark max-h-60 overflow-y-auto">
                    @forelse($notifications as $notification)
                        <div
                            x-data="{ removed: false, isRead: {{ $notification->read_at ? 'true' : 'false' }} }"
                            x-show="!clearedAll && !removed"
                            x-transition:leave="transition ease-out duration-150"
                            x-transition:leave-start="opacity-100 max-h-24"
                            x-transition:leave-end="opacity-0 max-h-0"
                            wire:key="notif-{{ $notification->id }}"
                            class="px-3.5 py-2.5 hover:bg-blue-300/15 cursor-pointer transition-colors border-b border-gray-100/30 dark:border-gray-800/30 last:border-b-0 overflow-hidden"
                            :class="(!isRead && !readAll && !clearedAll) ? 'bg-blue-50/80 dark:bg-blue-900/20' : ''">
                            <div class="flex justify-between items-start gap-2.5">
                                @php
                                    $notifUrl = $notification->data['url'] ?? '#';
                                    if ($notifUrl !== '#' && (str_starts_with($notifUrl, 'http://') || str_starts_with($notifUrl, 'https://'))) {
                                        $parsedUrl = parse_url($notifUrl);
                                        $notifUrl = ($parsedUrl['path'] ?? '/') . (isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '') . (isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '');
                                    }
                                    $cleanMsg = preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', (string)($notification->data['message'] ?? 'New notification'));
                                    $cleanMsg = trim($cleanMsg);
                                @endphp
                                <a href="{{ $notifUrl }}"
                                    @click="if (!isRead) { isRead = true; markRead('{{ $notification->id }}'); }" class="flex-1 min-w-0">
                                    <div class="text-xs text-gray-900 dark:text-gray-100 leading-snug">
                                        {{ $cleanMsg }}
                                    </div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </div>
                                </a>
                                <button type="button"
                                    @click.stop="removed = true; dismiss('{{ $notification->id }}', !isRead)"
                                    class="text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors p-1 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20 cursor-pointer shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
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
                                <x-icon name="bell" class="w-7 h-7 mx-auto opacity-50" />
                            </div>
                            <div class="text-gray-500 dark:text-gray-400 text-xs">No notifications yet</div>
                        </div>
                    @endforelse

                    <!-- Dynamic Empty State when items dismissed/cleared -->
                    <div x-show="clearedAll || remainingCount <= 0" class="px-4 py-8 text-center" style="display: none;">
                        <div class="text-gray-400 dark:text-gray-500 mb-2">
                            <x-icon name="bell" class="w-7 h-7 mx-auto opacity-50" />
                        </div>
                        <div class="text-gray-500 dark:text-gray-400 text-xs">No notifications yet</div>
                    </div>
                </div>

                <!-- Footer -->
                @if($notifications->count() > 0)
                    <div x-show="!clearedAll && remainingCount > 0" class="p-3 border-t border-gray-100/50 dark:border-gray-800/50">
                        <button type="button" @click="clear()"
                            class="w-full text-center text-red-500 hover:text-red-700 dark:hover:text-red-400 transition-colors text-xs font-medium py-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 cursor-pointer">
                            Clear all notifications
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>





    <div id="dashboard-kpis-grid" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mt-5">
        {{-- Total Memos --}}
        <div class="card-highlight rounded-lg border border-white/5 bg-gradient-dark p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-400">Total Memos</h3>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-white/5">
                    <x-icon name="book-outline" class="h-4 w-4 text-blue-300" />
                </div>
            </div>
            <div class="mt-3 text-3xl font-bold tracking-tight text-white">{{ $totalEntries }}</div>
            <p class="mt-1 text-xs text-gray-500">
                @if($entriesFromLastWeek > 0)
                    <span class="text-emerald-400">▲ {{ $entriesFromLastWeek }}</span> from last week
                @elseif($entriesFromLastWeek < 0)
                    <span class="text-rose-400">▼ {{ abs($entriesFromLastWeek) }}</span> less than last week
                @else
                    Same as last week
                @endif
            </p>
        </div>

        {{-- Most Used Tag --}}
        <div class="card-highlight rounded-lg border border-white/5 bg-gradient-dark p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-400">Most Used Tag</h3>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-white/5">
                    <x-icon name="tag" class="h-4 w-4 text-emerald-300" />
                </div>
            </div>
            <div class="mt-3 text-3xl font-bold tracking-tight text-white">{{ $mostUsedTag ? '#' . $mostUsedTag->name : 'No tags' }}</div>
            <p class="mt-1 text-xs text-gray-500">Used in {{ $mostUsedTagCount }} entries</p>
        </div>

        {{-- Longest Entry --}}
        <div class="card-highlight rounded-lg border border-white/5 bg-gradient-dark p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-400">Longest Entry</h3>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-white/5">
                    <x-iconpark-writingfluently-o class="h-4 w-4 text-violet-300" />
                </div>
            </div>
            @if(isset($longestEntryCharCount) && isset($longestEntryDate))
                <div class="mt-3 text-3xl font-bold tracking-tight text-white">{{ $longestEntryCharCount }}</div>
                <p class="mt-1 text-xs text-gray-500">characters on {{ $longestEntryDate }}</p>
            @else
                <div class="mt-3 text-3xl font-bold tracking-tight text-white">0</div>
                <p class="mt-1 text-xs text-gray-500">No entries yet</p>
            @endif
        </div>

        {{-- Daily Streak --}}
        <div class="card-highlight rounded-lg border border-white/5 bg-gradient-dark p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-400">Daily Streak</h3>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-white/5">
                    <x-icon name="flash-outline" class="h-4 w-4 text-amber-300" />
                </div>
            </div>
            <div class="mt-3 text-3xl font-bold tracking-tight text-white">{{ $currentStreak }}</div>
            <p class="mt-1 text-xs text-gray-500">{{ $streakMessage }}</p>
        </div>
    </div>
    <!-- AI-Powered Quick Actions -->
    <div id="dashboard-action-buttons">
        @livewire('dashboard.ai-quick-chat')
    </div>
    <!-- Recent section goes here -->
    @if($recentEntries && $recentEntries->count() > 0)
        <div class="mt-8">
            <h2 class="text-xl font-semibold mb-4">Recent Entry</h2>
            @foreach($recentEntries as $entry)
                <a href="{{ route('entries.show', $entry) }}" wire:navigate.hover>
                    <div
                        class="rounded-lg bg-card text-card-foreground shadow-md card-highlight bg-gradient-dark border border-white/10">
                        <div class="flex flex-col space-y-1.5 p-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-2xl font-semibold leading-none tracking-tight">{{ $entry->title }}</h3>
                                <div class="text-sm text-muted-foreground">{{ $entry->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        <div class="p-6 pt-0">
                            <p class="line-clamp-3 text-sm text-gray-300">{{ strip_tags(\Illuminate\Support\Str::markdown($entry->content)) }}</p>
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
