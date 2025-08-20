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
                <!-- Theme toggle -->
                <button class="ml-auto p-2 text-gray-400 hover:text-white transition-colors">
                <x-icon name="panel-right-close" class="w-5 h-5" />
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
                                    <x-icon name="message" class="w-4 h-4 {{ $activeSession && $activeSession['id'] === $session['id'] ? 'text-white' : 'text-gray-400' }}" />
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
            </div>

            <!-- Messages Area -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6" id="messages-container">
                @forelse($messages as $message)
                    <div class="flex items-start gap-4 {{ $message['isAi'] ? '' : 'flex-row-reverse' }}">
                        @if($message['isAi'])
                            <!-- AI Avatar -->
                            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                                <x-icon name="flash-outline" class=" w-4 h-4" />
                            </div>
                        @else
                            <!-- User Avatar -->
                            <div class="w-8 h-8 bg-gray-600 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-medium text-white">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                            </div>
                        @endif

                        <div class="flex-1 max-w-2xl">
                            <div
                                class="rounded-2xl px-4 py-3 {{ $message['isAi'] ? 'bg-gray-800 text-gray-100' : 'bg-blue-600 text-white' }}">
                                <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $message['content'] }}</p>
                            </div>
                            <div class="flex items-center gap-2 mt-2 {{ $message['isAi'] ? '' : 'justify-end' }}">
                                <p class="text-xs text-gray-500">{{ $message['timestamp'] }}</p>
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

                @if($isLoading)
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
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
            </div>

            <!-- Message Input -->
            <div class="bg-gray-700 border-t border-gray-600 rounded-b-lg bg-gradient-dark p-4">
    <form wire:submit="sendMessage" class="flex items-center space-x-3">
        <div class="flex-1">
            <textarea
                wire:model="newMessage"
                placeholder="Share your thoughts..."
                class="w-full bg-gray-700 border bg-gradient-dark rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent disabled:opacity-50 overflow-hidden resize-none max-h-[200px]"
                {{ $isLoading ? 'disabled' : '' }}
                oninput="this.style.height='auto'; this.style.height=(this.scrollHeight)+'px';"
                onkeydown="if(event.key==='Enter' && !event.shiftKey){event.preventDefault(); this.closest('form').dispatchEvent(new Event('submit', {bubbles: true}));}"
            ></textarea>
        </div>
        <div>
            <button type="submit"
                class="text-white p-3 rounded-xl transition-colors flex items-center justify-center"
                {{ $isLoading ? 'disabled' : '' }}>
                @if($isLoading)
                    <x-icon name="stop" class="w-5 h-5" />
                @else
                    <x-icon name="send" class="w-5 h-5" />
                @endif
            </button>
        </div>
    </form>
</div>

        @else
            <!-- No Active Session -->
            <div class="flex-1 flex items-center justify-center">
                <div class="text-center max-w-md">
                    <div class="mb-8">
                        <svg class="w-24 h-24 mx-auto text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-semibold mb-3 text-white">Weekly Reflection Session</h3>
                    <p class="text-gray-400 mb-8 leading-relaxed">
                        AI-powered reflection and insights
                    </p>
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
        Livewire.on('scroll-to-bottom', () => {
            const container = document.getElementById('messages-container');
            if (container) {
                setTimeout(() => {
                    container.scrollTop = container.scrollHeight;
                }, 100);
            }
        });

        Livewire.on('add-ai-message', (data) => {
            setTimeout(() => {
                @this.messages.push(data[0]);
                @this.$refresh();
            }, 1500);
        });
    });
</script>