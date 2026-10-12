<div>
    <!-- AI-Powered Quick Actions -->
    <div class="mt-12 space-y-4">
        <div class="text-center space-y-2">
            <h2 class="text-2xl font-semibold flex items-center justify-center gap-2">
                <x-icon name="brain" class="h-6 w-6 text-blue-400" />
                AI-Powered Insights
            </h2>
            <p class="text-muted">Let Lumi AI help you explore your thoughts and patterns</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 sm:grid-cols-1">
            <!-- Guided Reflection Card -->
            <div class="group relative cursor-pointer overflow-hidden rounded-lg border border-white/10 bg-gradient-dark transition-all duration-200 hover:border-blue-400/40"
                wire:click="startGuidedReflection" 
                wire:loading.class="opacity-75 border-blue-400/60"
                wire:target="startGuidedReflection">
                <div class="pointer-events-none absolute inset-0 bg-dot-pattern opacity-60"></div>
                <div class="relative p-4">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-md bg-blue-500/10">
                            <x-icon name="heart-handshake" class="h-5 w-5 text-blue-300" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white">Start Guided Reflection</h3>
                            <p class="text-sm text-gray-400">AI-guided self-reflection session</p>
                        </div>
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/5 text-blue-300 transition-transform duration-200 group-hover:translate-x-0.5">
                            <svg wire:loading.remove wire:target="startGuidedReflection" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                <path fill-rule="evenodd" d="M3.25 10a.75.75 0 0 1 .75-.75h9.19L9.97 6.03a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.22-3.22H4A.75.75 0 0 1 3.25 10Z" clip-rule="evenodd" />
                            </svg>
                            <div wire:loading wire:target="startGuidedReflection" class="h-4 w-4 animate-spin rounded-full border-2 border-blue-400 border-t-transparent"></div>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Weekly Summary Card -->
            <div class="group relative cursor-pointer overflow-hidden rounded-lg border border-white/10 bg-gradient-dark transition-all duration-200 hover:border-emerald-400/40"
                wire:click="summarizePastWeek" 
                wire:loading.class="opacity-75 border-emerald-400/60"
                wire:target="summarizePastWeek">
                <div class="pointer-events-none absolute inset-0 bg-dot-pattern opacity-60"></div>
                <div class="relative p-4">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-md bg-emerald-500/10">
                            <x-icon name="scroll-text" class="h-5 w-5 text-emerald-300" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white">Weekly Summary</h3>
                            <p class="text-sm text-gray-400">Insights from recent entries</p>
                        </div>
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/5 text-emerald-300 transition-transform duration-200 group-hover:translate-x-0.5">
                            <svg wire:loading.remove wire:target="summarizePastWeek" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                <path fill-rule="evenodd" d="M3.25 10a.75.75 0 0 1 .75-.75h9.19L9.97 6.03a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.22-3.22H4A.75.75 0 0 1 3.25 10Z" clip-rule="evenodd" />
                            </svg>
                            <div wire:loading wire:target="summarizePastWeek" class="h-4 w-4 animate-spin rounded-full border-2 border-emerald-400 border-t-transparent"></div>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Chat Card -->
            <div class="group relative cursor-pointer overflow-hidden rounded-lg border border-white/10 bg-gradient-dark transition-all duration-200 hover:border-violet-400/40"
                wire:click="startQuickChat" 
                wire:loading.class="opacity-75 border-violet-400/60"
                wire:target="startQuickChat">
                <div class="pointer-events-none absolute inset-0 bg-dot-pattern opacity-60"></div>
                <div class="relative p-4">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-md bg-violet-500/10">
                            <x-icon name="flash-outline" class="h-5 w-5 text-violet-300" />
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold text-white">Quick Chat</h3>
                            </div>
                            <p class="text-sm text-gray-400">Private chat, not saved</p>
                        </div>
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/5 text-violet-300 transition-transform duration-200 group-hover:translate-x-0.5">
                            <svg wire:loading.remove wire:target="startQuickChat" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                <path fill-rule="evenodd" d="M3.25 10a.75.75 0 0 1 .75-.75h9.19L9.97 6.03a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.22-3.22H4A.75.75 0 0 1 3.25 10Z" clip-rule="evenodd" />
                            </svg>
                            <div wire:loading wire:target="startQuickChat" class="h-4 w-4 animate-spin rounded-full border-2 border-violet-400 border-t-transparent"></div>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Review Past Memos Card -->
            <div class="group relative cursor-pointer overflow-hidden rounded-lg border border-white/10 bg-gradient-dark transition-all duration-200 hover:border-amber-400/40"
                wire:click="reviewPastMemos" 
                wire:loading.class="opacity-75 border-amber-400/60"
                wire:target="reviewPastMemos">
                <div class="pointer-events-none absolute inset-0 bg-dot-pattern opacity-60"></div>
                <div class="relative p-4">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-md bg-amber-500/10">
                            <x-icon name="clock" class="h-5 w-5 text-amber-300" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white">Review Past Memos</h3>
                            <p class="text-sm text-gray-400">Analyze patterns in your writing</p>
                        </div>
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/5 text-amber-300 transition-transform duration-200 group-hover:translate-x-0.5">
                            <svg wire:loading.remove wire:target="reviewPastMemos" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                <path fill-rule="evenodd" d="M3.25 10a.75.75 0 0 1 .75-.75h9.19L9.97 6.03a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.22-3.22H4A.75.75 0 0 1 3.25 10Z" clip-rule="evenodd" />
                            </svg>
                            <div wire:loading wire:target="reviewPastMemos" class="h-4 w-4 animate-spin rounded-full border-2 border-amber-400 border-t-transparent"></div>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Therapy Chat CTA (featured) -->
    <div class="group relative mt-12 overflow-hidden rounded-xl border border-blue-500/20 bg-gradient-dark">
        <div class="pointer-events-none absolute inset-0 bg-circuit-pattern opacity-50"></div>
        <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-blue-500/10 blur-3xl"></div>

        <div class="relative p-6">
            <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-500/15 ring-1 ring-blue-400/20">
                        <x-icon name="brain" class="h-6 w-6 text-blue-300" />
                    </div>
                    <div class="max-w-2xl">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-blue-300/70">Featured Session</p>
                        <h3 class="mt-1 text-xl font-semibold text-white">Ready for a deeper conversation?</h3>
                        <p class="mt-2 text-sm text-gray-400">
                            I've analyzed your recent entries and noticed some interesting patterns. Let's explore them
                            together.
                        </p>
                    </div>
                </div>

                <button wire:click="startTherapySession" 
                    wire:loading.class="opacity-75 cursor-wait"
                    wire:target="startTherapySession"
                    class="inline-flex min-w-[220px] cursor-pointer items-center justify-center gap-2 rounded-md bg-blue-600 px-4 py-3 text-sm font-medium text-white transition hover:bg-blue-500">
                    <x-icon wire:loading.remove wire:target="startTherapySession" name="chatbubbles-outline" class="h-4 w-4 text-blue-100" />
                    <div wire:loading wire:target="startTherapySession" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    <span>Start Therapy Session</span>
                    <svg wire:loading.remove wire:target="startTherapySession" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-blue-100">
                        <path fill-rule="evenodd" d="M3.25 10a.75.75 0 0 1 .75-.75h9.19L9.97 6.03a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.22-3.22H4A.75.75 0 0 1 3.25 10Z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Weekly Summary Modal -->
    @if($showSummaryModal)
        <div x-data="{ show: true }" x-show="show" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            @keydown.escape.window="show = false; $wire.closeSummaryModal()"
            @click.self="show = false; $wire.closeSummaryModal()">
            <div class="bg-card border border-white/10 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[85vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150"
                @click.stop>

                {{-- Header --}}
                <div class="p-6 border-b border-white/10 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                            <x-icon name="scroll-text" class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white">Weekly Summary</h3>
                            <p class="text-xs text-muted">Insights & reflections from your recent entries</p>
                        </div>
                    </div>
                    <button type="button" @click="show = false; $wire.closeSummaryModal()"
                        class="p-2 text-muted hover:text-white hover:bg-white/10 rounded-lg transition-colors cursor-pointer"
                        aria-label="Close modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Content --}}
                <div class="p-6 overflow-y-auto flex-1 min-h-0 space-y-4 overscroll-contain scrollbar-none [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                    <div wire:loading wire:target="loadWeeklySummary" class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="w-10 h-10 border-2 border-emerald-400 border-t-transparent rounded-full animate-spin mb-4"></div>
                        <p class="text-sm text-muted">Analyzing your week & preparing report...</p>
                    </div>

                    @if($summaryLoading)
                        <div x-init="$wire.loadWeeklySummary()" wire:loading.remove wire:target="loadWeeklySummary" class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="w-10 h-10 border-2 border-emerald-400 border-t-transparent rounded-full animate-spin mb-4"></div>
                            <p class="text-sm text-muted">Analyzing your week & preparing report...</p>
                        </div>
                    @elseif(!empty($weeklySummary))
                        <div wire:loading.remove wire:target="loadWeeklySummary" class="space-y-4">
                            @foreach($this->parsedWeeklySummary as $section)
                                <div class="rounded-xl border border-white/10 bg-white/[0.02] p-5 shadow-sm transition-all hover:border-white/15">
                                    <div class="flex items-center gap-2 pb-2.5 mb-3 border-b border-white/10">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-blue-400">
                                            {{ $section['title'] }}
                                        </h4>
                                    </div>
                                    <div class="prose prose-invert max-w-none text-sm leading-relaxed prose-p:text-gray-300 prose-p:my-2 prose-strong:text-white prose-ul:list-none prose-ul:pl-0 prose-ol:pl-4 prose-li:list-none prose-li:pl-0 prose-li:my-1.5 prose-li:text-gray-300 prose-table:w-full prose-table:my-3 prose-th:text-blue-300 prose-th:bg-white/5 prose-th:p-2.5 prose-th:text-xs prose-th:uppercase prose-th:border prose-th:border-white/10 prose-td:p-2.5 prose-td:border prose-td:border-white/10 prose-td:text-gray-300">
                                        {!! \Illuminate\Support\Str::markdown($section['content']) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div wire:loading.remove wire:target="loadWeeklySummary" class="text-center py-10">
                            <p class="text-sm text-muted">No summary generated yet. Click the card to create one.</p>
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="p-4 border-t border-white/10 flex justify-end bg-black/20 shrink-0">
                    <button type="button" @click="show = false; $wire.closeSummaryModal()"
                        class="px-4 py-2 text-sm font-medium text-white border border-white/10 rounded-lg hover:bg-white/5 transition-colors cursor-pointer">
                        Close
                    </button>
                </div>

            </div>
        </div>
    @endif


    <!-- Quick Chat Modal -->
    @if($showQuickChatModal)
        <div x-data="{ show: true }" x-show="show" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            @keydown.escape.window="show = false; $wire.closeQuickChatModal()"
            @click.self="show = false; $wire.closeQuickChatModal()">
            <div class="bg-card border border-white/10 rounded-lg max-w-2xl w-full mx-4 h-[600px] flex flex-col shadow-xl animate-in fade-in zoom-in-95 duration-150"
                @click.stop>
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-white/10">
                    <h3 class="text-lg font-semibold text-white">Quick Chat</h3>
                    <button type="button" @click="show = false; $wire.closeQuickChatModal()"
                        class="p-1 text-muted hover:text-white hover:bg-white/10 rounded-lg transition-colors cursor-pointer"
                        aria-label="Close quick chat">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                <!-- Messages & Input container with Alpine for optimistic rendering -->
                <div class="flex-1 flex flex-col overflow-hidden"
                    x-data="{
                        pendingMessage: '',
                        scrollToBottom() {
                            $nextTick(() => {
                                const container = this.$refs.quickChatScroll;
                                if (container) {
                                    container.scrollTop = container.scrollHeight;
                                }
                            });
                        },
                        submitMessage() {
                            const form = this.$refs.quickChatForm;
                            const textarea = form ? form.querySelector('textarea') : null;
                            const msg = textarea ? textarea.value.trim() : '';
                            if (!msg) return;

                            this.pendingMessage = msg;
                            textarea.value = '';
                            textarea.style.height = 'auto';
                            this.scrollToBottom();

                            $wire.sendQuickChat(msg).then(() => {
                                this.pendingMessage = '';
                                this.scrollToBottom();
                            }).catch(() => {
                                this.pendingMessage = '';
                                this.scrollToBottom();
                            });
                        }
                    }"
                    x-init="scrollToBottom()"
                    x-effect="scrollToBottom()">

                    <!-- Messages -->
                    <div x-ref="quickChatScroll" class="flex-1 overflow-y-auto p-4 space-y-4 scrollbar-none">
                        @foreach($quickChatMessages as $message)
                            <div class="flex {{ $message['sender'] === 'user' ? 'justify-end' : 'justify-start' }}">
                                <div
                                    class="max-w-[80%] {{ $message['sender'] === 'user' ? 'bg-primary text-primary-foreground' : 'bg-muted/20 text-white' }} rounded-lg p-3">
                                    <p class="text-sm whitespace-pre-wrap">{{ $message['content'] }}</p>
                                    <p class="text-xs opacity-70 mt-1">{{ $message['timestamp'] }}</p>
                                </div>
                            </div>
                        @endforeach

                        <!-- Optimistic Pending User Message -->
                        <div x-show="pendingMessage" x-cloak class="flex justify-end">
                            <div class="max-w-[80%] bg-primary text-primary-foreground rounded-lg p-3">
                                <p class="text-sm whitespace-pre-wrap" x-text="pendingMessage"></p>
                                <p class="text-xs opacity-70 mt-1">Just now</p>
                            </div>
                        </div>

                        <!-- Thinking Indicator -->
                        <div wire:loading wire:target="sendQuickChat" class="flex justify-start">
                            <div class="bg-muted/20 rounded-lg p-3">
                                <div class="flex items-center space-x-2">
                                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                                    <span class="text-sm text-muted">Thinking...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input -->
                    <div class="p-4 border-t border-white/10" x-ref="quickChatForm" @submit.prevent="submitMessage()">
                        <x-chat-form wire-model="quickChatInput"
                            placeholder="Share your thoughts..." :is-disabled="$quickChatLoading" :is-typing="$quickChatLoading"
                            submit-icon="send" typing-icon="stop" :use-client-submit="true" form-id="quick-chat-form" />
                    </div>
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
