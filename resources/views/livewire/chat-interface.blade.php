<div id="chatRoot" class="relative flex h-full max-h-full w-full bg-gradient-dark gap-0 md:gap-4 overflow-hidden overscroll-none"
    x-data="{
        showDeleteModal: false,
        sessionToDelete: null,
        sessionTitleToDelete: '',
        deletedSessionIds: [],
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
    data-user-initial="{{ substr(auth()->user()->name ?? 'U', 0, 1) }}">
    <!-- Chat Drawer / Sidebar -->
    <div id="chatDrawer"
        :class="mobileDrawerOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full'"
        class="fixed md:static inset-y-0 md:inset-auto left-0 z-50 w-80 max-w-[85vw] md:h-full bg-gradient-dark sidebar-gradient rounded-none md:rounded-lg border-r md:border border-gray-700 flex flex-col md:translate-x-0 transition-all duration-300">
        <!-- Header -->
        <div class="p-4 border-b border-gray-700 flex items-center justify-between gap-2">
            <button wire:click="createNewSession"
                @click="closeMobile(); if (window.__syncConversationUrl) window.__syncConversationUrl(null);"
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
                        @click="closeMobile()"
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
                    <div class="p-2 rounded-full {{ $activeSession ? 'bg-blue-600' : 'bg-blue-600/30 border border-blue-500/30' }} shrink-0">
                        <x-icon name="brain" class="w-6 h-6 {{ $activeSession ? 'text-white' : 'text-blue-400' }}" />
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-base sm:text-lg font-semibold text-white truncate">
                            {{ $activeSession ? $activeSession['title'] : 'AI Reflection Companion' }}
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-400 truncate">
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
                    <div wire:key="messages-{{ $activeSession['id'] }}" class="space-y-6" id="messages-container">
                        @if ($isLoadingMessages)
                            <div class="flex items-center justify-center py-16">
                                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                            </div>
                        @else
                            @foreach($messages as $message)
                                <div class="flex items-start gap-4 {{ $message['isAi'] ? '' : 'flex-row-reverse' }}">
                                    @if($message['isAi'])
                                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                                            <x-icon name="flash-outline" class="w-4 h-4 text-white" />
                                        </div>
                                    @else
                                        <div class="w-8 h-8 bg-gray-600 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-sm font-medium text-white">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                                        </div>
                                    @endif

                                    <div class="max-w-[300px] md:max-w-[400px] lg:max-w-[500px]">
                                        <div
                                            class="rounded-2xl px-4 py-3 {{ $message['isAi'] ? 'bg-gray-800 text-gray-100' : 'bg-blue-600 text-white' }} {{ isset($message['isError']) ? 'bg-red-600' : '' }}">
                                            <p class="text-sm leading-relaxed whitespace-pre-wrap break-words">{{ $message['content'] }}</p>
                                        </div>
                                        <div class="flex items-center gap-2 mt-2 {{ $message['isAi'] ? '' : 'justify-end' }}">
                                            <p class="text-xs text-gray-500 message-timestamp" data-created-at="{{ $message['createdAt'] }}"></p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @else
                    <!-- Greeting Content Area when no active session -->
                    <div id="empty-chat-state" class="my-auto py-8 px-4 w-full max-w-3xl mx-auto flex flex-col md:flex-row md:items-center md:justify-between gap-6">
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
                        $wire.deleteSession(idToDelete); 
                    }"
                    class="flex-1 px-4 py-3 rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition-colors cursor-pointer">
                    Delete Chat
                </button>
            </div>
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
                    <\/div>
                <\/div>
            `;

            syncEmptyChatState();
            refreshMessageTimestamps();
            scrollMessagesToBottom();
        };

        const resetStreamingUi = () => {
            const dots = document.getElementById('streaming-message-dots');
            const content = document.getElementById('streaming-message-content');

            if (dots) {
                dots.classList.remove('hidden');
            }

            if (content) {
                content.textContent = '';
                content.classList.add('hidden');
            }
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

            contentEl.textContent += text;
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
                await component.call('failStreamingResponse', conversationId, finalError, userMessage);
                return;
            }

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
                        if (event.type === 'chunk' && event.data?.text) {
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
                    }
                    syncConversationUrl(sessionId);
                });

                Livewire.on('session-cleared', () => {
                    const root = document.getElementById('chatRoot');
                    if (root) {
                        root.dataset.conversationId = '';
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
