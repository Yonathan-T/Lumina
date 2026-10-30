<div id="chatRoot" class="relative flex h-full max-h-full w-full bg-gradient-dark gap-0 md:gap-4 overflow-hidden overscroll-none"
    x-data="{
        showDeleteModal: false,
        sessionToDelete: null,
        sessionTitleToDelete: '',
        deletedSessionIds: [],
        activeSessionId: '{{ $activeSession['id'] ?? '' }}',
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
                    const cardWidth = 352;
                    const cardHeight = 220;
                    let left = rect.left;
                    if (left + cardWidth > window.innerWidth - 16) {
                        left = window.innerWidth - cardWidth - 16;
                    }
                    if (left < 16) {
                        left = 16;
                    }
                    let top = rect.bottom + 6;
                    if (top + cardHeight > window.innerHeight - 16 && rect.top > cardHeight + 16) {
                        top = rect.top - cardHeight - 6;
                    }
                    if (top < 16) {
                        top = 16;
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
                @click="activeSessionId = ''; closeMobile(); if (window.__syncConversationUrl) window.__syncConversationUrl(null);"
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
                                        <div class="w-8 h-8 {{ isset($message['isError']) ? 'bg-red-600' : 'bg-blue-600' }} rounded-full flex items-center justify-center flex-shrink-0">
                                            @if(isset($message['isError']))
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @else
                                                <x-icon name="flash-outline" class="w-4 h-4 text-white" />
                                            @endif
                                        </div>
                                    @else
                                        <div class="w-8 h-8 bg-gray-600 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-sm font-medium text-white">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                                        </div>
                                    @endif

                                    <div class="max-w-[300px] md:max-w-[400px] lg:max-w-[500px]">
                                        <div
                                            class="rounded-2xl px-4 py-3 {{ $message['isAi'] ? (isset($message['isError']) ? 'bg-red-900/60 border border-red-500/40 text-red-200' : 'bg-gray-800 text-gray-100') : 'bg-blue-600 text-white' }}">
                                            <p class="text-sm leading-relaxed whitespace-pre-wrap break-words">{!! $this->renderMessageContent($message) !!}</p>
                                        </div>

                                        @if($message['isAi'] && !empty($message['metadata']['tools_used']))
                                            <div class="mt-2 space-y-1.5" x-data="{ open: false }">
                                                @foreach($message['metadata']['tools_used'] as $tool)
                                                    <div class="rounded-xl border border-white/10 bg-white/[0.03] overflow-hidden text-xs">
                                                        <button 
                                                            type="button" 
                                                            @click="open = !open" 
                                                            class="w-full px-3 py-1.5 flex items-center justify-between text-left hover:bg-white/[0.06] transition-colors cursor-pointer select-none">
                                                            <span class="inline-flex items-center gap-1.5 text-gray-300">
                                                                @if(($tool['tool'] ?? '') === 'search_journal')
                                                                    <svg class="w-3.5 h-3.5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                                @elseif(($tool['tool'] ?? '') === 'get_user_stats')
                                                                    <svg class="w-3.5 h-3.5 text-purple-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                                                @elseif(($tool['tool'] ?? '') === 'list_tags')
                                                                    <svg class="w-3.5 h-3.5 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                                                @else
                                                                    <svg class="w-3.5 h-3.5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                                @endif
                                                                <span class="font-normal text-gray-300">{{ $tool['label'] ?? $tool['tool'] }}</span>
                                                            </span>
                                                            <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                            </svg>
                                                        </button>
                                                        <div x-show="open" x-cloak class="px-3 py-2 border-t border-white/5 bg-black/30 text-[11px] text-gray-400 space-y-1.5 font-mono">
                                                            @if(!empty($tool['args']))
                                                                <div>
                                                                    <span class="text-gray-500 uppercase tracking-wider text-[10px]">Parameters:</span>
                                                                    <pre class="whitespace-pre-wrap break-all text-gray-300 font-sans mt-0.5">{{ json_encode($tool['args'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                                </div>
                                                            @endif
                                                            @if(!empty($tool['result']))
                                                                <div>
                                                                    @if(isset($tool['result']['entries']) && is_array($tool['result']['entries']))
                                                                        <div class="flex items-center justify-between mb-1">
                                                                            <span class="text-gray-500 uppercase tracking-wider text-[10px]">Connected Memories:</span>
                                                                            <span class="text-[9px] text-blue-400 font-mono">Semantic & Keyword</span>
                                                                        </div>
                                                                        <div class="mt-1 space-y-1.5 font-sans">
                                                                            @foreach($tool['result']['entries'] as $resEntry)
                                                                                @php
                                                                                    $resEntryB64 = base64_encode(json_encode($resEntry, JSON_UNESCAPED_UNICODE));
                                                                                @endphp
                                                                                <div 
                                                                                    data-entry-preview="true"
                                                                                    data-entry-b64="{{ $resEntryB64 }}"
                                                                                    onclick="window.location.href = '{{ route('entries.show', $resEntry['id']) }}'"
                                                                                    class="bg-white/5 p-2.5 rounded-lg border border-white/5 hover:border-blue-500/40 hover:bg-white/[0.08] transition-all cursor-pointer group">
                                                                                    <div class="flex items-center justify-between text-xs gap-2">
                                                                                        <span class="font-medium text-white group-hover:text-blue-300 transition-colors line-clamp-1 flex items-center gap-1.5">
                                                                                            <svg class="w-3.5 h-3.5 text-blue-400 opacity-70 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><path class="transition-transform group-hover:translate-x-0.5" d="M7 8h8"/><path class="transition-transform group-hover:-translate-x-0.5" d="M7 12h10"/><path class="transition-transform group-hover:translate-x-0.5" d="M7 16h6"/></svg>
                                                                                            {{ $resEntry['title'] }}
                                                                                        </span>
                                                                                        <div class="flex items-center gap-1.5 flex-shrink-0">
                                                                                            @if(($resEntry['match_type'] ?? '') === 'exact_and_concept')
                                                                                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-500/15 text-emerald-300 border border-emerald-500/20 font-mono">Exact & Theme</span>
                                                                                            @elseif(($resEntry['match_type'] ?? '') === 'keyword_match')
                                                                                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-amber-500/15 text-amber-300 border border-amber-500/20 font-mono">Exact Match</span>
                                                                                            @elseif(($resEntry['match_type'] ?? '') === 'conceptual_similarity')
                                                                                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-blue-500/15 text-blue-300 border border-blue-500/20 font-mono">Similar Theme</span>
                                                                                            @endif
                                                                                            <span class="text-gray-500 text-[10px]">{{ $resEntry['date'] }}</span>
                                                                                        </div>
                                                                                    </div>
                                                                                    <p class="text-gray-400 text-xs mt-1 italic line-clamp-2">"{{ $resEntry['snippet'] }}"</p>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    @elseif(isset($tool['result']['tags']) && is_array($tool['result']['tags']))
                                                                        <span class="text-gray-500 uppercase tracking-wider text-[10px]">Tags Found:</span>
                                                                        <div class="mt-1 font-sans flex flex-wrap gap-1.5">
                                                                            @foreach($tool['result']['tags'] as $resTag)
                                                                                <span class="inline-flex items-center gap-1 bg-white/5 px-2 py-0.5 rounded text-gray-300 text-xs border border-white/5">
                                                                                    <span>#{{ $resTag['name'] }}</span>
                                                                                    <span class="text-[10px] text-gray-500 font-mono">({{ $resTag['entries_count'] }})</span>
                                                                                </span>
                                                                            @endforeach
                                                                        </div>
                                                                    @else
                                                                        <pre class="whitespace-pre-wrap break-all text-gray-300 font-sans mt-0.5">{{ json_encode($tool['result'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="flex items-center gap-2 mt-2 {{ $message['isAi'] ? '' : 'justify-end' }}">
                                            <p class="text-xs text-gray-500 message-timestamp" data-created-at="{{ $message['createdAt'] }}"></p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endif

                <!-- Greeting Content Area when no active session or when deleted instantly -->
                <div id="empty-chat-state"
                    x-show="!activeSessionId || deletedSessionIds.includes(String(activeSessionId))"
                    @if($activeSession) style="display: none;" @endif
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
            <div class="bg-[#090e1a]/95 backdrop-blur-xl border border-white/10 rounded-2xl shadow-2xl max-w-xl w-full max-h-[85vh] flex flex-col overflow-hidden ring-1 ring-white/10"
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
                    <div class="rounded-xl border border-white/10 bg-white/[0.02] p-4 shadow-sm">
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
         class="fixed z-50 w-88 max-w-[92vw] p-4 rounded-2xl bg-[#090e1a]/85 backdrop-blur-xl border border-white/10 shadow-2xl shadow-black/80 ring-1 ring-white/10 text-left pointer-events-auto flex flex-col"
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
            <p class="text-xs text-gray-200/95 leading-relaxed italic bg-white/[0.03] p-2.5 rounded-lg border border-white/5 whitespace-pre-wrap font-sans" x-text="previewEntry?.snippet"></p>
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
                const url = new URL(window.location.href);
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

        const syncEmptyChatState = () => {
            const emptyState = document.getElementById('empty-chat-state');
            const lane = document.getElementById('chat-pending-lane');
            const root = document.getElementById('chatRoot');

            if (!emptyState) {
                return;
            }

            if (root?.dataset?.conversationId || (lane && lane.children.length > 0)) {
                emptyState.remove();
                return;
            }

            emptyState.classList.remove('hidden');
        };

        const clearPendingExchange = () => {
            const lane = document.getElementById('chat-pending-lane');
            if (lane) {
                lane.innerHTML = '';
            }

            syncEmptyChatState();
        };

        const showPendingExchange = (message) => {
            const lane = document.getElementById('chat-pending-lane');
            const root = document.getElementById('chatRoot');
            const emptyState = document.getElementById('empty-chat-state');

            if (emptyState) {
                emptyState.remove();
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
                    <div class="max-w-[300px] md:max-w-[400px] lg:max-w-[500px]">
                        <div class="rounded-2xl px-4 py-3 bg-blue-600 text-white">
                            <p class="text-sm leading-relaxed whitespace-pre-wrap break-words">${safeMessage}<\/p>
                        <\/div>
                        <div class="flex items-center gap-2 mt-2 justify-end">
                            <p class="text-xs text-gray-500 message-timestamp" data-created-at="${createdAt}"><\/p>
                        <\/div>
                    <\/div>
                <\/div>
                <div id="streaming-message" class="flex items-start gap-4 w-full">
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                        <\/svg>
                    <\/div>
                    <div class="max-w-[300px] md:max-w-[400px] lg:max-w-[500px]">
                        <div class="bg-gray-800 rounded-2xl px-4 py-3 min-h-[44px] flex items-center">
                            <div id="streaming-message-dots" class="flex space-x-1">
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce"><\/div>
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.1s"><\/div>
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.2s"><\/div>
                            <\/div>
                            <p id="streaming-message-content" class="hidden text-sm leading-relaxed whitespace-pre-wrap break-words text-gray-100"><\/p>
                        <\/div>
                        <div id="streaming-tool-activity" class="hidden mt-2 space-y-1.5"><\/div>
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
            let escaped = escapeHtml(rawText);
            if (!entries || !entries.length) return escaped;

            const targets = [];
            for (const entry of entries) {
                if (!entry || !entry.title) continue;
                const entryKey = 'entry_' + entry.id;
                window.__entryPreviewCache.set(entryKey, entry);

                targets.push({
                    title: entry.title,
                    entryKey: entryKey,
                    length: entry.title.length,
                    entry: entry,
                });

                const cleanTitle = entry.title.replace(/^[\p{Emoji}\s\p{P}]+|[\p{Emoji}\s\p{P}]+$/gu, '').trim();
                if (cleanTitle && cleanTitle !== entry.title && cleanTitle.length >= 3) {
                    targets.push({
                        title: cleanTitle,
                        entryKey: entryKey,
                        length: cleanTitle.length,
                        entry: entry,
                    });
                }
            }

            targets.sort((a, b) => b.length - a.length);

            for (const target of targets) {
                const titleEsc = escapeRegex(escapeHtml(target.title));
                const pattern = new RegExp('(?:\\*|_|&quot;|")*' + titleEsc + '(?:\'s|’s)?(?:\\*|_|&quot;|")*', 'i');

                const b64 = btoa(unescape(encodeURIComponent(JSON.stringify(target.entry))));
                const pill = `<button type="button" ` +
                    `data-entry-preview="true" ` +
                    `data-entry-b64="${b64}" ` +
                    `data-entry-key="${target.entryKey}" ` +
                    `onclick="window.location.href = '/entries/${target.entry.id}'" ` +
                    `class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-blue-500/15 hover:bg-blue-500/25 text-blue-300 hover:text-blue-200 border border-blue-500/30 transition-all font-medium cursor-pointer align-baseline text-xs mx-0.5 group shadow-sm">` +
                    `<svg class="w-3.5 h-3.5 text-blue-400 group-hover:scale-110 transition-transform flex-shrink-0" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><path class="transition-transform group-hover:translate-x-0.5" d="M7 8h8"/><path class="transition-transform group-hover:-translate-x-0.5" d="M7 12h10"/><path class="transition-transform group-hover:translate-x-0.5" d="M7 16h6"/></svg>` +
                    `<span>${escapeHtml(target.entry.title)}</span>` +
                    `</button>`;

                if (pattern.test(escaped)) {
                    escaped = escaped.replace(pattern, pill);
                }
            }

            return escaped;
        };

        const renderStreamingToElement = () => {
            const contentEl = document.getElementById('streaming-message-content');
            if (!contentEl) return;
            contentEl.innerHTML = formatContentWithPills(streamedRawText, streamedEntries);
        };

        const resetStreamingUi = () => {
            streamedRawText = '';
            streamedEntries = [];
            const dots = document.getElementById('streaming-message-dots');
            const content = document.getElementById('streaming-message-content');
            const toolContainer = document.getElementById('streaming-tool-activity');

            if (dots) {
                dots.classList.remove('hidden');
            }

            if (content) {
                content.innerHTML = '';
                content.classList.add('hidden');
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

            if (toolData.status === 'running') {
                container.innerHTML = `
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-blue-500/20 bg-blue-500/10 text-xs text-blue-300 animate-pulse">
                        <svg class="animate-spin h-3.5 w-3.5 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"><\/circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"><\/path>
                        <\/svg>
                        <span>${escapeHtml(toolData.label || 'Processing memory...')}<\/span>
                    <\/div>
                `;
            } else if (toolData.status === 'done') {
                if (toolData.tool === 'search_journal' && Array.isArray(toolData.result?.entries)) {
                    streamedEntries = toolData.result.entries;
                    streamedEntries.forEach(entry => {
                        if (entry && entry.id) {
                            window.__entryPreviewCache.set('entry_' + entry.id, entry);
                        }
                    });
                    if (streamedRawText) {
                        renderStreamingToElement();
                    }
                }

                const toolIcon = toolData.tool === 'search_journal'
                    ? '<svg class="w-3.5 h-3.5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/><\/svg>'
                    : (toolData.tool === 'get_user_stats'
                        ? '<svg class="w-3.5 h-3.5 text-purple-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/><\/svg>'
                        : '<svg class="w-3.5 h-3.5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/><\/svg>');

                container.innerHTML = `
                    <div class="rounded-xl border border-white/10 bg-white/[0.04] text-xs text-gray-300">
                        <div class="px-3 py-1.5 flex items-center justify-between">
                            <span class="inline-flex items-center gap-1.5">
                                ${toolIcon}
                                <span class="font-normal text-gray-300">${escapeHtml(toolData.label || toolData.tool)}<\/span>
                            <\/span>
                            <span class="text-[10px] text-gray-500 uppercase tracking-wider font-mono">DONE<\/span>
                        <\/div>
                    <\/div>
                `;
            }
            scrollMessagesToBottom();
        };

        const appendStreamingText = (text) => {
            const contentEl = document.getElementById('streaming-message-content');
            const dotsEl = document.getElementById('streaming-message-dots');

            if (!contentEl || !text) {
                return;
            }

            if (contentEl.classList.contains('hidden')) {
                dotsEl?.classList.add('hidden');
                contentEl.classList.remove('hidden');
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

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) {
                        break;
                    }

                    buffer += decoder.decode(value, { stream: true });

                    const parsed = parseSseChunk(buffer);
                    buffer = parsed.remainder;

                    for (const event of parsed.events) {
                        if (event.type === 'tool' && event.data) {
                            handleToolEvent(event.data);
                        } else if (event.type === 'chunk' && event.data?.text) {
                            receivedText = true;
                            appendStreamingText(event.data.text);
                        } else if (event.type === 'done') {
                            receivedDone = true;
                            activeStreamController = null;
                            await finishStreaming(
                                component,
                                event.data?.conversationId ?? conversationId
                            );
                            return;
                        } else if (event.type === 'error') {
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
                        if (root._x_dataStack && root._x_dataStack[0]) {
                            root._x_dataStack[0].activeSessionId = String(sessionId);
                        }
                    }
                    syncConversationUrl(sessionId);
                });

                Livewire.on('session-cleared', () => {
                    const root = document.getElementById('chatRoot');
                    if (root) {
                        root.dataset.conversationId = '';
                        if (root._x_dataStack && root._x_dataStack[0]) {
                            root._x_dataStack[0].activeSessionId = '';
                        }
                    }
                    syncConversationUrl(null);
                });

                if (Livewire.hook) {
                    Livewire.hook('message.processed', () => {
                        syncEmptyChatState();
                        refreshMessageTimestamps();
                    });
                }
            }

            const activeRoot = document.getElementById('chatRoot');
            if (activeRoot && activeRoot.dataset.conversationId) {
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

        document.addEventListener('livewire:navigated', () => {
            refreshMessageTimestamps();
            syncEmptyChatState();
            scrollMessagesToBottom();
        });
    })();
</script>
</div>
