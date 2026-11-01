<div id="chatRoot" class="relative flex h-full max-h-full w-full bg-gradient-dark gap-0 md:gap-4 overflow-hidden overscroll-none"
    x-data="{
        showDeleteModal: false,
        sessionToDelete: null,
        sessionTitleToDelete: '',
        deletedSessionIds: [],
        activeSessionId: '{{ $activeSession['id'] ?? '' }}',
        hasPendingExchange: false,
        mobileDrawerOpen: false,
        desktopNavCollapsed: (function() {
            try { return localStorage.getItem('chat-nav-collapsed') === '1' && window.innerWidth >= 768; } catch(e) { return false; }
        })(),
        toggleDesktopNav() {
            this.desktopNavCollapsed = !this.desktopNavCollapsed;
            try { localStorage.setItem('chat-nav-collapsed', this.desktopNavCollapsed ? '1' : '0'); } catch(e){}
            this.syncBodyState();
        },
        openMobile() {
            this.mobileDrawerOpen = true;
            document.body.classList.add('chat-open');
        },
        closeMobile() {
            this.mobileDrawerOpen = false;
            document.body.classList.remove('chat-open');
        },
        toggleMobile() {
            if (this.mobileDrawerOpen) {
                this.closeMobile();
            } else {
                this.openMobile();
            }
        },
        syncBodyState() {
            if (this.desktopNavCollapsed && window.innerWidth >= 768) {
                document.body.classList.add('chat-nav-collapsed');
                document.documentElement.classList.add('chat-nav-collapsed');
            } else {
                document.body.classList.remove('chat-nav-collapsed');
                document.documentElement.classList.remove('chat-nav-collapsed');
            }
        },
        previewEntry: null,
        previewPosition: { x: 0, y: 0 },
        showPreview: false,
        isExpanded: false,
        previewTimeout: null,
        openEntryPreview(entry, event) {
            if (typeof entry === 'string') {
                try { entry = JSON.parse(entry); } catch (e) {}
            }
            if (this.previewTimeout) clearTimeout(this.previewTimeout);
            this.previewEntry = entry;

            if (!this.isExpanded && event) {
                const el = (event.currentTarget && typeof event.currentTarget.getBoundingClientRect === 'function')
                    ? event.currentTarget
                    : (event.target ? (event.target.closest('button, [data-entry-preview], div') || event.target) : null);

                if (el && typeof el.getBoundingClientRect === 'function') {
                    const rect = el.getBoundingClientRect();
                    const chatArea = document.getElementById('mainChatArea') || document.getElementById('chatRoot');
                    const bounds = chatArea ? chatArea.getBoundingClientRect() : {
                        left: 16,
                        right: window.innerWidth - 16,
                        top: 16,
                        bottom: window.innerHeight - 16
                    };

                    const cardWidth = 420;
                    const cardHeight = 220;

                    let left = rect.left;
                    if (left + cardWidth > bounds.right - 16) {
                        left = bounds.right - cardWidth - 16;
                    }
                    if (left < bounds.left + 16) {
                        left = bounds.left + 16;
                    }

                    let top = rect.bottom + 8;
                    if (top + cardHeight > bounds.bottom - 16 && rect.top > cardHeight + 16) {
                        top = rect.top - cardHeight - 8;
                    }
                    if (top < bounds.top + 16) {
                        top = bounds.top + 16;
                    }

                    this.previewPosition = { x: Math.round(left), y: Math.round(top) };
                }
            }

            this.showPreview = true;
        },
        toggleExpand() {
            this.isExpanded = !this.isExpanded;
            if (this.previewTimeout) clearTimeout(this.previewTimeout);
        },
        closeEntryPreview(delay = 180) {
            if (this.isExpanded && delay > 0) {
                return;
            }
            if (this.previewTimeout) clearTimeout(this.previewTimeout);
            if (delay === 0) {
                this.showPreview = false;
                this.isExpanded = false;
                return;
            }
            this.previewTimeout = setTimeout(() => {
                this.showPreview = false;
                this.isExpanded = false;
            }, delay);
        },
        keepPreviewOpen() {
            if (this.previewTimeout) clearTimeout(this.previewTimeout);
        }
    }"
    x-init="
        syncBodyState();
        $watch('mobileDrawerOpen', val => {
            if (val) {
                document.body.classList.add('chat-open');
            } else {
                document.body.classList.remove('chat-open');
            }
        });
    "
    :class="{ 'chat-nav-collapsed': desktopNavCollapsed }"
    data-stream-endpoint="{{ route('chat.stream') }}"
    data-conversation-id="{{ $activeSession['id'] ?? '' }}"
    data-user-initial="{{ substr(auth()->user()->name ?? 'U', 0, 1) }}"
    data-has-api-key="{{ !empty(auth()->user()->api_key) ? '1' : '0' }}">
    <!-- Chat Drawer / Sidebar -->
    <div id="chatDrawer"
        :class="mobileDrawerOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full'"
        class="fixed md:static inset-y-0 md:inset-auto left-0 z-50 w-80 max-w-[85vw] md:h-full bg-gradient-dark sidebar-gradient rounded-none md:rounded-lg border-r md:border border-gray-700 flex flex-col md:translate-x-0 transition-all duration-300">
        <!-- Header -->
        <div class="p-4 border-b border-gray-700 flex items-center justify-between gap-2">
            <button wire:click="createNewSession"
                @click="activeSessionId = ''; hasPendingExchange = false; closeMobile(); if (window.__syncConversationUrl) window.__syncConversationUrl(null);"
                wire:loading.attr="disabled"
                wire:target="createNewSession"
                class="cursor-pointer flex-1 flex items-center justify-center gap-2 bg-gradient-dark text-white rounded-lg px-4 py-2.5 transition-colors border border-white/10 hover:bg-blue-300/15 disabled:opacity-50">
                <x-icon wire:loading.remove wire:target="createNewSession" name="message" class="w-5 h-5 shrink-0" />
                <svg wire:loading wire:target="createNewSession" class="animate-spin w-5 h-5 text-blue-400 shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span>New Chat</span>
            </button>
            <div class="flex items-center gap-1">
                <!-- Minimize chat conversations (desktop) -->
                <button id="chatNavToggle"
                    type="button"
                    @click="toggleDesktopNav()"
                    class="hidden md:inline-flex items-center justify-center w-8 h-8 rounded-md border border-white/10 text-white/80 hover:text-white hover:bg-blue-300/15 transition cursor-pointer"
                    aria-label="Minimize conversations" title="Minimize conversations">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 17l-5-5 5-5" />
                        <path d="M11 17l-5-5 5-5" />
                    </svg>
                </button>
                <!-- Close drawer (mobile/overlay) -->
                <button id="chatDrawerClose"
                    type="button"
                    @click="closeMobile()"
                    class="ml-auto p-2 text-gray-400 hover:text-white transition-colors md:hidden cursor-pointer"
                    aria-label="Close drawer">
                    <x-icon name="panel-right-open" class="w-5 h-5" />
                </button>
            </div>
        </div>

        <!-- Navigation Menu -->
        <div class="flex-1 overflow-y-auto scrollbar-none">
            <!-- Recent Chats Section -->
            <div class="p-4">
                <h3 class="text-sm font-medium text-gray-400 mb-3">Recent Chats</h3>
                @forelse($sessions as $session)
                    @php
                        $isActive = ($activeSession['id'] ?? null) == $session['id'];
                    @endphp
                    <div wire:key="chat-session-{{ $session['id'] }}"
                        wire:click="selectSession({{ $session['id'] }})"
                        @click="activeSessionId = String('{{ $session['id'] }}'); closeMobile()"
                        x-show="!deletedSessionIds.includes(String('{{ $session['id'] }}'))"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="chat-session-item group p-3 rounded-lg cursor-pointer transition-colors mb-2 {{ $isActive ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-300 hover:bg-gray-700/60' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <x-icon name="message"
                                        class="w-4 h-4 {{ $isActive ? 'text-white' : 'text-gray-400' }}" />
                                    <h4 class="text-sm font-medium truncate">{{ $session['title'] }}</h4>
                                </div>
                                <p class="text-xs {{ $isActive ? 'text-blue-200' : 'text-gray-500' }}">
                                    {{ $session['lastActivity'] }} • {{ $session['messageCount'] }} messages
                                </p>
                            </div>
                            <button type="button"
                                @click.stop="sessionToDelete = '{{ $session['id'] }}'; sessionTitleToDelete = '{{ addslashes($session['title']) }}'; showDeleteModal = true"
                                class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-400 transition-all p-1.5 rounded hover:bg-red-500/10 cursor-pointer"
                                aria-label="Delete chat">
                                <x-icon name="trash" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500 py-8">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                            </path>
                        </svg>
                        <p class="text-sm">No conversations yet</p>
                        <p class="text-xs mt-1">Start a new chat to begin</p>
                    </div>
                @endforelse
            </div>


        </div>


    </div>

    <!-- Backdrop for small screens -->
    <div id="chatBackdrop"
        x-show="mobileDrawerOpen"
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closeMobile()"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 md:hidden"
        style="display: none;"></div>

    <!-- Main Chat Area -->
    <div id="mainChatArea" class="relative flex-1 flex flex-col sidebar-gradient border-0 md:border md:border-gray-700 rounded-none md:rounded-lg overflow-hidden h-full max-h-full overscroll-none transition-all duration-300">
        <!-- Slim pull-out tab attached to the main chat section -->
        <button id="chatDockBtn"
            type="button"
            @click="if (window.innerWidth >= 768) { toggleDesktopNav(); } else { toggleMobile(); }"
            class="chat-dock-tab absolute left-0 top-3.5 z-30 flex items-center justify-center gap-1.5 h-8 px-2.5 rounded-r-md border border-l-0 border-white/15 bg-[#0f111a]/95 hover:bg-blue-300/15 text-white/90 hover:text-white shadow-[0_4px_16px_rgba(0,0,0,0.6)] backdrop-blur-md cursor-pointer transition-all duration-300 select-none group"
            aria-label="Open conversations"
            title="Open conversations">
            <svg class="w-3.5 h-3.5 text-white/80 group-hover:text-white transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 17l-5-5 5-5" />
                <path d="M11 17l-5-5 5-5" />
            </svg>
            <span class="text-xs font-medium text-white/80 group-hover:text-white hidden sm:inline">Conversations</span>
        </button>

        <!-- Chat Header (Fixed, stays on top and never covered by loading overlay) -->
        <div class="bg-gradient-dark border-b border-gray-700 p-3 sm:p-4 rounded-t-none md:rounded-t-lg sticky top-0 z-20">
            <div class="chat-header-inner flex items-center justify-between gap-3 transition-all duration-300">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="p-2 rounded-full {{ $activeSession ? 'bg-blue-600' : 'bg-blue-600/30 border border-blue-500/30' }} shrink-0"
                        x-bind:class="(!activeSessionId || deletedSessionIds.includes(String(activeSessionId))) ? 'bg-blue-600/30 border border-blue-500/30' : 'bg-blue-600'">
                        <x-icon name="brain" class="w-6 h-6 text-white" />
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-base sm:text-lg font-semibold text-white truncate"
                            x-text="(!activeSessionId || deletedSessionIds.includes(String(activeSessionId))) ? 'AI Reflection Companion' : '{{ addslashes($activeSession['title'] ?? 'AI Reflection Companion') }}'">
                            {{ $activeSession ? $activeSession['title'] : 'AI Reflection Companion' }}
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-400 truncate"
                            x-text="(!activeSessionId || deletedSessionIds.includes(String(activeSessionId))) ? 'Your calm space to reflect, think, and explore' : 'AI-powered reflection and insights'">
                            {{ $activeSession ? 'AI-powered reflection and insights' : 'Your calm space to reflect, think, and explore' }}
                        </p>
                    </div>
                </div>

                <!-- Right: Mobile app sidebar hamburger toggle -->
                <button id="mobileSidebarToggle"
                    type="button"
                    class="md:hidden p-2 text-white/80 hover:text-white rounded-lg border border-white/20 bg-white/5 hover:bg-white/10 active:scale-95 transition shrink-0 cursor-pointer"
                    aria-label="Toggle navigation menu"
                    title="Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Main Chat Body (Messages / Greeting & Starters) -->
        <div class="relative flex-1 flex flex-col overflow-hidden">
            <!-- Loading overlay for content area only, perfectly centered with wire:loading.flex (only when switching sessions, no text) -->
            <div wire:loading.flex wire:target="selectSession"
                class="absolute inset-0 z-30 flex items-center justify-center bg-[#070b14]/75 backdrop-blur-sm">
                <div class="w-8 h-8 border-2 border-blue-400 border-t-transparent rounded-full animate-spin"></div>
            </div>

            <!-- Messages Area (Scrollable) -->
            <div class="flex-1 overflow-y-auto overscroll-contain p-4 md:p-6 pb-28 md:pb-6" id="messages-scroll">
                @if($activeSession)
                    <div wire:key="messages-{{ $activeSession['id'] }}"
                        x-show="activeSessionId && !deletedSessionIds.includes(String(activeSessionId))"
                        class="space-y-6" id="messages-container">
                        @if ($isLoadingMessages)
                            <div class="flex items-center justify-center py-16">
                                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                            </div>
                        @else
                            @foreach($messages as $message)
                                <div class="flex items-start gap-4 {{ $message['isAi'] ? '' : 'flex-row-reverse' }}">
                                    @if($message['isAi'])
                                        <div class="w-8 h-8 {{ isset($message['isError']) ? 'bg-red-600' : 'bg-blue-600' }} rounded-full flex items-center justify-center flex-shrink-0 shadow-sm border border-blue-400/20">
                                            @if(isset($message['isError']))
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @else
                                                <img src="{{ asset('lumi.svg') }}" alt="Lumi" class="w-5.5 h-5.5 object-contain select-none pointer-events-none" />
                                            @endif
                                        </div>
                                    @else
                                        <div class="w-8 h-8 bg-gray-600 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-sm font-medium text-white">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                                        </div>
                                    @endif

                                    <div class="max-w-[85%] {{ $message['isAi'] ? 'md:max-w-[650px] w-full flex flex-col items-start' : 'md:max-w-[500px] flex flex-col items-end' }}">
                                        @if($message['isAi'] && !empty($message['metadata']['tools_used']))
                                            @php
                                                $toolsUsed = $message['metadata']['tools_used'];
                                                $totalTools = count($toolsUsed);
                                                $totalDurationMs = array_sum(array_column($toolsUsed, 'duration_ms'));
                                                $totalDurationFormatted = $totalDurationMs >= 1000 
                                                    ? number_format($totalDurationMs / 1000, 1) . 's' 
                                                    : ($totalDurationMs > 0 ? $totalDurationMs . 'ms' : '');
                                            @endphp
                                            <div class="mb-2" x-data="{ timelineOpen: false }">
                                                {{-- Collapsible Timeline Header --}}
                                                <button 
                                                    type="button" 
                                                    @click="timelineOpen = !timelineOpen" 
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] border border-white/10 text-xs text-gray-400 hover:text-gray-200 transition-all cursor-pointer select-none group">
                                                    <span class="font-mono text-blue-400 text-[11px]">&gt;_</span>
                                                    <span class="font-mono text-[11px]">Ran {{ $totalTools }} {{ $totalTools === 1 ? 'command' : 'commands' }}</span>
                                                    @if($totalDurationFormatted)
                                                        <span class="text-gray-500 font-mono text-[10px]">· {{ $totalDurationFormatted }}</span>
                                                    @endif
                                                    <svg class="w-3 h-3 text-gray-500 transition-transform duration-200" :class="{ 'rotate-180': timelineOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>

                                                {{-- Vertical Grok-Style Rail --}}
                                                <div x-show="timelineOpen" x-cloak class="mt-2.5 ml-2.5 pl-4 border-l border-white/15 space-y-2 py-1">
                                                    @foreach($toolsUsed as $tool)
                                                        <div x-data="{ stepOpen: false }" class="relative">
                                                            {{-- Rail Node --}}
                                                            <div class="absolute -left-[21px] top-1.5 w-2.5 h-2.5 rounded-full bg-[#0d1424] border-2 border-blue-400"></div>

                                                            {{-- Step Toggle Button --}}
                                                            <button 
                                                                type="button" 
                                                                @click="stepOpen = !stepOpen" 
                                                                class="w-full text-left flex items-center justify-between gap-2 px-2 py-1 rounded-lg hover:bg-white/[0.05] transition-colors cursor-pointer select-none group">
                                                                <span class="inline-flex items-center gap-1.5 text-xs text-gray-300 font-mono">
                                                                    <span class="text-blue-400/90">&gt;_</span>
                                                                    <span class="text-gray-300 group-hover:text-white transition-colors">
                                                                        @if(($tool['tool'] ?? '') === 'search_journal')
                                                                            Ran search: "{{ $tool['args']['query'] ?? '' }}"
                                                                        @elseif(($tool['tool'] ?? '') === 'get_user_stats')
                                                                            Ran check: journaling stats & streak
                                                                        @elseif(($tool['tool'] ?? '') === 'list_tags')
                                                                            Ran check: journal tags
                                                                        @elseif(($tool['tool'] ?? '') === 'draft_entry')
                                                                            Ran command: save memo "{{ $tool['args']['title'] ?? '' }}"
                                                                        @else
                                                                            Ran command: {{ $tool['tool'] ?? 'action' }}
                                                                        @endif
                                                                    </span>
                                                                </span>
                                                                <div class="flex items-center gap-1.5 shrink-0">
                                                                    @if(!empty($tool['duration_ms']))
                                                                        <span class="text-[10px] text-gray-500 font-mono">{{ $tool['duration_ms'] }}ms</span>
                                                                    @endif
                                                                    <svg class="w-3 h-3 text-gray-500 transition-transform duration-200" :class="{ 'rotate-180': stepOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                                    </svg>
                                                                </div>
                                                            </button>

                                                            {{-- Step Drawer --}}
                                                            <div x-show="stepOpen" x-cloak class="mt-1 mb-2 rounded-xl border border-white/10 bg-black/40 p-2.5 space-y-2 text-xs">
                                                                @if(!empty($tool['args']))
                                                                    <div>
                                                                        <span class="text-[10px] font-mono text-gray-500 uppercase tracking-wider block mb-1">Command Parameters</span>
                                                                        <div class="p-2 rounded-lg bg-black/60 border border-white/5 font-mono text-[11px] text-blue-300 overflow-x-auto">
                                                                            <pre class="whitespace-pre-wrap break-all">{{ json_encode($tool['args'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                @if(!empty($tool['result']))
                                                                    <div>
                                                                        <span class="text-[10px] font-mono text-gray-500 uppercase tracking-wider block mb-1">Output</span>
                                                                        @if(isset($tool['result']['entries']) && is_array($tool['result']['entries']))
                                                                            <div class="space-y-1.5 font-sans">
                                                                                @foreach($tool['result']['entries'] as $resEntry)
                                                                                    @php
                                                                                        $resEntryB64 = base64_encode(json_encode($resEntry, JSON_UNESCAPED_UNICODE));
                                                                                    @endphp
                                                                                    <div 
                                                                                        data-entry-preview="true"
                                                                                        data-entry-b64="{{ $resEntryB64 }}"
                                                                                        onclick="window.location.href = '{{ route('entries.show', $resEntry['id']) }}'"
                                                                                        class="bg-white/5 p-2 rounded-lg border border-white/5 hover:border-blue-500/40 hover:bg-white/[0.08] transition-all cursor-pointer group">
                                                                                        <div class="flex items-center justify-between text-xs gap-2">
                                                                                            <span class="font-medium text-white group-hover:text-blue-300 transition-colors line-clamp-1 flex items-center gap-1.5">
                                                                                                <svg class="w-3.5 h-3.5 text-blue-400 opacity-70 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><path class="transition-transform group-hover:translate-x-0.5" d="M7 8h8"/><path class="transition-transform group-hover:-translate-x-0.5" d="M7 12h10"/><path class="transition-transform group-hover:translate-x-0.5" d="M7 16h6"/></svg>
                                                                                                {{ $resEntry['title'] }}
                                                                                            </span>
                                                                                            <span class="text-gray-500 text-[10px] font-mono">{{ $resEntry['date'] }}</span>
                                                                                        </div>
                                                                                        <p class="text-gray-400 text-xs mt-1 italic line-clamp-1">"{{ $resEntry['snippet'] }}"</p>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @elseif(isset($tool['result']['tags']) && is_array($tool['result']['tags']))
                                                                            <div class="flex flex-wrap gap-1.5 font-sans">
                                                                                @foreach($tool['result']['tags'] as $resTag)
                                                                                    <span class="inline-flex items-center gap-1 bg-white/5 px-2 py-0.5 rounded text-gray-300 text-xs border border-white/5">
                                                                                        <span>#{{ $resTag['name'] }}</span>
                                                                                        <span class="text-[10px] text-gray-500 font-mono">({{ $resTag['entries_count'] }})</span>
                                                                                    </span>
                                                                                @endforeach
                                                                            </div>
                                                                        @elseif(isset($tool['result']['status']) && $tool['result']['status'] === 'not_found')
                                                                            <div class="p-2 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-300 text-[11px] font-mono">
                                                                                {{ $tool['result']['message'] ?? '0 memories matched. Query reformulated.' }}
                                                                            </div>
                                                                        @else
                                                                            <div class="p-2 rounded-lg bg-black/60 border border-white/5 font-mono text-[11px] text-gray-300 overflow-x-auto">
                                                                                <pre class="whitespace-pre-wrap break-all">{{ json_encode($tool['result'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <div
                                            class="rounded-2xl px-4 py-3 w-fit max-w-full text-left {{ $message['isAi'] ? (isset($message['isError']) ? 'bg-red-900/60 border border-red-500/40 text-red-200' : 'bg-gray-800 text-gray-100') : 'bg-blue-600 text-white' }}">
                                            <p class="text-sm leading-relaxed whitespace-pre-wrap break-words">{!! $this->renderMessageContent($message) !!}</p>
                                        </div>

                                        <div class="flex items-center gap-2 mt-1.5 {{ $message['isAi'] ? '' : 'justify-end' }}">
                                            <p class="text-xs text-gray-500 message-timestamp" data-created-at="{{ $message['createdAt'] }}"></p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endif

                <!-- Greeting Content Area when no active session or when deleted instantly -->
                @if(!$activeSession)
                    <div id="empty-chat-state"
                        x-show="(!activeSessionId || deletedSessionIds.includes(String(activeSessionId))) && !hasPendingExchange"
                        class="my-auto py-8 px-4 w-full max-w-3xl mx-auto flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                        <div class="text-left max-w-sm">
                            <h2 class="text-xl md:text-2xl font-normal text-white/90 tracking-tight leading-snug">{{ $greeting }}</h2>
                        </div>

                        <div class="flex flex-col gap-2.5 w-full md:max-w-xs text-left">
                            @foreach($starterPrompts as $prompt)
                                <button type="button"
                                    onclick="window.setChatPrompt('{{ addslashes($prompt['action']) }}')"
                                    class="p-3 rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 hover:border-white/20 transition-all cursor-pointer group text-left">
                                    <span class="text-sm font-normal text-gray-400 group-hover:text-white transition-colors block">
                                        {{ $prompt['title'] }}
                                    </span>
                                    <span class="text-xs text-gray-500 group-hover:text-gray-300 transition-colors block mt-0.5 line-clamp-1">
                                        {{ $prompt['subtitle'] }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Unified Chat Pending Lane (always present, never duplicated, identical full-width styling) -->
                <div id="chat-pending-lane" wire:ignore class="space-y-6 mt-4"></div>
            </div>

            <!-- Message Input Always Present -->
            <div
                class="bg-gray-700/60 backdrop-blur border-t border-gray-600 rounded-b-lg bg-gradient-dark p-3 md:p-4 sticky bottom-0 z-10 safe-bottom">
                <x-chat-form wire-model="newMessage" placeholder="Share your thoughts..."
                    :is-typing="false" submit-icon="send" typing-icon="stop" :use-client-submit="true" />
            </div>
        </div>

        <!-- Large Entry Preview Modal (Scoped strictly inside mainChatArea, so it never overlaps the chat sidebar) -->
        <div x-show="showPreview && previewEntry && isExpanded"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute inset-0 z-40 flex items-center justify-center p-4 md:p-6 bg-black/60 backdrop-blur-sm"
             @click.self="closeEntryPreview(0)"
             @keydown.escape.window="closeEntryPreview(0)">
            <div class="bg-gradient-dark border border-white/15 rounded-2xl shadow-2xl max-w-xl w-full max-h-[85vh] flex flex-col overflow-hidden ring-1 ring-white/10"
                 @click.stop>
                {{-- Header --}}
                <div class="p-5 border-b border-white/10 flex items-center justify-between shrink-0 bg-white/[0.02]">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/15 border border-blue-500/30 flex items-center justify-center text-blue-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M3 7V5a2 2 0 0 1 2-2h2" />
                                <path d="M17 3h2a2 2 0 0 1 2 2v2" />
                                <path d="M21 17v2a2 2 0 0 1-2 2h-2" />
                                <path d="M7 21H5a2 2 0 0 1-2-2v-2" />
                                <path d="M7 8h8" />
                                <path d="M7 12h10" />
                                <path d="M7 16h6" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base sm:text-lg font-semibold text-white truncate" x-text="previewEntry?.title"></h3>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs text-muted font-mono" x-text="previewEntry?.date"></span>
                                <template x-if="previewEntry?.match_type === 'exact_and_concept'">
                                    <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-300 border border-emerald-500/20 font-mono">Exact & Theme</span>
                                </template>
                                <template x-if="previewEntry?.match_type === 'keyword_match'">
                                    <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-amber-500/15 text-amber-300 border border-amber-500/20 font-mono">Exact Match</span>
                                </template>
                                <template x-if="previewEntry?.match_type === 'conceptual_similarity'">
                                    <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-blue-500/15 text-blue-300 border border-blue-500/20 font-mono">Similar Theme</span>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <button type="button" @click.stop="toggleExpand()"
                            class="p-2 text-muted hover:text-white hover:bg-white/10 rounded-lg transition-colors cursor-pointer"
                            title="Minimize preview">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9L4 4m0 5h5V4m6 5l5-5m-5 5h5V4M9 15l-5 5m0-5h5v5m6-5l5 5m-5-5h5v5"></path>
                            </svg>
                        </button>
                        <button type="button" @click.stop="closeEntryPreview(0)"
                            class="p-2 text-muted hover:text-white hover:bg-white/10 rounded-lg transition-colors cursor-pointer"
                            aria-label="Close modal">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Content (scrollable without visible scrollbar) --}}
                <div class="p-5 overflow-y-auto flex-1 min-h-0 space-y-4 overscroll-contain scrollbar-none [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                    <div class="rounded-xl border border-white/10 sidebar-gradient p-4 shadow-inner">
                        <div class="flex items-center gap-2 pb-2 mb-2.5 border-b border-white/10">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-blue-400">Memory Excerpt</h4>
                        </div>
                        <div class="text-sm text-gray-200 leading-relaxed font-normal whitespace-pre-wrap font-sans max-h-[45vh] overflow-y-auto overscroll-contain scrollbar-none [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden" x-text="previewEntry?.snippet"></div>
                    </div>

                    <!-- Tags -->
                    <template x-if="previewEntry?.tags && previewEntry.tags.length">
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            <template x-for="tag in previewEntry.tags" :key="tag">
                                <span class="text-xs px-2.5 py-1 rounded-md bg-white/5 border border-white/10 text-gray-300 font-mono" x-text="'#' + tag"></span>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="p-4 border-t border-white/10 flex items-center justify-between bg-black/20 shrink-0">
                    <a :href="'/entries/' + previewEntry?.id" wire:navigate
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-500 rounded-lg transition-colors cursor-pointer shadow-sm">
                        <svg class="w-4 h-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        <span>Open in Journal</span>
                    </a>
                    <button type="button" @click.stop="closeEntryPreview(0)"
                        class="px-4 py-2 text-sm font-medium text-white border border-white/10 rounded-lg hover:bg-white/5 transition-colors cursor-pointer">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Chat Confirmation Modal -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" @keydown.escape.window="showDeleteModal = false">
        <!-- blur backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showDeleteModal = false"></div>

        <div
            class="relative bg-gradient-dark border border-[rgb(29,40,58)]/10 rounded-lg shadow-2xl p-8 max-w-md w-full mx-4 transform transition-all z-10">
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                        </path>
                    </svg>
                </div>
            </div>

            <h3 class="text-xl font-bold text-center mb-2">Delete Conversation</h3>

            <p class="text-muted text-center mb-8 leading-relaxed">
                Are you sure you want to delete <span class="text-white font-medium" x-text="sessionTitleToDelete ? '«' + sessionTitleToDelete + '»' : 'this conversation'"></span>?
                <br><br>
                This action cannot be undone and will permanently remove all messages from this chat.
            </p>

            <div class="flex gap-3">
                <button type="button" @click="showDeleteModal = false; sessionToDelete = null; sessionTitleToDelete = ''"
                    class="flex-1 px-4 py-3 rounded-md text-sm font-medium text-white border border-white/10 hover:bg-[rgb(29,40,58)] transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="button"
                    @click="if (sessionToDelete) { 
                        const idToDelete = sessionToDelete;
                        deletedSessionIds.push(String(idToDelete)); 
                        showDeleteModal = false; 
                        sessionToDelete = null; 
                        sessionTitleToDelete = ''; 
                        if (String(activeSessionId) === String(idToDelete)) {
                            activeSessionId = '';
                            const root = document.getElementById('chatRoot');
                            if (root) {
                                root.dataset.conversationId = '';
                            }
                            if (window.__syncConversationUrl) {
                                window.__syncConversationUrl(null);
                            }
                            const lane = document.getElementById('chat-pending-lane');
                            if (lane) {
                                lane.innerHTML = '';
                            }
                        }
                        $wire.deleteSession(idToDelete); 
                    }"
                    class="flex-1 px-4 py-3 rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition-colors cursor-pointer">
                    Delete Chat
                </button>
            </div>
        </div>
    </div>

    <!-- Interactive Floating Entry Hover Preview (Mini Popover) -->
    <div x-show="showPreview && previewEntry && !isExpanded" 
         x-cloak 
         @mouseenter="keepPreviewOpen()"
         @mouseleave="closeEntryPreview(180)"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
         class="fixed z-50 w-[420px] max-w-[92vw] p-4 rounded-2xl bg-gradient-dark border border-white/10 shadow-2xl shadow-black/90 ring-1 ring-white/10 text-left pointer-events-auto flex flex-col"
         :style="`left: ${previewPosition.x}px; top: ${previewPosition.y}px;`">
        
        <div class="flex items-start justify-between gap-2 mb-2.5">
            <div class="flex items-center gap-2 min-w-0">
                <span class="w-7 h-7 rounded-lg bg-blue-500/15 border border-blue-500/30 flex items-center justify-center text-blue-400 flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 7V5a2 2 0 0 1 2-2h2" />
                        <path d="M17 3h2a2 2 0 0 1 2 2v2" />
                        <path d="M21 17v2a2 2 0 0 1-2 2h-2" />
                        <path d="M7 21H5a2 2 0 0 1-2-2v-2" />
                        <path d="M7 8h8" />
                        <path d="M7 12h10" />
                        <path d="M7 16h6" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <h4 class="text-xs font-semibold text-white leading-tight truncate" x-text="previewEntry?.title"></h4>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="text-[10px] text-gray-400 font-mono truncate" x-text="previewEntry?.date"></span>
                        <template x-if="previewEntry?.match_type === 'exact_and_concept'">
                            <span class="text-[9px] px-1.5 py-0.2 rounded-full bg-emerald-500/15 text-emerald-300 border border-emerald-500/20 font-mono flex-shrink-0">Exact & Theme</span>
                        </template>
                        <template x-if="previewEntry?.match_type === 'keyword_match'">
                            <span class="text-[9px] px-1.5 py-0.2 rounded-full bg-amber-500/15 text-amber-300 border border-amber-500/20 font-mono flex-shrink-0">Exact Match</span>
                        </template>
                        <template x-if="previewEntry?.match_type === 'conceptual_similarity'">
                            <span class="text-[9px] px-1.5 py-0.2 rounded-full bg-blue-500/15 text-blue-300 border border-blue-500/20 font-mono flex-shrink-0">Similar Theme</span>
                        </template>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-1 flex-shrink-0">
                <button type="button" @click.stop="toggleExpand()"
                    class="p-1 text-gray-400 hover:text-white hover:bg-white/10 rounded transition-colors cursor-pointer"
                    title="Enlarge preview">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                    </svg>
                </button>
                <button type="button" @click.stop="closeEntryPreview(0)"
                    class="p-1 text-gray-400 hover:text-white hover:bg-white/10 rounded transition-colors cursor-pointer"
                    title="Close">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Scrollable content without visible scrollbar -->
        <div class="overflow-y-auto max-h-40 overscroll-contain pr-1 mb-3 scrollbar-none [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
            <div class="sidebar-gradient p-3.5 rounded-xl border border-white/10 shadow-inner">
                <p class="text-xs text-gray-200 leading-relaxed font-normal whitespace-pre-wrap font-sans" x-text="previewEntry?.snippet"></p>
            </div>
        </div>

        <div class="flex items-center justify-between gap-2 pt-2 border-t border-white/10 mt-auto">
            <div class="flex flex-wrap gap-1 max-w-[65%]">
                <template x-for="tag in (previewEntry?.tags || []).slice(0, 2)" :key="tag">
                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-white/5 text-gray-400 font-mono" x-text="'#' + tag"></span>
                </template>
            </div>
            <a :href="'/entries/' + previewEntry?.id" wire:navigate.hover
               class="inline-flex items-center gap-1 text-xs font-medium text-blue-400 hover:text-blue-300 group">
                <span>Read memo</span>
                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </a>
        </div>
    </div>

<script>
    (function () {
        let activeStreamController = null;

        const scrollMessagesToBottom = () => {
            const container = document.getElementById('messages-scroll');
            if (!container) {
                return;
            }

            requestAnimationFrame(() => {
                container.scrollTop = container.scrollHeight;
            });
        };

        const getCsrfToken = () => {
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta && meta.content) {
                return meta.content;
            }
            const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
            return match ? decodeURIComponent(match[1]) : '';
        };

        const getChatComponent = () => {
            const root = document.getElementById('chatRoot');
            if (!root) {
                return null;
            }

            const wireElement = root.closest('[wire\\:id]');
            if (!wireElement) {
                return null;
            }

            return Livewire.find(wireElement.getAttribute('wire:id'));
        };

        const parseSseChunk = (buffer) => {
            const events = [];
            const segments = buffer.split('\n\n');
            const remainder = segments.pop() ?? '';

            for (const rawEvent of segments) {
                if (!rawEvent.trim()) {
                    continue;
                }

                let eventType = 'message';
                const dataLines = [];

                for (const line of rawEvent.split('\n')) {
                    if (line.startsWith('event:')) {
                        eventType = line.slice(6).trim();
                    } else if (line.startsWith('data:')) {
                        dataLines.push(line.slice(5).trim());
                    }
                }

                if (dataLines.length === 0) {
                    continue;
                }

                try {
                    events.push({
                        type: eventType,
                        data: JSON.parse(dataLines.join('\n')),
                    });
                } catch (error) {
                    console.error('Failed to parse stream event', error);
                }
            }

            return { events, remainder };
        };

        const escapeHtml = (value) => {
            const element = document.createElement('div');
            element.textContent = value;
            return element.innerHTML;
        };

        const formatMessageTime = (isoString = null) => {
            const date = isoString ? new Date(isoString) : new Date();

            if (Number.isNaN(date.getTime())) {
                return '';
            }

            return date.toLocaleTimeString([], {
                hour: 'numeric',
                minute: '2-digit',
            });
        };

        const refreshMessageTimestamps = () => {
            document.querySelectorAll('.message-timestamp[data-created-at]').forEach((element) => {
                const formatted = formatMessageTime(element.dataset.createdAt);

                if (formatted) {
                    element.textContent = formatted;
                }
            });
        };

        const setFormBusy = (busy) => {
            const form = document.getElementById('chat-message-form');
            const textarea = form?.querySelector('textarea');
            const button = form?.querySelector('button[type="submit"]');

            if (textarea) {
                textarea.disabled = busy;
            }

            if (button) {
                button.disabled = busy;
            }
        };

        const syncConversationUrl = (sessionId) => {
            try {
                if (typeof window === 'undefined' || !window.location || !window.location.pathname.startsWith('/chat')) {
                    return;
                }
                const url = new URL('/chat', window.location.origin);
                if (sessionId) {
                    url.searchParams.set('conversation', sessionId);
                    sessionStorage.setItem('active_chat_conversation_id', String(sessionId));
                } else {
                    url.searchParams.delete('conversation');
                    sessionStorage.removeItem('active_chat_conversation_id');
                }
                if (window.history && window.history.replaceState) {
                    window.history.replaceState(null, '', url.pathname + (url.search ? url.search : ''));
                }
            } catch (e) {}
        };
        window.__syncConversationUrl = syncConversationUrl;

        const getAlpineChatData = () => {
            const root = document.getElementById('chatRoot');
            if (!root) return null;
            if (window.Alpine && typeof window.Alpine.$data === 'function') {
                try {
                    return window.Alpine.$data(root);
                } catch (e) {}
            }
            return root._x_dataStack && root._x_dataStack[0] ? root._x_dataStack[0] : null;
        };

        const syncEmptyChatState = () => {
            const emptyState = document.getElementById('empty-chat-state');
            const lane = document.getElementById('chat-pending-lane');
            const root = document.getElementById('chatRoot');

            if (!emptyState) {
                return;
            }

            const alpineData = getAlpineChatData();
            const hasPending = alpineData?.hasPendingExchange || (lane && lane.children.length > 0);

            if (root?.dataset?.conversationId || hasPending) {
                emptyState.classList.add('hidden');
                emptyState.style.display = 'none';
                return;
            }

            emptyState.classList.remove('hidden');
            emptyState.style.display = '';
        };

        const clearPendingExchange = () => {
            const lane = document.getElementById('chat-pending-lane');
            if (lane) {
                lane.innerHTML = '';
            }

            const alpineData = getAlpineChatData();
            if (alpineData) {
                alpineData.hasPendingExchange = false;
            }

            syncEmptyChatState();
        };

        const showPendingExchange = (message) => {
            const lane = document.getElementById('chat-pending-lane');
            const root = document.getElementById('chatRoot');
            const emptyState = document.getElementById('empty-chat-state');

            const alpineData = getAlpineChatData();
            if (alpineData) {
                alpineData.hasPendingExchange = true;
            }

            if (emptyState) {
                emptyState.classList.add('hidden');
                emptyState.style.display = 'none';
            }

            if (!lane || !root) {
                return;
            }

            const userInitial = escapeHtml(root.dataset.userInitial || 'U');
            const safeMessage = escapeHtml(message);
            const createdAt = new Date().toISOString();

            lane.innerHTML = `
                <div class="flex items-start gap-4 flex-row-reverse w-full">
                    <div class="w-8 h-8 bg-gray-600 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-sm font-medium text-white">${userInitial}<\/span>
                    <\/div>
                    <div class="max-w-[85%] md:max-w-[500px] flex flex-col items-end">
                        <div class="rounded-2xl px-4 py-3 bg-blue-600 text-white w-fit max-w-full text-left">
                            <p class="text-sm leading-relaxed whitespace-pre-wrap break-words">${safeMessage}<\/p>
                        <\/div>
                        <div class="flex items-center gap-2 mt-1.5 justify-end">
                            <p class="text-xs text-gray-500 message-timestamp" data-created-at="${createdAt}"><\/p>
                        <\/div>
                    <\/div>
                <\/div>
                <div id="streaming-message" class="flex items-start gap-4 w-full">
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm border border-blue-400/20">
                        <img src="/lumi.svg" alt="Lumi" class="w-5.5 h-5.5 object-contain select-none pointer-events-none" />
                    <\/div>
                    <div class="max-w-[85%] md:max-w-[650px] w-full flex flex-col items-start">
                        <div id="streaming-tool-activity" class="hidden mb-2.5"><\/div>
                        <div id="streaming-thinking-indicator" class="flex items-center gap-2 h-8 text-xs text-gray-400 select-none">
                            <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse shrink-0"><\/span>
                            <span class="text-xs font-mono text-gray-400 leading-none inline-block translate-y-[1px]">Thinking...<\/span>
                        <\/div>
                        <div id="streaming-message-bubble" class="hidden bg-gray-800 rounded-2xl px-4 py-3 w-fit max-w-full text-left">
                            <p id="streaming-message-content" class="text-sm leading-relaxed whitespace-pre-wrap break-words text-gray-100"><\/p>
                        <\/div>
                    <\/div>
                <\/div>
            `;

            syncEmptyChatState();
            refreshMessageTimestamps();
            scrollMessagesToBottom();
        };

        window.__entryPreviewCache = window.__entryPreviewCache || new Map();

        const decodeEntryB64 = (b64) => {
            if (!b64) return null;
            try {
                const binString = atob(b64);
                const bytes = Uint8Array.from(binString, m => m.charCodeAt(0));
                return JSON.parse(new TextDecoder().decode(bytes));
            } catch (e) {
                try {
                    return JSON.parse(decodeURIComponent(escape(atob(b64))));
                } catch (e2) {
                    return null;
                }
            }
        };

        window.__getChatAlpine = () => {
            const root = document.getElementById('chatRoot');
            if (!root) return null;
            if (window.Alpine && typeof window.Alpine.$data === 'function') {
                try {
                    return window.Alpine.$data(root);
                } catch(e) {}
            }
            if (root._x_dataStack && root._x_dataStack[0]) {
                return root._x_dataStack[0];
            }
            return null;
        };

        window.__openEntryPreview = (entry, event) => {
            const data = window.__getChatAlpine();
            if (data && typeof data.openEntryPreview === 'function') {
                data.openEntryPreview(entry, event);
            }
        };

        window.__openEntryPreviewByKey = (key, event) => {
            const entry = window.__entryPreviewCache.get(key);
            if (entry) {
                window.__openEntryPreview(entry, event);
            }
        };

        window.__closeEntryPreview = (delay = 180) => {
            const data = window.__getChatAlpine();
            if (data && typeof data.closeEntryPreview === 'function') {
                data.closeEntryPreview(delay);
            }
        };

        // Global delegated hover listeners: guaranteed to fire across all dynamic updates, stream events, and sessions
        document.addEventListener('mouseover', (e) => {
            const trigger = e.target.closest('[data-entry-preview]');
            if (!trigger) return;

            if (e.relatedTarget && trigger.contains(e.relatedTarget)) {
                return;
            }

            const b64 = trigger.getAttribute('data-entry-b64');
            let entry = decodeEntryB64(b64);
            if (!entry) {
                const key = trigger.getAttribute('data-entry-key');
                if (key) {
                    entry = window.__entryPreviewCache.get(key);
                }
            }
            if (entry) {
                window.__openEntryPreview(entry, e);
            }
        }, { passive: true });

        document.addEventListener('mouseout', (e) => {
            const trigger = e.target.closest('[data-entry-preview]');
            if (!trigger) return;

            if (!e.relatedTarget || !trigger.contains(e.relatedTarget)) {
                window.__closeEntryPreview(180);
            }
        }, { passive: true });

        let streamedRawText = '';
        let streamedEntries = [];

        const escapeRegex = (string) => {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        };

        const formatContentWithPills = (rawText, entries) => {
            if (!rawText) return '';

            // Clean up em-dashes and double dashes
            let cleaned = rawText.replace(/\s*[—–]\s*/gu, ', ').replace(/\s*--\s*/g, ', ');
            let escaped = escapeHtml(cleaned);

            if (entries && entries.length) {
                const targets = [];
                for (const entry of entries) {
                    if (!entry || !entry.title) continue;
                    const entryKey = 'entry_' + entry.id;
                    window.__entryPreviewCache.set(entryKey, entry);

                    const candidates = new Set();
                    candidates.add(entry.title);

                    const cleanTitle = entry.title.replace(/^[\p{Emoji}\p{So}\p{Sk}\p{Sm}\p{Mn}\p{Me}\p{C}\s\p{P}]+|[\p{Emoji}\p{So}\p{Sk}\p{Sm}\p{Mn}\p{Me}\p{C}\s\p{P}]+$/gu, '').trim();
                    if (cleanTitle && cleanTitle.length >= 3) {
                        candidates.add(cleanTitle);
                        const subparts = cleanTitle.split(/\s+[—–\-:]\s+/);
                        if (subparts[0] && subparts[0].trim().length >= 3) {
                            candidates.add(subparts[0].trim());
                        }
                    }

                    for (const cand of candidates) {
                        targets.push({
                            title: cand,
                            entryKey: entryKey,
                            length: cand.length,
                            entry: entry,
                        });
                    }
                }

                targets.sort((a, b) => b.length - a.length);
                const matchedEntryIds = new Set();

                for (const target of targets) {
                    if (matchedEntryIds.has(target.entry.id)) {
                        continue;
                    }

                    const titleEsc = escapeRegex(escapeHtml(target.title));
                    const pattern = new RegExp('(?:[\\*_"\']|&quot;)*' + titleEsc + '(?:\'s|’s)?(?:[\\*_"\']|&quot;)*', 'i');

                    if (pattern.test(escaped)) {
                        const b64 = btoa(unescape(encodeURIComponent(JSON.stringify(target.entry))));
                        const pill = `<button type="button" ` +
                            `data-entry-preview="true" ` +
                            `data-entry-b64="${b64}" ` +
                            `data-entry-key="${target.entryKey}" ` +
                            `onclick="window.location.href = '/entries/${target.entry.id}'" ` +
                            `class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-blue-500/15 hover:bg-blue-500/25 text-blue-300 hover:text-blue-200 border border-blue-500/30 transition-all font-medium cursor-pointer align-baseline text-xs mx-0.5 group shadow-sm">` +
                            `<svg class="w-3.5 h-3.5 text-blue-400 group-hover:scale-110 transition-transform flex-shrink-0" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><path class="transition-transform group-hover:translate-x-0.5" d="M7 8h8"/><path class="transition-transform group-hover:-translate-x-0.5" d="M7 12h10"/><path class="transition-transform group-hover:translate-x-0.5" d="M7 16h6"/><\/svg>` +
                            `<span>${escapeHtml(target.entry.title)}<\/span>` +
                            `<\/button>`;

                        escaped = escaped.replace(pattern, pill);
                        matchedEntryIds.add(target.entry.id);
                    }
                }
            }

            // Format bullet lines: "* " or "- "
            const lines = escaped.split('\n');
            const formattedLines = lines.map(line => {
                const trimmed = line.trim();
                const m = trimmed.match(/^[\*\-]\s+(.+)$/);
                if (m) {
                    return `<span class="inline-flex items-start gap-2 my-0.5"><span class="w-1.5 h-1.5 rounded-full bg-blue-400 mt-2 shrink-0"><\/span><span>${m[1]}<\/span><\/span>`;
                }
                return line;
            });
            escaped = formattedLines.join('\n');

            // Format bold: **text**
            escaped = escaped.replace(/\*\*([^*]+)\*\*/gu, '<strong class="font-semibold text-white">$1<\/strong>');

            // Format italics: *text*
            escaped = escaped.replace(/(?<=\s|^)\*([^*]+)\*(?=\s|$|[,.;:!?])/gu, '<em class="italic text-gray-200">$1<\/em>');

            // Clean lone asterisks
            escaped = escaped.replace(/(?<=\s|^)\*(?=\s|$)/gu, '');

            return escaped;
        };

        const renderStreamingToElement = () => {
            const contentEl = document.getElementById('streaming-message-content');
            if (!contentEl) return;
            contentEl.innerHTML = formatContentWithPills(streamedRawText, streamedEntries);
        };

        let liveStreamStartTime = null;
        let liveStopwatchInterval = null;
        let completedToolStepsCount = 0;

        const startLiveStopwatch = () => {
            if (liveStreamStartTime) return;
            liveStreamStartTime = Date.now();
            liveStopwatchInterval = setInterval(() => {
                const el = document.getElementById('streaming-stopwatch-text');
                if (el && liveStreamStartTime) {
                    const sec = Math.max(1, Math.floor((Date.now() - liveStreamStartTime) / 1000));
                    el.textContent = `Working for ${sec}s`;
                }
            }, 500);
        };

        const stopLiveStopwatch = (totalSteps) => {
            if (liveStopwatchInterval) {
                clearInterval(liveStopwatchInterval);
                liveStopwatchInterval = null;
            }
            const indicator = document.getElementById('streaming-working-indicator');
            if (indicator && liveStreamStartTime) {
                const totalSec = ((Date.now() - liveStreamStartTime) / 1000).toFixed(1);
                indicator.innerHTML = `
                    <span class="w-2 h-2 rounded-full bg-emerald-400"><\/span>
                    <span class="text-xs text-gray-400 font-mono">Worked for ${totalSec}s ${totalSteps ? '· ' + totalSteps + (totalSteps === 1 ? ' command' : ' commands') : ''}<\/span>
                `;
            }
        };

        const resetStreamingUi = () => {
            stopLiveStopwatch();
            liveStreamStartTime = null;
            completedToolStepsCount = 0;
            streamedRawText = '';
            streamedEntries = [];
            const thinking = document.getElementById('streaming-thinking-indicator');
            const bubble = document.getElementById('streaming-message-bubble');
            const content = document.getElementById('streaming-message-content');
            const toolContainer = document.getElementById('streaming-tool-activity');

            if (thinking) {
                thinking.classList.remove('hidden');
            }

            if (bubble) {
                bubble.classList.add('hidden');
            }

            if (content) {
                content.innerHTML = '';
            }

            if (toolContainer) {
                toolContainer.innerHTML = '';
                toolContainer.classList.add('hidden');
            }
        };

        const handleToolEvent = (toolData) => {
            const container = document.getElementById('streaming-tool-activity');
            if (!container || !toolData) return;

            container.classList.remove('hidden');

            // Scaffold container if not yet built
            let rail = document.getElementById('streaming-tool-rail');
            if (!rail) {
                container.innerHTML = `
                    <div class="mb-2">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/[0.04] border border-white/10 text-xs text-gray-400 font-mono mb-2">
                            <span class="text-blue-400">&gt;_<\/span>
                            <span id="streaming-tool-header-count">Running commands<\/span>
                        <\/div>
                        <div id="streaming-tool-rail" class="ml-2.5 pl-4 border-l border-white/15 space-y-2 py-1"><\/div>
                        <div id="streaming-working-indicator" class="flex items-center gap-2 text-xs text-gray-400 font-mono pl-3 pt-2">
                            <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"><\/span>
                            <span id="streaming-stopwatch-text">Working for 1s<\/span>
                        <\/div>
                    <\/div>
                `;
                rail = document.getElementById('streaming-tool-rail');
            }

            startLiveStopwatch();

            if (toolData.status === 'running') {
                let runningStep = document.getElementById('streaming-tool-running-step');
                if (!runningStep) {
                    runningStep = document.createElement('div');
                    runningStep.id = 'streaming-tool-running-step';
                    runningStep.className = 'relative';
                    rail.appendChild(runningStep);
                }

                let runningLabel = 'Running action...';
                if (toolData.tool === 'search_journal') {
                    const q = toolData.args?.query || '';
                    runningLabel = q ? `Searching memories: "${q}"...` : 'Searching memories...';
                } else if (toolData.tool === 'get_user_stats') {
                    runningLabel = 'Checking journaling stats & streak...';
                } else if (toolData.tool === 'list_tags') {
                    runningLabel = 'Checking journal tags...';
                } else if (toolData.tool === 'draft_entry') {
                    runningLabel = `Saving memo "${toolData.args?.title || ''}"...`;
                }

                runningStep.innerHTML = `
                    <div class="absolute -left-[21px] top-1.5 w-2.5 h-2.5 rounded-full bg-[#0d1424] border-2 border-blue-400 animate-pulse"><\/div>
                    <div class="flex items-center gap-2 px-2 py-1 rounded-lg text-xs font-mono text-blue-300">
                        <span class="text-blue-400">&gt;_<\/span>
                        <span class="animate-pulse">${escapeHtml(runningLabel)}<\/span>
                    <\/div>
                `;
            } else if (toolData.status === 'done') {
                const runningStep = document.getElementById('streaming-tool-running-step');
                if (runningStep) {
                    runningStep.remove();
                }

                if (toolData.tool === 'search_journal' && Array.isArray(toolData.result?.entries)) {
                    const existingIds = new Set(streamedEntries.map(e => e.id));
                    for (const entry of toolData.result.entries) {
                        if (entry && entry.id && !existingIds.has(entry.id)) {
                            streamedEntries.push(entry);
                            existingIds.add(entry.id);
                        }
                    }
                    streamedEntries.forEach(entry => {
                        if (entry && entry.id) {
                            window.__entryPreviewCache.set('entry_' + entry.id, entry);
                        }
                    });
                    if (streamedRawText) {
                        renderStreamingToElement();
                    }
                }

                completedToolStepsCount++;
                const headerCount = document.getElementById('streaming-tool-header-count');
                if (headerCount) {
                    headerCount.textContent = `Ran ${completedToolStepsCount} ${completedToolStepsCount === 1 ? 'command' : 'commands'}`;
                }

                let stepTitle = `Ran command: ${toolData.tool}`;
                if (toolData.tool === 'search_journal') {
                    stepTitle = `Ran search: "${toolData.args?.query || ''}"`;
                } else if (toolData.tool === 'get_user_stats') {
                    stepTitle = 'Ran check: journaling stats & streak';
                } else if (toolData.tool === 'list_tags') {
                    stepTitle = 'Ran check: journal tags';
                } else if (toolData.tool === 'draft_entry') {
                    stepTitle = `Ran command: save memo "${toolData.args?.title || ''}"`;
                }

                let outputHtml = '';
                if (Array.isArray(toolData.result?.entries)) {
                    const entriesHtml = toolData.result.entries.map(resEntry => {
                        const b64 = btoa(unescape(encodeURIComponent(JSON.stringify(resEntry))));
                        return `
                            <div data-entry-preview="true"
                                 data-entry-b64="${b64}"
                                 onclick="window.location.href = '/entries/${resEntry.id}'"
                                 class="bg-white/5 p-2 rounded-lg border border-white/5 hover:border-blue-500/40 hover:bg-white/[0.08] transition-all cursor-pointer group">
                                <div class="flex items-center justify-between text-xs gap-2">
                                    <span class="font-medium text-white group-hover:text-blue-300 transition-colors line-clamp-1 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-blue-400 opacity-70 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><path class="transition-transform group-hover:translate-x-0.5" d="M7 8h8"/><path class="transition-transform group-hover:-translate-x-0.5" d="M7 12h10"/><path class="transition-transform group-hover:translate-x-0.5" d="M7 16h6"/><\/svg>
                                        ${escapeHtml(resEntry.title)}
                                    <\/span>
                                    <span class="text-gray-500 text-[10px] font-mono">${escapeHtml(resEntry.date || '')}<\/span>
                                <\/div>
                                <p class="text-gray-400 text-xs mt-1 italic line-clamp-1">"${escapeHtml(resEntry.snippet || '')}"<\/p>
                            <\/div>
                        `;
                    }).join('');
                    outputHtml = `<div class="space-y-1.5 font-sans">${entriesHtml}<\/div>`;
                } else if (Array.isArray(toolData.result?.tags)) {
                    const tagsHtml = toolData.result.tags.map(tag => `
                        <span class="inline-flex items-center gap-1 bg-white/5 px-2 py-0.5 rounded text-gray-300 text-xs border border-white/5">
                            <span>#${escapeHtml(tag.name)}<\/span>
                            <span class="text-[10px] text-gray-500 font-mono">(${tag.entries_count})<\/span>
                        <\/span>
                    `).join('');
                    outputHtml = `<div class="flex flex-wrap gap-1.5 font-sans">${tagsHtml}<\/div>`;
                } else if (toolData.result?.status === 'not_found') {
                    outputHtml = `<div class="p-2 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-300 text-[11px] font-mono">${escapeHtml(toolData.result.message || '0 memories matched. Query reformulated.')}<\/div>`;
                } else if (toolData.result) {
                    outputHtml = `<div class="p-2 rounded-lg bg-black/60 border border-white/5 font-mono text-[11px] text-gray-300 overflow-x-auto"><pre class="whitespace-pre-wrap break-all">${escapeHtml(JSON.stringify(toolData.result, null, 2))}<\/pre><\/div>`;
                }

                const argsHtml = toolData.args && Object.keys(toolData.args).length > 0
                    ? `<div>
                        <span class="text-[10px] font-mono text-gray-500 uppercase tracking-wider block mb-1">Command Parameters<\/span>
                        <div class="p-2 rounded-lg bg-black/60 border border-white/5 font-mono text-[11px] text-blue-300 overflow-x-auto">
                            <pre class="whitespace-pre-wrap break-all">${escapeHtml(JSON.stringify(toolData.args, null, 2))}<\/pre>
                        <\/div>
                       <\/div>`
                    : '';

                const durationBadge = toolData.duration_ms
                    ? `<span class="text-[10px] text-gray-500 font-mono">${toolData.duration_ms}ms<\/span>`
                    : '';

                const doneItem = document.createElement('div');
                doneItem.className = 'relative group';
                doneItem.innerHTML = `
                    <div class="absolute -left-[21px] top-1.5 w-2.5 h-2.5 rounded-full bg-[#0d1424] border-2 border-blue-400"><\/div>
                    <button type="button" 
                            onclick="const d = this.nextElementSibling; d.classList.toggle('hidden'); this.querySelector('.chevron-icon')?.classList.toggle('rotate-180');"
                            class="w-full text-left flex items-center justify-between gap-2 px-2 py-1 rounded-lg hover:bg-white/[0.05] transition-colors cursor-pointer select-none">
                        <span class="inline-flex items-center gap-1.5 text-xs text-gray-300 font-mono">
                            <span class="text-blue-400/90">&gt;_<\/span>
                            <span class="text-gray-300 group-hover:text-white transition-colors">${escapeHtml(stepTitle)}<\/span>
                        <\/span>
                        <div class="flex items-center gap-1.5 shrink-0">
                            ${durationBadge}
                            <svg class="chevron-icon w-3 h-3 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            <\/svg>
                        <\/div>
                    <\/button>
                    <div class="hidden mt-1 mb-2 rounded-xl border border-white/10 bg-black/40 p-2.5 space-y-2 text-xs">
                        ${argsHtml}
                        ${outputHtml ? `<div><span class="text-[10px] font-mono text-gray-500 uppercase tracking-wider block mb-1">Output<\/span>${outputHtml}<\/div>` : ''}
                    <\/div>
                `;
                rail.appendChild(doneItem);
            }
            scrollMessagesToBottom();
        };

        const appendStreamingText = (text) => {
            const contentEl = document.getElementById('streaming-message-content');
            const bubbleEl = document.getElementById('streaming-message-bubble');
            const thinkingEl = document.getElementById('streaming-thinking-indicator');

            if (!contentEl || !text) {
                return;
            }

            if (bubbleEl && bubbleEl.classList.contains('hidden')) {
                thinkingEl?.classList.add('hidden');
                bubbleEl.classList.remove('hidden');
            }

            streamedRawText += text;
            renderStreamingToElement();
            scrollMessagesToBottom();
        };

        const showStreamingError = (errorMessage) => {
            const streamingMessage = document.getElementById('streaming-message');
            if (streamingMessage) {
                streamingMessage.innerHTML = `
                    <div class="w-8 h-8 bg-red-600 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        <\/svg>
                    <\/div>
                    <div class="max-w-[300px] md:max-w-[400px] lg:max-w-[500px]">
                        <div class="rounded-2xl px-4 py-3 bg-red-900/60 border border-red-500/40 text-red-200">
                            <p class="text-sm leading-relaxed whitespace-pre-wrap break-words">${escapeHtml(errorMessage)}<\/p>
                        <\/div>
                    <\/div>
                `;
            }
        };

        window.setChatPrompt = (promptText) => {
            const form = document.getElementById('chat-message-form');
            const textarea = form?.querySelector('textarea');
            if (textarea) {
                textarea.value = promptText;
                textarea.style.height = 'auto';
                textarea.style.height = `${Math.min(textarea.scrollHeight, 180)}px`;
                textarea.focus();
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
            }
        };

        const finishStreaming = async (component, conversationId, failed = false, errorMessage = null, userMessage = null) => {
            setFormBusy(false);
            stopLiveStopwatch(completedToolStepsCount);

            if (failed) {
                const finalError = errorMessage || 'Sorry, I encountered an error. Please try again.';
                showStreamingError(finalError);
                try {
                    await component.call('failStreamingResponse', conversationId, finalError, userMessage);
                    clearPendingExchange();
                } catch (e) {
                    console.error('Failed to sync error with Livewire', e);
                }
                return;
            }

            renderStreamingToElement();
            await component.call('completeStreamingResponse', conversationId);
            clearPendingExchange();
        };

        const startStreamingResponse = async ({ conversationId, message }) => {
            const endpoint = document.getElementById('chatRoot')?.dataset.streamEndpoint;
            const component = getChatComponent();

            if (!endpoint || !component || !conversationId || !message) {
                await finishStreaming(component, conversationId, true, null, message);
                return;
            }

            if (activeStreamController) {
                activeStreamController.abort();
            }

            activeStreamController = new AbortController();
            let streamStarted = false;

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'text/event-stream',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-XSRF-TOKEN': getCsrfToken(),
                    },
                    credentials: 'same-origin',
                    signal: activeStreamController.signal,
                    body: JSON.stringify({
                        conversation_id: conversationId,
                        message: message,
                    }),
                });

                resetStreamingUi();

                if (!response.ok) {
                    let errorMessage = 'Sorry, I encountered an error. Please try again.';

                    try {
                        const errorBody = await response.json();
                        if (errorBody?.message) {
                            errorMessage = errorBody.message;
                        }
                    } catch (error) {
                        // Keep default error message.
                    }

                    activeStreamController = null;
                    await finishStreaming(component, conversationId, true, errorMessage, message);
                    return;
                }

                const reader = response.body?.getReader();
                if (!reader) {
                    activeStreamController = null;
                    await finishStreaming(component, conversationId, true, 'Sorry, I encountered an error. Please try again.', message);
                    return;
                }

                streamStarted = true;
                const decoder = new TextDecoder();
                let buffer = '';
                let receivedText = false;
                let receivedDone = false;

                let streamWatchdog = setTimeout(() => {
                    if (activeStreamController) {
                        activeStreamController.abort();
                    }
                }, 50000);

                const resetWatchdog = () => {
                    clearTimeout(streamWatchdog);
                    streamWatchdog = setTimeout(() => {
                        if (activeStreamController) {
                            activeStreamController.abort();
                        }
                    }, 35000);
                };

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) {
                        break;
                    }

                    resetWatchdog();
                    buffer += decoder.decode(value, { stream: true });

                    const parsed = parseSseChunk(buffer);
                    buffer = parsed.remainder;

                    for (const event of parsed.events) {
                        resetWatchdog();
                        if (event.type === 'tool' && event.data) {
                            handleToolEvent(event.data);
                        } else if (event.type === 'chunk' && event.data?.text) {
                            receivedText = true;
                            appendStreamingText(event.data.text);
                        } else if (event.type === 'done') {
                            clearTimeout(streamWatchdog);
                            receivedDone = true;
                            activeStreamController = null;
                            await finishStreaming(
                                component,
                                event.data?.conversationId ?? conversationId
                            );
                            return;
                        } else if (event.type === 'error') {
                            clearTimeout(streamWatchdog);
                            activeStreamController = null;
                            await finishStreaming(
                                component,
                                conversationId,
                                true,
                                event.data?.message ?? 'Sorry, I encountered an error. Please try again.',
                                message
                            );
                            return;
                        }
                    }
                }

                clearTimeout(streamWatchdog);
                activeStreamController = null;

                if (! receivedDone) {
                    await finishStreaming(
                        component,
                        conversationId,
                        true,
                        receivedText
                            ? 'The response was interrupted before it finished. Please try again.'
                            : 'The response timed out. Please try again.',
                        message
                    );
                    return;
                }

                await finishStreaming(component, conversationId);
            } catch (error) {
                if (error?.name === 'AbortError') {
                    clearPendingExchange();
                    setFormBusy(false);
                    return;
                }

                activeStreamController = null;
                await finishStreaming(component, conversationId, true, 'Sorry, I encountered an error. Please try again.', message);
            }
        };

        const handleChatSubmit = async (event) => {
            const form = event.target.closest('form[data-client-submit="true"]');
            if (!form) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            const textarea = form.querySelector('textarea');
            const message = textarea?.value.trim();

            if (!message || textarea?.disabled) {
                return;
            }

            const component = getChatComponent();
            const root = document.getElementById('chatRoot');

            if (!component || !root) {
                return;
            }

            textarea.value = '';
            textarea.style.height = 'auto';
            setFormBusy(true);
            showPendingExchange(message);

            if (root.dataset.hasApiKey === '0') {
                showStreamingError('Gemini API key is missing. Please configure your key in Settings > Account > API Integration to chat with Lumi.');
                setFormBusy(false);
                return;
            }

            let conversationId = root.dataset.conversationId;

            try {
                if (!conversationId) {
                    conversationId = await component.call('ensureSessionForMessage');

                    if (conversationId) {
                        root.dataset.conversationId = String(conversationId);
                        const alpineData = getAlpineChatData();
                        if (alpineData) {
                            alpineData.activeSessionId = String(conversationId);
                            alpineData.hasPendingExchange = true;
                        }
                        syncConversationUrl(conversationId);
                    }
                }

                if (!conversationId) {
                    throw new Error('Unable to start conversation.');
                }

                await startStreamingResponse({
                    conversationId: Number(conversationId),
                    message,
                });
            } catch (error) {
                await finishStreaming(
                    component,
                    conversationId ? Number(conversationId) : null,
                    true,
                    'Sorry, I encountered an error. Please try again.',
                    message
                );
            }
        };

        document.addEventListener('submit', (e) => {
            const form = e.target.closest('form[data-client-submit="true"]');
            if (form) {
                handleChatSubmit(e);
            }
        });

        const setupLivewireHooks = () => {
            if (typeof Livewire === 'undefined') {
                return;
            }

            if (!window.__chatLivewireHooksRegistered) {
                window.__chatLivewireHooksRegistered = true;

                Livewire.on('messages-updated', () => {
                    scrollMessagesToBottom();
                    refreshMessageTimestamps();
                });

                Livewire.on('session-selected', (event) => {
                    const root = document.getElementById('chatRoot');
                    const sessionId = event?.sessionId || (typeof event === 'number' || typeof event === 'string' ? event : null);
                    if (root && sessionId) {
                        root.dataset.conversationId = String(sessionId);
                        const alpineData = getAlpineChatData();
                        if (alpineData) {
                            alpineData.activeSessionId = String(sessionId);
                        }
                    }
                    syncConversationUrl(sessionId);
                });

                Livewire.on('session-cleared', () => {
                    const root = document.getElementById('chatRoot');
                    if (root) {
                        root.dataset.conversationId = '';
                        const alpineData = getAlpineChatData();
                        if (alpineData) {
                            alpineData.activeSessionId = '';
                            alpineData.hasPendingExchange = false;
                        }
                    }
                    syncConversationUrl(null);
                });

                if (Livewire.hook) {
                    Livewire.hook('morph.updated', () => {
                        syncEmptyChatState();
                        refreshMessageTimestamps();
                    });
                    Livewire.hook('commit', () => {
                        syncEmptyChatState();
                        refreshMessageTimestamps();
                    });
                }
            }

            const activeRoot = document.getElementById('chatRoot');
            if (activeRoot && activeRoot.dataset.conversationId && window.location.pathname.startsWith('/chat')) {
                syncConversationUrl(activeRoot.dataset.conversationId);
            }

            refreshMessageTimestamps();
            syncEmptyChatState();
            scrollMessagesToBottom();
        };

        if (window.Livewire) {
            setupLivewireHooks();
        } else {
            document.addEventListener('livewire:init', setupLivewireHooks);
        }

        if (!window.__chatNavigatedHandlerBound) {
            window.__chatNavigatedHandlerBound = true;
            document.addEventListener('livewire:navigated', () => {
                const root = document.getElementById('chatRoot');
                if (root) {
                    refreshMessageTimestamps();
                    syncEmptyChatState();
                    scrollMessagesToBottom();
                    if (root.dataset.conversationId && window.location.pathname.startsWith('/chat')) {
                        syncConversationUrl(root.dataset.conversationId);
                    }
                }
            });
        }
    })();
</script>
</div>
