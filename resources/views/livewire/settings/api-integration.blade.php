<div
    class="relative overflow-hidden rounded-2xl border border-gray-800 bg-gradient-to-br from-gray-900 via-gray-900 to-gray-800 p-8">
    <div
        class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-gradient-to-br from-teal-500/10 to-blue-500/10 blur-3xl">
    </div>
    <div
        class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-gradient-to-tr from-blue-500/10 to-teal-500/10 blur-3xl">
    </div>

    <div class="relative z-10 bg-diagonal-lines">
        <div class="mb-8 flex items-start justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-dark ">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-white">API Integration</h3>
                        <p class="text-sm text-gray-400">Manage your AI-powered features</p>
                    </div>
                </div>
            </div>

            @if($this->hasApiKey())
                @if($isKeyVerified)
                    <div class="flex items-center gap-2 rounded-full bg-emerald-500/10 px-4 py-2 ring-1 ring-emerald-500/20">
                        <div class="relative flex h-2 w-2">
                            <span
                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        </div>
                        <span class="text-sm font-medium text-emerald-400">Active</span>
                    </div>
                @else
                    <div class="flex items-center gap-2 rounded-full bg-yellow-500/10 px-4 py-2 ring-1 ring-yellow-500/20">
                        <div class="h-2 w-2 rounded-full bg-yellow-500"></div>
                        <span class="text-sm font-medium text-yellow-400">Saved (Unverified)</span>
                    </div>
                @endif
            @else
                <div class="flex items-center gap-2 rounded-full bg-gray-500/10 px-4 py-2 ring-1 ring-gray-500/20">
                    <div class="h-2 w-2 rounded-full bg-gray-500"></div>
                    <span class="text-sm font-medium text-gray-400">Not Configured</span>
                </div>
            @endif
        </div>

        <div class="mb-6 rounded-xl border border-gray-700/50 bg-gray-800/50 p-6 backdrop-blur-sm">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-dark ">
                    <svg class="h-5 w-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white">Gemini API Key</h4>
                    <p class="text-sm text-gray-400">Configure your personal Gemini API key for AI-powered features</p>
                </div>
            </div>

            <div class="mb-4 flex items-start gap-3 rounded-lg bg-blue-500/10 p-4 ring-1 ring-blue-500/20">
                <svg class="h-5 w-5 flex-shrink-0 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                    </path>
                </svg>
                <div>
                    <p class="text-sm font-medium text-blue-300">Secure Storage</p>
                    <p class="text-xs text-blue-400/80">Your API key is encrypted and stored securely in our database
                    </p>
                </div>
            </div>

            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-gray-300">API Key</label>
                <div class="relative">
                    <input type="{{ $showKey ? 'text' : 'password' }}" wire:model="apiKey"
                        placeholder="Enter your Gemini API key"
                        class="w-full rounded-lg border border-gray-600 bg-gray-900/50 px-4 py-3 pr-12 text-white placeholder-gray-500 transition-all focus:border-blue-500/10 focus:outline-none focus:ring-2 focus:ring-blue-500/10" />
                    <button type="button" wire:click="toggleShowKey"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 transition-colors hover:text-white">
                        @if($showKey)
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21">
                                </path>
                            </svg>
                        @else
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                </path>
                            </svg>
                        @endif
                    </button>
                </div>
            </div>

            @if($statusMessage)
                <div
                    class="mb-4 rounded-lg p-3 {{ $status === 'success' ? 'bg-emerald-500/10 ring-1 ring-emerald-500/20' : ($status === 'error' ? 'bg-red-500/10 ring-1 ring-red-500/20' : 'bg-blue-500/10 ring-1 ring-blue-500/20') }}">
                    <p
                        class="text-sm {{ $status === 'success' ? 'text-emerald-400' : ($status === 'error' ? 'text-red-400' : 'text-blue-400') }}">
                        {{ $statusMessage }}
                    </p>
                </div>
            @endif

            <div class="flex flex-wrap gap-3">

                <button wire:click="testConnection" wire:loading.attr="disabled"
                    class="flex items-center gap-2 rounded-lg bg-gray-700/50 px-4 py-2.5 text-sm font-medium text-white transition-all hover:bg-gray-700 disabled:opacity-50">

                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>

                    <span wire:loading.remove wire:target="testConnection">Test Connection</span>
                    <span wire:loading wire:target="testConnection">Testing...</span>
                </button>

                <button wire:click="saveApiKey" wire:loading.attr="disabled"
                    class="flex items-center gap-2 rounded-lg bg-gradient-dark px-4 py-2.5 text-sm font-medium text-white transition-all hover:from-teal-500 hover:to-blue-500 disabled:opacity-50"
                    {{ $isKeyVerified ? '' : 'disabled' }}>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span wire:loading.remove wire:target="saveApiKey">Save API Key</span>
                    <span wire:loading wire:target="saveApiKey">Saving...</span>
                </button>

                @if($this->hasApiKey())
                    <button wire:click="openConfirmationModal" wire:loading.attr="disabled"
                        class="flex items-center gap-2 rounded-lg bg-red-500/10 px-4 py-2.5 text-sm font-medium text-red-400 ring-1 ring-red-500/20 transition-all hover:bg-red-500/20 disabled:opacity-50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                        <span wire:loading.remove wire:target="removeApiKey">Remove Key</span>
                        <span wire:loading wire:target="removeApiKey">Removing...</span>
                    </button>
                @endif

            </div>

        </div>

        <div class="rounded-xl border border-gray-700/50 bg-gray-800/30 p-6">
            <h4 class="mb-4 text-lg font-semibold text-white">How to get your API key:</h4>
            <div class="space-y-3">
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gradient-dark  text-sm font-bold text-white">
                        1</div>
                    <div class="pt-1">
                        <p class="text-gray-300">Visit <a href="https://aistudio.google.com/app/apikey" target="_blank"
                                class="font-medium text-teal-400 hover:text-teal-300 underline">Google AI Studio</a>
                        </p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gradient-dark  text-sm font-bold text-white">
                        2</div>
                    <div class="pt-1">
                        <p class="text-gray-300">Sign in with your Google account</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gradient-dark  text-sm font-bold text-white">
                        3</div>
                    <div class="pt-1">
                        <p class="text-gray-300">Click "Create API Key" or "Get API Key"</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gradient-dark  text-sm font-bold text-white">
                        4</div>
                    <div class="pt-1">
                        <p class="text-gray-300">Copy the key and paste it above</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gradient-dark  text-sm font-bold text-white">
                        5</div>
                    <div class="pt-1">
                        <p class="text-gray-300">Click "Test Connection" to verify it works</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($isConfirmingRemoval)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0, 0, 0, 0.75);"
            wire:keydown.escape="closeConfirmationModal" tabindex="0">

            <div @click.away="closeConfirmationModal"
                class="w-full max-w-md rounded-xl border border-red-500/50 bg-gradient-dark p-6 shadow-2xl transition-all">

                <div class="flex items-center gap-4 mb-4">
                    <div class="h-10 w-10 flex-shrink-0 rounded-full bg-red-500/20 flex items-center justify-center">
                        <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.332 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </div>
                    <h5 class="text-xl font-bold text-white">Confirm Key Removal</h5>
                </div>

                <p class="text-gray-300 mb-6">
                    Are you sure you want to remove your Gemini API key? This action will disable all AI-powered features
                    until a new key is provided.
                </p>

                <div class="flex justify-end gap-3">
                    <button wire:click="closeConfirmationModal"
                        class="rounded-lg bg-gray-700/50 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-700">
                        Cancel
                    </button>

                    <button wire:click="removeApiKey"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700">
                        Yes, Remove Key
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>