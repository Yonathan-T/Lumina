<div class="space-y-6">
    <!-- Flash Messages -->
    @if (session('success'))
        <div id="success-message"
            class="bg-gradient-dark card-highlight border border-white/10 Rounded-lg px-6 py-4 shadow-lg backdrop-blur-sm border border-green-500/20">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="font-medium text-white">{{ session('success') }}</span>
                </div>
                <button onclick="dismissMessage('success-message')"
                    class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div id="error-message"
            class="bg-gradient-dark card-highlight rounded-lg px-6 py-4 shadow-lg backdrop-blur-sm border border-red-500/20">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span class="font-medium text-white">{{ session('error') }}</span>
                </div>
                <button onclick="dismissMessage('error-message')" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Export Data Card -->
    <div class="bg-gradient-dark card-highlight rounded-lg overflow-hidden">
        <div class="p-6 border-b border-white/10">
            <h3 class="text-xl font-semibold text-white">Export Data</h3>
            <p class="text-sm text-gray-400 mt-1">Download your journal entries, chat conversations</p>
        </div>

        <div class="p-6">
            <button type="button" wire:click="exportData" wire:loading.attr="disabled" wire:target="exportData"
                class="w-full px-4 py-3 border border-white/10 hover:bg-blue-300/15 rounded-lg transition-all duration-200 text-left flex items-center justify-between group disabled:opacity-50 disabled:cursor-not-allowed">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center group-hover:bg-blue-500/20 transition-colors">
                        <svg wire:loading.remove wire:target="exportData" class="w-5 h-5 text-blue-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                        </svg>

                        <svg wire:loading wire:target="exportData" class="w-5 h-5 text-blue-400 animate-spin"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-white">Export All Journal Entries</p>
                        <p class="text-xs text-gray-400">We'll email you a secure download link</p>
                    </div>
                </div>

                <svg class="w-5 h-5 text-gray-400 group-hover:text-white transition-colors" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
            <button type="button" wire:click="exportConversation" wire:loading.attr="disabled"
                wire:target="exportConversation"
                class="w-full mt-4 px-4 py-3 border border-white/10 hover:bg-blue-300/15 rounded-lg transition-all duration-200 text-left flex items-center justify-between group disabled:opacity-50 disabled:cursor-not-allowed">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center group-hover:bg-blue-500/20 transition-colors">
                        <svg wire:loading.remove wire:target="exportConversation" class="w-5 h-5 text-blue-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                        </svg>

                        <svg wire:loading wire:target="exportConversation" class="w-5 h-5 text-blue-400 animate-spin"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-white">Export All Chat Conversations</p>
                        <p class="text-xs text-gray-400">We'll email you a secure download link</p>
                    </div>
                </div>

                <svg class="w-5 h-5 text-gray-400 group-hover:text-white transition-colors" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <p class="text-xs text-gray-500 px-1 mt-3">
                Your data will be exported in JSON format and sent to your email. The download link expires in 24 hours.
            </p>
        </div>
    </div>

    <!-- Delete Account Card -->
    <div class="bg-gradient-dark card-highlight rounded-lg overflow-hidden border border-red-500/20">
        <div class="p-6 border-b border-white/10 bg-red-500/5">
            <h3 class="text-xl font-semibold text-white">Danger Zone</h3>
            <p class="text-sm text-gray-400 mt-1">Permanently delete your account and all data</p>
        </div>

        <div class="p-6 bg-red-500/5">
            <button type="button" wire:click="confirmDelete"
                class="w-full px-4 py-3 border border-red-500/30 hover:bg-red-300/15 rounded-lg transition-all duration-200 text-left flex items-center justify-between group">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-red-500/10 flex items-center justify-center group-hover:bg-red-500/20 transition-colors">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-white">Delete Account</p>
                        <p class="text-xs text-gray-400">Permanently delete all your data</p>
                    </div>
                </div>

                <svg class="w-5 h-5 text-gray-400 group-hover:text-red-400 transition-colors" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            @if ($showDeleteConfirm)
                <div class="mt-4 bg-red-900/20 border border-red-500/30 p-4 rounded-lg">
                    <div class="mb-2 font-bold text-red-400">⚠️ Are you absolutely sure?</div>
                    <div class="text-gray-400 text-sm mb-4">
                        This will permanently delete your account, all your journal entries, tags, conversations, and any
                        other
                        data. This action cannot be undone.
                    </div>
                    <div class="flex gap-2">
                        <button type="button" wire:click="deleteAccount"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                            Yes, Delete Everything
                        </button>
                        <button type="button" wire:click="$set('showDeleteConfirm', false)"
                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                            Cancel
                        </button>
                    </div>
                </div>
            @endif

            <p class="text-xs text-gray-500 px-1 mt-3">
                Once you delete your account, there is no going back. All your data will be permanently erased.
            </p>
        </div>
    </div>

</div>

<script>
    // Auto-dismiss messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function () {
        const successMessage = document.getElementById('success-message');
        const errorMessage = document.getElementById('error-message');

        if (successMessage) {
            setTimeout(() => {
                dismissMessage('success-message');
            }, 5000);
        }

        if (errorMessage) {
            setTimeout(() => {
                dismissMessage('error-message');
            }, 5000);
        }
    });

    // Manual dismiss function
    function dismissMessage(messageId) {
        const message = document.getElementById(messageId);
        if (message) {
            message.style.opacity = '0';
            message.style.transform = 'translateY(-10px)';
            message.style.transition = 'all 0.3s ease-out';

            setTimeout(() => {
                message.remove();
            }, 300);
        }
    }
</script>