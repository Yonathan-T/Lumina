<div id="chatRoot" class="relative flex h-screen bg-gradient-dark gap-4 overflow-hidden"
    data-stream-endpoint="{{ route('chat.stream') }}"
    data-conversation-id="{{ $activeSession['id'] ?? '' }}"
    data-user-initial="{{ substr(auth()->user()->name ?? 'U', 0, 1) }}">
    <!-- Dock button that attaches near main sidebar -->
    <button id="chatDockBtn"
        class="fixed chat-dock-pos z-50 hidden items-center justify-center w-10 h-10 rounded-full border border-white/20 bg-white/10 text-white hover:bg-white/20 transition"
        aria-label="Toggle chat sessions">
        <x-icon name="message" class="w-5 h-5" />
    </button>

    <!-- Chat Drawer / Sidebar -->
    <div id="chatDrawer"
        class="fixed md:static inset-y-0 md:inset-auto left-0 z-50 w-80 bg-gradient-dark sidebar-gradient rounded-none md:rounded-lg border border-gray-700 flex flex-col transform -translate-x-full md:translate-x-0 transition-all duration-300">
        <!-- Header -->
        <div class="p-4 border-b border-gray-700 flex items-center justify-between gap-2">
            <button wire:click="createNewSession"
                class="cursor-pointer flex-1 flex items-center justify-center gap-2 bg-gradient-dark  text-white rounded-lg px-4 py-2.5 transition-colors">
                <x-icon name="message" class="w-5 h-5" />
                New Chat
            </button>
            <div class="flex items-center gap-1">
                <!-- Collapse/expand chat nav (desktop) -->
                <button id="chatNavToggle"
                    class="hidden md:inline-flex items-center justify-center w-9 h-9 rounded-md border border-white/10 text-white/80 hover:text-white hover:bg-white/10 transition"
                    aria-label="Toggle chat navigation" aria-expanded="true">
                    <svg class="icon-collapse w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8.53 11.47a.75.75 0 0 1 0 1.06l-3 3a.75.75 0 0 1-1.06-1.06L6.94 12 4.47 9.53A.75.75 0 1 1 5.53 8.47l3 3Zm8-2.94a.75.75 0 0 1 1.06 1.06L14.06 12l3.53 3.53a.75.75 0 1 1-1.06 1.06l-4-4a.75.75 0 0 1 0-1.06l4-4Z"
                            clip-rule="evenodd" />
                    </svg>
                    <svg class="icon-expand w-4 h-4 hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M5.47 8.47a.75.75 0 0 1 1.06 0L10 11.94l3.47-3.47a.75.75 0 1 1 1.06 1.06l-4 4a.75.75 0 0 1-1.06 0l-4-4a.75.75 0 0 1 0-1.06Zm8 6a.75.75 0 0 1 1.06 0l3 3a.75.75 0 1 1-1.06 1.06L14.06 16.6l-2.47 2.47a.75.75 0 1 1-1.06-1.06l3-3Z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
                <!-- Close drawer (mobile/overlay) -->
                <button id="chatDrawerClose"
                    class="ml-auto p-2 text-gray-400 hover:text-white transition-colors md:hidden">
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
                    <div wire:click="selectSession('{{ $session['id'] }}')"
                        class="group p-3 rounded-lg cursor-pointer transition-colors mb-2 {{ $activeSession && $activeSession['id'] === $session['id'] ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <x-icon name="message"
                                        class="w-4 h-4 {{ $activeSession && $activeSession['id'] === $session['id'] ? 'text-white' : 'text-gray-400' }}" />
                                    <h4 class="text-sm font-medium truncate">{{ $session['title'] }}</h4>
                                </div>
                                <p
                                    class="text-xs {{ $activeSession && $activeSession['id'] === $session['id'] ? 'text-blue-200' : 'text-gray-500' }}">
                                    {{ $session['lastActivity'] }} • {{ $session['messageCount'] }} messages
                                </p>
                            </div>
                            <button wire:click.stop="deleteSession('{{ $session['id'] }}')"
                                class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-400 transition-all p-1">
                                <x-icon name="trash" class=" w-4 h-4" />
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
    <div id="chatBackdrop" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 hidden md:hidden"></div>

    <!-- Main Chat Area -->
    <div class="flex-1 flex flex-col sidebar-gradient border border-gray-700 rounded-lg">
        @if($activeSession)
            <!-- Chat Header -->
            <div class="bg-gradient-dark border-b border-gray-700 p-4 rounded-t-lg sticky top-0 z-10">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-full bg-blue-600">
                        <x-icon name="brain" class=" w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">{{ $activeSession['title'] }}</h3>
                        <p class="text-sm text-gray-400">AI-powered reflection and insights</p>
                    </div>

                </div>
                <!--
                                                                     <div class=" flex  items center gap-2 justify-end">
                                                                            <span class="text-muted">Gen-Z Mode</span>
                                                                            <x-toggle :model="'darkMode'" />
                                                                        </div>
                                                                         -->

            </div>

            <!-- Messages Area -->
            @if ($activeSession)
                <div class="flex-1 overflow-y-auto p-4 md:p-6 pb-28 md:pb-6" id="messages-scroll">
                    <div wire:key="messages-{{ $activeSession['id'] }}" class="space-y-6" id="messages-container">
                        @if ($isLoadingMessages)
                            <div class="flex items-center justify-center py-8">
                                <div class="flex items-center space-x-3">
                                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                                    <span class="text-gray-400 text-sm">Loading messages...</span>
                                </div>
                            </div>
                        @else
                            @forelse($messages as $message)
                                <div class="flex items-start gap-4 {{ $message['isAi'] ? '' : 'flex-row-reverse' }}">
                                    @if($message['isAi'])
                                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                                            <x-icon name="flash-outline" class="w-4 h-4" />
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
                            @empty
                                <div id="empty-chat-state" class="text-center text-gray-500 mt-16">
                                    <div class="mb-6">
                                        <svg class="w-20 h-20 mx-auto text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                            </path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-medium mb-2 text-white">Start a conversation</h3>
                                    <p class="text-gray-400">Send a message to begin your therapy session</p>
                                </div>
                            @endforelse
                        @endif
                    </div>

                    <div id="chat-pending-lane" wire:ignore class="space-y-6"></div>
                </div>
            @else
                <div class="flex items-center justify-center h-full text-center text-gray-500 py-8">
                    <div class="w-full">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                            </path>
                        </svg>
                        <p class="text-sm">No conversations yet</p>
                        <p class="text-xs mt-1">Start a new chat to begin</p>
                    </div>
                </div>
            @endif

            <!-- Message Input -->
            <div
                class="bg-gray-700/60 backdrop-blur border-t border-gray-600 rounded-b-lg bg-gradient-dark p-3 md:p-4 sticky bottom-0 z-10 safe-bottom">
                <x-chat-form wire-model="newMessage" placeholder="Share your thoughts..."
                    :is-typing="false" submit-icon="send" typing-icon="stop" :use-client-submit="true" />
            </div>

        @else
            <!-- No Active Session -->
            <div class="flex-1 flex items-center justify-center">
                @if(!$sessions)
                    <div class="text-center max-w-md">
                        <div class="flex items-center justify-center h-full text-center text-gray-500 py-8">
                            <div class="w-full">
                                <x-icon name="message" class="w-12 h-12 mx-auto mb-3 text-gray-600" />
                                <p class="text-sm">You haven’t started any chats yet. Ready to dive in?</p>
                                <p class="text-xs mt-1">Start a new chat to begin</p>
                            </div>
                        </div>
                        <button wire:click="createNewSession"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-medium transition-colors inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Start New Chat
                        </button>
                    </div>
                @else
                    <div class="text-center max-w-md">
                        <div class="flex items-center justify-center h-full text-center text-gray-500 py-8">
                            <div class="w-full">
                                <x-icon name="message" class="w-12 h-12 mx-auto mb-3 text-gray-600" />
                                <p class="text-sm">This conversation is no longer available.</p>
                                <p class="text-xs mt-1">Choose another from the sidebar or start a new one</p>
                            </div>
                        </div>
                        <button wire:click="createNewSession"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-medium transition-colors inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Start New Chat
                        </button>
                    </div>
                @endif
            </div>

        @endif
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
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

        const syncEmptyChatState = () => {
            const emptyState = document.getElementById('empty-chat-state');
            const lane = document.getElementById('chat-pending-lane');

            if (!emptyState) {
                return;
            }

            if (lane && lane.children.length > 0) {
                emptyState.classList.add('hidden');
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

            if (!lane || !root) {
                return;
            }

            const userInitial = escapeHtml(root.dataset.userInitial || 'U');
            const safeMessage = escapeHtml(message);
            const createdAt = new Date().toISOString();

            lane.innerHTML = `
                <div class="flex items-start gap-4 flex-row-reverse">
                    <div class="w-8 h-8 bg-gray-600 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-sm font-medium text-white">${userInitial}</span>
                    </div>
                    <div class="max-w-[300px] md:max-w-[400px] lg:max-w-[500px]">
                        <div class="rounded-2xl px-4 py-3 bg-blue-600 text-white">
                            <p class="text-sm leading-relaxed whitespace-pre-wrap break-words">${safeMessage}</p>
                        </div>
                        <div class="flex items-center gap-2 mt-2 justify-end">
                            <p class="text-xs text-gray-500 message-timestamp" data-created-at="${createdAt}"></p>
                        </div>
                    </div>
                </div>
                <div id="streaming-message" class="flex items-start gap-4">
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="max-w-[300px] md:max-w-[400px] lg:max-w-[500px]">
                        <div class="bg-gray-800 rounded-2xl px-4 py-3 min-h-[44px] flex items-center">
                            <div id="streaming-message-dots" class="flex space-x-1">
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce"></div>
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                            </div>
                            <p id="streaming-message-content" class="hidden text-sm leading-relaxed whitespace-pre-wrap break-words text-gray-100"></p>
                        </div>
                    </div>
                </div>
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

        const finishStreaming = async (component, conversationId, failed = false, errorMessage = null) => {
            setFormBusy(false);

            if (failed) {
                clearPendingExchange();
                await component.call('failStreamingResponse', conversationId, errorMessage);
                return;
            }

            await component.call('completeStreamingResponse', conversationId);
            clearPendingExchange();
        };

        const startStreamingResponse = async ({ conversationId, message }) => {
            const endpoint = document.getElementById('chatRoot')?.dataset.streamEndpoint;
            const component = getChatComponent();

            if (!endpoint || !component || !conversationId || !message) {
                await finishStreaming(component, conversationId, true);
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
                    await finishStreaming(component, null, true, errorMessage);
                    return;
                }

                const reader = response.body?.getReader();
                if (!reader) {
                    activeStreamController = null;
                    await finishStreaming(component, null, true);
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
                                event.data?.message ?? 'Sorry, I encountered an error. Please try again.'
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
                            : 'The response timed out. Please try again.'
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
                await finishStreaming(component, streamStarted ? conversationId : null, true);
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
                    'Sorry, I encountered an error. Please try again.'
                );
            }
        };

        const bindChatForm = () => {
            const form = document.getElementById('chat-message-form');
            if (!form || form.dataset.bound === 'true') {
                return;
            }

            form.dataset.bound = 'true';
            form.addEventListener('submit', handleChatSubmit);
        };

        Livewire.on('messages-updated', () => {
            scrollMessagesToBottom();
            refreshMessageTimestamps();
        });
        bindChatForm();
        refreshMessageTimestamps();

        Livewire.hook('message.processed', () => {
            bindChatForm();
            syncEmptyChatState();
            refreshMessageTimestamps();
        });
    });

    // Chat drawer toggle logic
    (function () {
        const dockBtn = document.getElementById('chatDockBtn');
        const drawer = document.getElementById('chatDrawer');
        const backdrop = document.getElementById('chatBackdrop');
        const drawerClose = document.getElementById('chatDrawerClose');
        const navToggle = document.getElementById('chatNavToggle');

        function setOpen(open) {
            if (!drawer) return;
            if (open) {
                document.body.classList.add('chat-open');
                if (backdrop) backdrop.classList.remove('hidden');
            } else {
                document.body.classList.remove('chat-open');
                if (backdrop) backdrop.classList.add('hidden');
            }
        }

        if (dockBtn) {
            dockBtn.addEventListener('click', () => {
                const isDesktop = window.matchMedia('(min-width: 768px)').matches;
                if (isDesktop) {
                    // On desktop, dock button restores the nav
                    setNavCollapsed(false);
                } else {
                    // On mobile, dock toggles the drawer
                    setOpen(!document.body.classList.contains('chat-open'));
                }
            });
        }
        if (backdrop) {
            backdrop.addEventListener('click', () => setOpen(false));
        }
        if (drawerClose) {
            drawerClose.addEventListener('click', () => setOpen(false));
        }

        // Desktop nav collapse
        function setNavCollapsed(collapsed) {
            if (collapsed) {
                document.body.classList.add('chat-nav-collapsed');
                if (navToggle) {
                    navToggle.setAttribute('aria-expanded', 'false');
                    const c = navToggle.querySelector('.icon-collapse');
                    const e = navToggle.querySelector('.icon-expand');
                    if (c && e) { c.classList.add('hidden'); e.classList.remove('hidden'); }
                }
            } else {
                document.body.classList.remove('chat-nav-collapsed');
                if (navToggle) {
                    navToggle.setAttribute('aria-expanded', 'true');
                    const c = navToggle.querySelector('.icon-collapse');
                    const e = navToggle.querySelector('.icon-expand');
                    if (c && e) { c.classList.remove('hidden'); e.classList.add('hidden'); }
                }
            }
        }
        if (navToggle) {
            navToggle.addEventListener('click', function () {
                setNavCollapsed(!document.body.classList.contains('chat-nav-collapsed'));
            });
        }
    })();
</script>
