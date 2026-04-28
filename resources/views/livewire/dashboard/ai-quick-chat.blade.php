<div>
    <!-- AI-Powered Quick Actions -->
    <div class="mt-12 space-y-4">
        <div class="text-center space-y-2">
            <h2 class="text-2xl font-semibold flex items-center justify-center gap-2">
                <x-icon name="sparkles" class="h-6 w-6 text-blue-500" />
                AI-Powered Insights
            </h2>
            <p class="text-muted">Let Lumi AI help you explore your thoughts and patterns</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 sm:grid-cols-1">
            <!-- Guided Reflection Card -->
            <div class="group brand-panel rounded-lg card-highlight cursor-pointer transition-all duration-200 hover:border-[#c2b68e]/30"
                wire:click="startGuidedReflection" wire:loading.class="opacity-50 pointer-events-none"
                wire:target="startGuidedReflection">
                <div class="relative p-4 pb-3">
                    <div class="flex items-start gap-3">
                        <div class="brand-chip flex h-10 w-10 items-center justify-center rounded-md">
                            <x-icon name="heart-handshake" class="h-5 w-5 text-blue-300" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white/90">Start Guided Reflection</h3>
                            <p class="text-sm text-white/55">AI-guided self-reflection session</p>
                        </div>
                        <span class="brand-chip inline-flex h-8 w-8 items-center justify-center rounded-full text-blue-300 transition-transform duration-200 group-hover:translate-x-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                <path fill-rule="evenodd" d="M3.25 10a.75.75 0 0 1 .75-.75h9.19L9.97 6.03a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.22-3.22H4A.75.75 0 0 1 3.25 10Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="relative px-4 pb-4 pt-0">
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
            <div class="group brand-panel rounded-lg card-highlight cursor-pointer transition-all duration-200 hover:border-[#c2b68e]/30"
                wire:click="summarizePastWeek" wire:loading.class="opacity-50 pointer-events-none"
                wire:target="summarizePastWeek">
                <div class="relative p-4 pb-3">
                    <div class="flex items-start gap-3">
                        <div class="brand-chip flex h-10 w-10 items-center justify-center rounded-md">
                            <x-icon name="scroll-text" class="h-5 w-5 text-emerald-300" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white/90">Weekly Summary</h3>
                            <p class="text-sm text-white/55">Insights from recent entries</p>
                        </div>
                        <span class="brand-chip inline-flex h-8 w-8 items-center justify-center rounded-full text-emerald-300 transition-transform duration-200 group-hover:translate-x-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                <path fill-rule="evenodd" d="M3.25 10a.75.75 0 0 1 .75-.75h9.19L9.97 6.03a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.22-3.22H4A.75.75 0 0 1 3.25 10Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="relative px-4 pb-4 pt-0">
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
            @can('access-premium')
                {{-- ✅ Premium users see and can click the card --}}
                <div class="group brand-panel rounded-lg card-highlight cursor-pointer transition-all duration-200 hover:border-[#c2b68e]/30"
                    wire:click="startQuickChat" wire:loading.class="opacity-50 pointer-events-none"
                    wire:target="startQuickChat">
                    <div class="relative p-4 pb-3">
                        <div class="flex items-start gap-3">
                            <div class="brand-chip flex h-10 w-10 items-center justify-center rounded-md">
                                <x-icon name="flash-outline" class="h-5 w-5 text-violet-300" />
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-white/90">Quick Chat</h3>
                                <p class="text-sm text-white/55">Private chat, not saved</p>
                            </div>
                            <span class="brand-chip inline-flex h-8 w-8 items-center justify-center rounded-full text-violet-300 transition-transform duration-200 group-hover:translate-x-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                    <path fill-rule="evenodd" d="M3.25 10a.75.75 0 0 1 .75-.75h9.19L9.97 6.03a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.22-3.22H4A.75.75 0 0 1 3.25 10Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
            @else
                {{-- 🔒 Free users see a locked state --}}
                <div
                    class="brand-panel rounded-lg card-highlight cursor-not-allowed opacity-75">
                    <div class="relative p-4 pb-3">
                        <div class="flex items-start gap-3">
                            <div class="brand-chip flex h-10 w-10 items-center justify-center rounded-md">
                                <x-icon name="lock" class="h-5 w-5 text-violet-300" />
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-white/90">Quick Chat</h3>
                                <p class="text-sm text-white/55">Upgrade to unlock this feature</p>
                            </div>
                            <span class="brand-chip inline-flex h-8 items-center rounded-full px-3 text-[11px] uppercase tracking-[0.18em] text-white/55">Locked</span>
                        </div>
                    </div>


                </div>
            @endcan


            <!-- Review Memos Card -->
            <div class="group brand-panel rounded-lg card-highlight cursor-pointer transition-all duration-200 hover:border-[#c2b68e]/30"
                wire:click="reviewPastMemos" wire:loading.class="opacity-50 pointer-events-none"
                wire:target="reviewPastMemos">
                <div class="relative p-4 pb-3">
                    <div class="flex items-start gap-3">
                        <div class="brand-chip flex h-10 w-10 items-center justify-center rounded-md">
                            <x-icon name="scroll-text" class="h-5 w-5 text-amber-300" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white/90">Review Past Memos</h3>
                            <p class="text-sm text-white/55">Analyze patterns in your writing</p>
                        </div>
                        <span class="brand-chip inline-flex h-8 w-8 items-center justify-center rounded-full text-amber-300 transition-transform duration-200 group-hover:translate-x-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                <path fill-rule="evenodd" d="M3.25 10a.75.75 0 0 1 .75-.75h9.19L9.97 6.03a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.22-3.22H4A.75.75 0 0 1 3.25 10Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="relative px-4 pb-4 pt-0">
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
    <div
        class="brand-panel relative mt-12 rounded-lg light-gradient-card">

        <div class="relative p-6">
            <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                <div class="flex items-start gap-4">
                    <div class="brand-chip flex h-12 w-12 items-center justify-center rounded-full">
                        <x-icon name="brain" class="h-6 w-6 text-blue-300" />
                    </div>
                    <div class="max-w-2xl">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-white/35">Featured Session</p>
                        <h3 class="mt-1 text-xl font-semibold text-white/90">Ready for a deeper conversation?</h3>
                        <p class="mt-2 text-sm text-white/60">
                            I've analyzed your recent entries and noticed some interesting patterns. Let's explore them
                            together.
                        </p>
                    </div>
                </div>

                <button wire:click="startTherapySession" wire:loading.class="opacity-50 pointer-events-none"
                    wire:target="startTherapySession"
                    class="brand-button cursor-pointer inline-flex items-center justify-center gap-2 rounded-md px-4 py-3 text-sm font-medium text-white transition min-w-[220px]">
                    @if($isProcessing === 'therapy-session')
                        <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                        <span>Starting session...</span>
                    @else
                        <x-icon name="chatbubbles-outline" class="h-4 w-4 text-blue-300" />
                        <span>Start Therapy Session</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-blue-300">
                            <path fill-rule="evenodd" d="M3.25 10a.75.75 0 0 1 .75-.75h9.19L9.97 6.03a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.22-3.22H4A.75.75 0 0 1 3.25 10Z" clip-rule="evenodd" />
                        </svg>
                    @endif
                </button>
            </div>
        </div>
    </div>

    <!-- Weekly Summary Modal -->
    {{-- @if($showSummaryModal)
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
    @endif --}}
    @if($showSummaryModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            wire:click="closeSummaryModal">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl max-w-4xl w-full max-h-[80vh] overflow-hidden"
                wire:click.stop>

                {{-- Header --}}
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

                {{-- Content --}}
                <div class="p-6 overflow-y-auto max-h-[60vh] space-y-6">
                    @if($summaryLoading)
                        <div class="flex items-center justify-center py-12">
                            <div class="text-center">
                                <div
                                    class="w-12 h-12 border-4 border-green-500 border-t-transparent rounded-full animate-spin mx-auto mb-4">
                                </div>
                                <p class="text-gray-600 dark:text-gray-400">Analyzing your week...</p>
                            </div>
                        </div>
                    @elseif(!empty($weeklySummary))
                        @php
                            // Split TL;DR from the rest
                            preg_match('/(🧭 TL;DR.*?)(?=✨|$)/s', $weeklySummary, $matches);
                            $tldr = $matches[1] ?? '';
                            $rest = trim(str_replace($tldr, '', $weeklySummary));
                        @endphp

                        {{-- TL;DR Card --}}
                        @if(!empty($tldr))
                            <div
                                class="bg-green-50 dark:bg-green-900/40 border border-green-400/30 rounded-xl p-4 prose prose-lg dark:prose-invert">
                                {!! \Illuminate\Support\Str::markdown($tldr) !!}
                            </div>
                        @endif

                        {{-- Other Sections --}}
                        <div class="mt-4 prose prose-lg dark:prose-invert">
                            {!! \Illuminate\Support\Str::markdown($rest) !!}
                        </div>

                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No summary generated yet. Click the card to create
                            one.</p>
                    @endif
                </div>

                {{-- Footer --}}
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
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-card border border-white/10 rounded-lg max-w-2xl w-full mx-4 h-[600px] flex flex-col shadow-xl">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-white/10">
                    <h3 class="text-lg font-semibold text-white">Quick Chat</h3>
                    <button wire:click="closeQuickChatModal" class="text-muted hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                <!-- Messages -->
                <div class="flex-1 overflow-y-auto p-4 space-y-4">
                    @foreach($quickChatMessages as $message)
                        <div class="flex {{ $message['sender'] === 'user' ? 'justify-end' : 'justify-start' }}">
                            <div
                                class="max-w-[80%] {{ $message['sender'] === 'user' ? 'bg-primary text-primary-foreground' : 'bg-muted/20 text-white' }} rounded-lg p-3">
                                <p class="text-sm whitespace-pre-wrap">{{ $message['content'] }}</p>
                                <p class="text-xs opacity-70 mt-1">{{ $message['timestamp'] }}</p>
                            </div>
                        </div>
                    @endforeach

                    @if($quickChatLoading)
                        <div class="flex justify-start">
                            <div class="bg-muted/20 rounded-lg p-3">
                                <div class="flex items-center space-x-2">
                                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                                    <span class="text-sm text-muted">Thinking...</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Input -->
                <div class="p-4 border-t border-white/10">
                    <x-chat-form wire-submit="sendQuickChat" wire-model="quickChatInput"
                        placeholder="Share your thoughts..." :is-disabled="$quickChatLoading" :is-typing="$quickChatLoading"
                        submit-icon="send" typing-icon="stop" />
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
