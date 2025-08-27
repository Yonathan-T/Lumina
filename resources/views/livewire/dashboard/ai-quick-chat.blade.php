<div>
    <!-- AI-Powered Quick Actions -->
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
                wire:click="startGuidedReflection" wire:loading.class="opacity-50 pointer-events-none"
                wire:target="startGuidedReflection">
                <div class="p-4 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-gradient-dark">
                            <x-icon name="heart-handshake" class="h-5 w-5" />
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
                            Starting reflection session...
                        </div>
                    @endif
                </div>
            </div>

            <!-- Weekly Summary Card -->
            <div class="card-highlight border cursor-pointer transition-all duration-200 hover:scale-[1.02] bg-green-500/10 hover:bg-green-500/20 border-green-500/20 rounded-lg"
                wire:click="summarizePastWeek" wire:loading.class="opacity-50 pointer-events-none"
                wire:target="summarizePastWeek">
                <div class="p-4 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-gradient-dark">
                            <x-icon name="scroll-text" class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">Weekly Summary</h3>
                            <p class="text-sm text-muted-foreground">TLDR + insights from recent entries</p>
                        </div>
                    </div>
                </div>
                <div class="px-4 pb-4 pt-0">
                    @if($isProcessing === 'weekly-summary')
                        <div class="flex items-center gap-2 text-sm text-green-500">
                            <div class="w-4 h-4 border-2 border-green-500 border-t-transparent rounded-full animate-spin">
                            </div>
                            Generating summary...
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Chat Card -->
            <div class="card-highlight border cursor-pointer transition-all duration-200 hover:scale-[1.02] bg-purple-500/10 hover:bg-purple-500/20 border-purple-500/20 rounded-lg"
                wire:click="startQuickChat" wire:loading.class="opacity-50 pointer-events-none"
                wire:target="startQuickChat">
                <div class="p-4 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-gradient-dark">
                            <x-icon name="flash-outline" class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">Quick Chat</h3>
                            <p class="text-sm text-muted-foreground">Private chat (not saved)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review Memos Card -->
            <div class="card-highlight border cursor-pointer transition-all duration-200 hover:scale-[1.02] bg-yellow-500/10 hover:bg-yellow-500/20 border-yellow-500/20 rounded-lg"
                wire:click="reviewPastMemos" wire:loading.class="opacity-50 pointer-events-none"
                wire:target="reviewPastMemos">
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
                            Analyzing patterns...
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Therapy Chat CTA -->
    <div class="mt-12 border border-blue-500/20 bg-blue-500/15 light-gradient-card rounded-lg">
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
            <button wire:click="startTherapySession" wire:loading.class="opacity-50 pointer-events-none"
                wire:target="startTherapySession"
                class="cursor-pointer inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors bg-blue-600 hover:bg-blue-700 text-white h-10 px-4 py-2">
                @if($isProcessing === 'therapy-session')
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                    Starting session...
                @else
                    <x-icon name="chatbubbles-outline" class="mr-2 h-4 w-4" />
                    Start Therapy Session
                @endif
            </button>
        </div>
    </div>

    <!-- Weekly Summary Modal -->
    @if($showSummaryModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            wire:click="closeSummaryModal">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl max-w-4xl w-full max-h-[80vh] overflow-hidden"
                wire:click.stop>
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-semibold flex items-center gap-2">
                            <x-icon name="scroll-text" class="h-6 w-6 text-green-500" />
                            Weekly Summary
                        </h2>
                        <button wire:click="closeSummaryModal"
                            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                            <x-icon name="message" class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <div class="p-6 overflow-y-auto max-h-[60vh]">
                    @if($summaryLoading)
                        <div class="flex items-center justify-center py-12">
                            <div class="text-center">
                                <div
                                    class="w-12 h-12 border-4 border-green-500 border-t-transparent rounded-full animate-spin mx-auto mb-4">
                                </div>
                                <p class="text-gray-600 dark:text-gray-400">Analyzing your week...</p>
                            </div>
                        </div>
                    @else
                        <div class="prose prose-lg max-w-none dark:prose-invert">
                            {!! \Illuminate\Support\Str::markdown($weeklySummary) !!}
                        </div>
                    @endif
                </div>

                <div class="p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                    <div class="flex justify-end gap-3">
                        <button wire:click="closeSummaryModal"
                            class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Quick Chat Modal -->
    @if($showQuickChatModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            wire:click="closeQuickChatModal">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl max-w-2xl w-full h-[600px] flex flex-col"
                wire:click.stop>
                <!-- Header -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <x-icon name="chatbubbles-outline" class="h-5 w-5 text-purple-500" />
                        Quick Chat
                    </h2>
                    <button wire:click="closeQuickChatModal"
                        class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                        <x-icon name="message" class="h-4 w-4" />
                    </button>
                </div>

                <!-- Messages -->
                <div class="flex-1 overflow-y-auto p-4 space-y-4 chat-scrollbar">
                    @foreach($quickChatMessages as $message)
                            <div class="flex {{ $message['sender'] === 'ai' ? 'justify-start' : 'justify-end' }}">
                                <div class="max-w-[80%] group">
                                    <div class="p-3 rounded-lg {{ $message['sender'] === 'ai'
                        ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100'
                        : 'bg-blue-500 text-white' }}">
                                        {{ $message['content'] }}
                                    </div>
                                    <div
                                        class="text-xs text-gray-500 mt-1 {{ $message['sender'] === 'ai' ? 'text-left' : 'text-right' }}">
                                        {{ $message['timestamp'] }}
                                    </div>
                                </div>
                            </div>
                    @endforeach

                    @if($quickChatLoading)
                        <div class="flex justify-start">
                            <div class="max-w-[80%]">
                                <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg">
                                    <div class="flex space-x-2">
                                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"
                                            style="animation-delay: 0.2s"></div>
                                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"
                                            style="animation-delay: 0.4s"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Input -->
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <form wire:submit.prevent="sendQuickChat" class="flex gap-2">
                        <input type="text" wire:model.defer="quickChatInput" placeholder="Type your message..."
                            class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:border-blue-500 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800"
                            {{ $quickChatLoading ? 'disabled' : '' }}>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 transition-colors"
                            {{ $quickChatLoading ? 'disabled' : '' }}>
                            @if($quickChatLoading)
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            @else
                                Send
                            @endif
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <style>
        /* Custom scrollbar for chat */
        .chat-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .chat-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .chat-scrollbar::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .chat-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Dark mode scrollbar */
        .dark .chat-scrollbar::-webkit-scrollbar-track {
            background: #374151;
        }

        .dark .chat-scrollbar::-webkit-scrollbar-thumb {
            background: #6b7280;
        }

        .dark .chat-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>
</div>