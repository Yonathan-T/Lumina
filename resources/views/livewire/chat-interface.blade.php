<div class="flex h-screen bg-gradient-dark  gap-4 overflow-hidden">
    <!-- Sidebar -->
    <div class="w-80 bg-gradient-dark sidebar-gradient rounded-lg border border-gray-700 flex flex-col">
        <!-- Header -->
        <div class="p-4 border-b border-gray-700 flex items-center justify-between">
            <button wire:click="createNewSession"
                class="cursor-pointer w-full flex items-center justify-center gap-2 bg-gradient-dark  text-white rounded-lg px-4 py-2.5 transition-colors">
                <x-icon name="message" class="w-5 h-5" />
                New Chat
            </button>
            <div>
                <!-- Hide the sidebar -->
                <button class="ml-auto p-2 text-gray-400 hover:text-white transition-colors">
                    <x-icon name="panel-right-open" class="w-5 h-5" />
                </button>
            </div>
        </div>

        <!-- Navigation Menu -->
        <div class="flex-1 overflow-y-auto">
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

    <!-- Main Chat Area -->
    <div class="flex-1 flex flex-col sidebar-gradient border border-gray-700 rounded-lg">
        @if($activeSession)
            <!-- Chat Header -->
            <div class="bg-gradient-dark  border-b border-gray-700 p-4 rounded-t-lg">
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
                <div wire:key="$activeSession['id']" class="flex-1 overflow-y-auto p-6 space-y-6" id="messages-container">
                    @if ($isLoadingMessages)
                        <div class="flex items-center justify-center py-8">
                            <div class="flex items-center space-x-3">
                                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                                <span class="text-gray-400 text-sm">Loading messages...</span>
                            </div>
                        </div>
                    @else
                        @forelse($messages as $message)
                            <div
                                class="flex items-start gap-4 {{ $message['isAi'] ? '' : 'flex-row-reverse' }} {{ isset($message['isOptimistic']) ? 'opacity-70' : '' }}">
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
                                        <p class="text-xs text-gray-500">{{ $message['timestamp'] }}</p>
                                        @if(isset($message['isOptimistic']))
                                            <div class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-gray-500 mt-16">
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

                        @if($isTyping)
                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                                    <x-icon name="flash-outline" class="w-4 h-4" />
                                </div>
                                <div class="bg-gray-800 rounded-2xl px-4 py-3">
                                    <div class="flex items-center space-x-2">
                                        <div class="flex space-x-1">
                                            <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce"></div>
                                            <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.1s">
                                            </div>
                                            <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.2s">
                                            </div>
                                        </div>
                                        <span class="text-sm text-gray-400">AI is thinking...</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
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
            <div class="bg-gray-700 border-t border-gray-600 rounded-b-lg bg-gradient-dark p-4">
                <x-chat-form 
                    wire-submit="sendMessage"
                    wire-model="newMessage"
                    placeholder="Share your thoughts..."
                    :is-typing="$isTyping"
                    submit-icon="send"
                    typing-icon="stop" />
            </div>

        @else
            <!-- No Active Session -->
            <div class="flex-1 flex items-center justify-center">
                <div class="text-center max-w-md">
                    <div class="flex items-center justify-center h-full text-center text-gray-500 py-8">
                        <div class="w-full">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                </path>
                            </svg>
                            <p class="text-sm">No conversations yet</p>
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
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('messages-updated', () => {
            const container = document.getElementById('messages-container');
            if (container) {
                // Use requestAnimationFrame to ensure DOM is updated
                requestAnimationFrame(() => {
                    container.scrollTop = container.scrollHeight;
                });
            }
        });

        // Handle async events
        Livewire.on('create-session-async', (data) => {
        });

        Livewire.on('message-sent-async', (data) => {
        });

        Livewire.on('generate-ai-response', (data) => {
        });

        Livewire.on('session-selected', (data) => {
        });

        Livewire.on('delete-session-async', (data) => {
        });
    });
</script>