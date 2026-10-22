<div
    class="bg-gradient-dark rounded-lg p-5 sm:p-8 shadow-2xs card-highlight space-y-6 sm:space-y-8"
    x-data="{ confirmGeminiRemoval: false, confirmElevenLabsRemoval: false }">

    {{-- Header --}}
    <div>
        <h2 class="text-2xl font-bold mb-2 text-white">API Keys</h2>
        <p class="text-muted">Configure your personal API keys for AI chat, reflection, and voice narration</p>
    </div>

    {{-- Secure Storage Notice --}}
    <div class="flex items-start gap-3 rounded-lg bg-white/5 border border-white/10 p-4">
        <svg class="h-5 w-5 flex-shrink-0 text-gray-300 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
            </path>
        </svg>
        <div>
            <p class="text-sm font-medium text-white">Secure Storage</p>
            <p class="text-xs text-muted">Your API keys are encrypted with AES-256 and stored securely in your private account</p>
        </div>
    </div>

    {{-- 1. Google Gemini API Key Card --}}
    <div class="rounded-xl border border-white/10 bg-white/[0.02] bg-subtle-dots p-5 sm:p-6 space-y-4 relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold text-white">Google Gemini API Key</h3>
                <p class="text-sm text-muted">Powers AI chat, self-reflection sessions, and weekly summaries</p>
            </div>

            @if($this->hasApiKey())
                @if($isKeyVerified)
                    <div class="self-start sm:self-auto flex items-center gap-2 rounded-full bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 shrink-0">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        </span>
                        <span class="text-xs font-medium text-emerald-400">Active</span>
                    </div>
                @else
                    <div class="self-start sm:self-auto flex items-center gap-2 rounded-full bg-amber-500/10 border border-amber-500/20 px-3 py-1 shrink-0">
                        <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                        <span class="text-xs font-medium text-amber-400">Saved (Unverified)</span>
                    </div>
                @endif
            @else
                <div class="self-start sm:self-auto flex items-center gap-2 rounded-full bg-white/5 border border-white/10 px-3 py-1 shrink-0">
                    <span class="h-2 w-2 rounded-full bg-gray-500"></span>
                    <span class="text-xs font-medium text-muted">Not Configured</span>
                </div>
            @endif
        </div>

        <div>
            <label class="block text-gray-300 mb-1 text-sm" for="gemini-api-key">Gemini API Key</label>
            <div class="relative" x-data="{ showKey: false }">
                <input id="gemini-api-key" :type="showKey ? 'text' : 'password'" wire:model.live.debounce.300ms="apiKey"
                    placeholder="{{ $hasSavedGeminiKey ? '••••••••••••••••••••••••••••••••' : 'Enter your Gemini API key' }}"
                    class="w-full px-4 py-2.5 pr-12 rounded bg-gradient-dark text-white border border-gray-700 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-white-500" />
                <button type="button" @click="showKey = !showKey"
                    class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-gray-400 hover:text-white transition-colors cursor-pointer"
                    aria-label="Toggle Gemini API Key visibility">
                    <svg x-show="showKey" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21">
                        </path>
                    </svg>
                    <svg x-show="!showKey" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                </button>
            </div>
            @if($hasSavedGeminiKey)
                <p class="mt-1.5 text-xs text-muted">Your key is stored securely with AES-256 encryption. Click the eye icon to view or hide it.</p>
            @endif
        </div>

        @if($statusMessage)
            <div class="rounded-lg p-3 {{ $status === 'success' ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400' : ($status === 'error' ? 'bg-red-500/10 border border-red-500/20 text-red-400' : 'bg-white/5 border border-white/10 text-gray-300') }} text-sm">
                {{ $statusMessage }}
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-3 pt-1">
            <button wire:click="testConnection" wire:loading.attr="disabled"
                class="px-4 py-2 rounded-md border border-white/10 bg-white/5 hover:bg-white/10 text-white font-medium text-sm transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="testConnection">Test Connection</span>
                <span wire:loading wire:target="testConnection">Testing...</span>
            </button>

            <button wire:click="saveApiKey" wire:loading.attr="disabled"
                {{ $this->canSaveGeminiKey() ? '' : 'disabled' }}
                class="px-6 py-2 bg-white text-gray-900 rounded-md font-semibold text-sm hover:bg-gray-200 transition disabled:opacity-40 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="saveApiKey">Save API Key</span>
                <span wire:loading wire:target="saveApiKey">Saving...</span>
            </button>

            @if($this->hasApiKey())
                <button type="button" @click="confirmGeminiRemoval = true"
                    class="px-4 py-2 rounded-md border border-red-500/20 bg-red-500/10 hover:bg-red-500/20 text-red-400 font-medium text-sm transition cursor-pointer">
                    Remove Key
                </button>
            @endif

            @if($hasSavedGeminiKey && trim($apiKey) === trim($originalSavedGeminiKey))
                <span class="text-xs text-muted">Key is saved. To update, enter a new key and test connection.</span>
            @endif
        </div>

        {{-- How to get Gemini API Key --}}
        <div class="mt-4 rounded-lg border border-white/5 bg-black/30 p-4">
            <h5 class="mb-2 text-xs font-semibold uppercase tracking-wider text-muted">How to get your Gemini API key:</h5>
            <ol class="space-y-1.5 text-xs text-gray-300 list-decimal list-inside">
                <li>Visit <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-white hover:underline font-medium">Google AI Studio</a></li>
                <li>Sign in with your Google account</li>
                <li>Click <strong>Create API Key</strong></li>
                <li>Copy the key, paste it above, and click <strong>Test Connection</strong></li>
            </ol>
        </div>
    </div>

    {{-- 2. ElevenLabs Voice API Key Card --}}
    <div class="rounded-xl border border-white/10 bg-white/[0.02] bg-subtle-dots p-5 sm:p-6 space-y-4 relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold text-white">ElevenLabs API Key</h3>
                <p class="text-sm text-muted">Powers high-quality AI voice narration for your journal entries</p>
            </div>

            @if($this->hasElevenLabsKey())
                @if($isElevenLabsKeyVerified)
                    <div class="self-start sm:self-auto flex items-center gap-2 rounded-full bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 shrink-0">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        </span>
                        <span class="text-xs font-medium text-emerald-400">Active</span>
                    </div>
                @else
                    <div class="self-start sm:self-auto flex items-center gap-2 rounded-full bg-amber-500/10 border border-amber-500/20 px-3 py-1 shrink-0">
                        <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                        <span class="text-xs font-medium text-amber-400">Saved (Unverified)</span>
                    </div>
                @endif
            @else
                <div class="self-start sm:self-auto flex items-center gap-2 rounded-full bg-white/5 border border-white/10 px-3 py-1 shrink-0">
                    <span class="h-2 w-2 rounded-full bg-gray-500"></span>
                    <span class="text-xs font-medium text-muted">Not Configured</span>
                </div>
            @endif
        </div>

        <div>
            <label class="block text-gray-300 mb-1 text-sm" for="elevenlabs-api-key">ElevenLabs API Key</label>
            <div class="relative" x-data="{ showElevenLabsKey: false }">
                <input id="elevenlabs-api-key" :type="showElevenLabsKey ? 'text' : 'password'" wire:model.live.debounce.300ms="elevenLabsKey"
                    placeholder="{{ $hasSavedElevenLabsKey ? '••••••••••••••••••••••••••••••••' : 'Enter your ElevenLabs API key' }}"
                    class="w-full px-4 py-2.5 pr-12 rounded bg-gradient-dark text-white border border-gray-700 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-white-500" />
                <button type="button" @click="showElevenLabsKey = !showElevenLabsKey"
                    class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-gray-400 hover:text-white transition-colors cursor-pointer"
                    aria-label="Toggle ElevenLabs API Key visibility">
                    <svg x-show="showElevenLabsKey" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21">
                        </path>
                    </svg>
                    <svg x-show="!showElevenLabsKey" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                </button>
            </div>
            @if($hasSavedElevenLabsKey)
                <p class="mt-1.5 text-xs text-muted">Your key is stored securely with AES-256 encryption. Click the eye icon to view or hide it.</p>
            @endif
        </div>

        @if($elevenLabsStatusMessage)
            <div class="rounded-lg p-3 {{ $elevenLabsStatus === 'success' ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400' : ($elevenLabsStatus === 'error' ? 'bg-red-500/10 border border-red-500/20 text-red-400' : 'bg-white/5 border border-white/10 text-gray-300') }} text-sm">
                {{ $elevenLabsStatusMessage }}
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-3 pt-1">
            <button wire:click="testElevenLabsConnection" wire:loading.attr="disabled"
                class="px-4 py-2 rounded-md border border-white/10 bg-white/5 hover:bg-white/10 text-white font-medium text-sm transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="testElevenLabsConnection">Test Connection</span>
                <span wire:loading wire:target="testElevenLabsConnection">Testing...</span>
            </button>

            <button wire:click="saveElevenLabsKey" wire:loading.attr="disabled"
                {{ $this->canSaveElevenLabsKey() ? '' : 'disabled' }}
                class="px-6 py-2 bg-white text-gray-900 rounded-md font-semibold text-sm hover:bg-gray-200 transition disabled:opacity-40 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="saveElevenLabsKey">Save API Key</span>
                <span wire:loading wire:target="saveElevenLabsKey">Saving...</span>
            </button>

            @if($this->hasElevenLabsKey())
                <button type="button" @click="confirmElevenLabsRemoval = true"
                    class="px-4 py-2 rounded-md border border-red-500/20 bg-red-500/10 hover:bg-red-500/20 text-red-400 font-medium text-sm transition cursor-pointer">
                    Remove Key
                </button>
            @endif

            @if($hasSavedElevenLabsKey && trim($elevenLabsKey) === trim($originalSavedElevenLabsKey))
                <span class="text-xs text-muted">Key is saved. To update, enter a new key and test connection.</span>
            @endif
        </div>

        {{-- Voice Preference Selector --}}
        <div class="mt-4 pt-4 border-t border-white/10 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                <div>
                    <label for="elevenlabs-voice-select" class="block text-sm font-medium text-white">Default Narration Voice</label>
                    <p class="text-xs text-muted">Choose your preferred voice for listening to journal memos</p>
                </div>

                @if($voiceSavedMessage)
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 rounded-full">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $voiceSavedMessage }}
                    </span>
                @endif
            </div>

            <div class="space-y-3">
                <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                    {{-- Hidden native select for automated test compatibility and accessibility --}}
                    <select id="elevenlabs-voice-select" wire:model.live="selectedVoiceId" class="sr-only">
                        <optgroup label="Free Tier Compatible Voices">
                            @foreach($this->presetVoices as $voice)
                                @if($voice['tier'] === 'Free')
                                    <option value="{{ $voice['id'] }}">{{ $voice['name'] }} — {{ $voice['description'] }}</option>
                                @endif
                            @endforeach
                        </optgroup>
                        <optgroup label="Paid / Creator Tier Voices">
                            @foreach($this->presetVoices as $voice)
                                @if($voice['tier'] !== 'Free')
                                    <option value="{{ $voice['id'] }}">{{ $voice['name'] }} — {{ $voice['description'] }} ({{ $voice['tier'] }})</option>
                                @endif
                            @endforeach
                        </optgroup>
                        <optgroup label="Custom Voice">
                            <option value="custom">Custom Voice ID</option>
                        </optgroup>
                    </select>

                    {{-- Custom Styled Dropdown Trigger Button --}}
                    <button type="button"
                        @click="open = !open"
                        class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl bg-[#080d19] border border-white/10 hover:border-white/25 focus:border-blue-500/60 focus:ring-2 focus:ring-blue-500/20 transition-all duration-200 cursor-pointer shadow-sm text-left group">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 shrink-0 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-white truncate">
                                        {{ $this->currentVoice['name'] ?? ($selectedVoiceId === 'custom' ? 'Custom Voice ID' : 'Select Voice') }}
                                    </span>
                                    @if($this->currentVoice)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider {{ $this->currentVoice['tier'] === 'Free' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-blue-500/10 text-blue-400 border border-blue-500/20' }}">
                                            {{ $this->currentVoice['tier'] }}
                                        </span>
                                    @elseif($selectedVoiceId === 'custom')
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider bg-amber-500/10 text-[#c6b78e] border border-amber-500/20">
                                            Custom
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400 truncate mt-0.5">
                                    {{ $this->currentVoice['description'] ?? ($selectedVoiceId === 'custom' ? ($customVoiceId ?: 'Using your personal ElevenLabs voice ID') : 'Choose a narration voice') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center text-gray-400 group-hover:text-white transition-colors">
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </button>

                    {{-- Custom Floating Menu Options --}}
                    <div x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                        class="absolute top-full left-0 right-0 z-50 mt-2 rounded-xl bg-[#080d19]/95 border border-white/10 backdrop-blur-xl shadow-2xl p-2 max-h-80 overflow-y-auto space-y-1"
                        style="display: none;">
                        
                        {{-- Free Tier Voices --}}
                        <div class="px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wider text-emerald-400/90 flex items-center justify-between">
                            <span>Free Tier Compatible</span>
                            <span class="text-[10px] text-gray-500 lowercase">zero extra credits</span>
                        </div>
                        @foreach($this->presetVoices as $voice)
                            @if($voice['tier'] === 'Free')
                                <button type="button"
                                    @click="$wire.set('selectedVoiceId', '{{ $voice['id'] }}'); open = false;"
                                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-left transition-colors cursor-pointer group {{ $selectedVoiceId === $voice['id'] ? 'bg-blue-600/15 border border-blue-500/30 text-white' : 'hover:bg-white/5 text-gray-300 hover:text-white border border-transparent' }}">
                                    <div class="min-w-0 pr-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium {{ $selectedVoiceId === $voice['id'] ? 'text-blue-400 font-semibold' : 'text-gray-200 group-hover:text-white' }}">{{ $voice['name'] }}</span>
                                            <span class="text-[10px] uppercase font-semibold px-1.5 py-0.2 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Free</span>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $voice['description'] }}</p>
                                    </div>
                                    @if($selectedVoiceId === $voice['id'])
                                        <svg class="w-4 h-4 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @endif
                                </button>
                            @endif
                        @endforeach

                        {{-- Paid Tier Voices --}}
                        <div class="px-2.5 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-wider text-blue-400/90 flex items-center justify-between border-t border-white/5 mt-1">
                            <span>Paid / Creator Tier</span>
                            <span class="text-[10px] text-gray-500 lowercase">requires ElevenLabs credits</span>
                        </div>
                        @foreach($this->presetVoices as $voice)
                            @if($voice['tier'] !== 'Free')
                                <button type="button"
                                    @click="$wire.set('selectedVoiceId', '{{ $voice['id'] }}'); open = false;"
                                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-left transition-colors cursor-pointer group {{ $selectedVoiceId === $voice['id'] ? 'bg-blue-600/15 border border-blue-500/30 text-white' : 'hover:bg-white/5 text-gray-300 hover:text-white border border-transparent' }}">
                                    <div class="min-w-0 pr-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium {{ $selectedVoiceId === $voice['id'] ? 'text-blue-400 font-semibold' : 'text-gray-200 group-hover:text-white' }}">{{ $voice['name'] }}</span>
                                            <span class="text-[10px] uppercase font-semibold px-1.5 py-0.2 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20">Paid</span>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $voice['description'] }}</p>
                                    </div>
                                    @if($selectedVoiceId === $voice['id'])
                                        <svg class="w-4 h-4 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @endif
                                </button>
                            @endif
                        @endforeach

                        {{-- Custom Voice Option --}}
                        <div class="pt-2 border-t border-white/5 mt-1">
                            <button type="button"
                                @click="$wire.set('selectedVoiceId', 'custom'); open = false;"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-left transition-colors cursor-pointer group {{ $selectedVoiceId === 'custom' ? 'bg-[#c6b78e]/15 border border-[#c6b78e]/30 text-white' : 'hover:bg-white/5 text-gray-300 hover:text-white border border-transparent' }}">
                                <div class="min-w-0 pr-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium {{ $selectedVoiceId === 'custom' ? 'text-[#c6b78e] font-semibold' : 'text-gray-200 group-hover:text-white' }}">Custom Voice ID</span>
                                        <span class="text-[10px] uppercase font-semibold px-1.5 py-0.2 rounded bg-amber-500/10 text-[#c6b78e] border border-amber-500/20">Your Choice</span>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-0.5">Use any cloned voice, community voice, or Voice Lab ID from ElevenLabs</p>
                                </div>
                                @if($selectedVoiceId === 'custom')
                                    <svg class="w-4 h-4 text-[#c6b78e] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                @endif
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Custom Voice Configuration Panel --}}
                @if($selectedVoiceId === 'custom')
                    <div class="rounded-xl border border-white/10 bg-[#080d19] p-4 space-y-3 shadow-md">
                        <div class="flex items-center justify-between">
                            <label for="custom-voice-id" class="block text-xs font-semibold uppercase tracking-wider text-[#c6b78e]">
                                Enter Custom ElevenLabs Voice ID
                            </label>
                            <span class="text-[11px] text-gray-400">Works with any cloned or community voice</span>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-2">
                            <div class="relative flex-1">
                                <input id="custom-voice-id" type="text" wire:model="customVoiceId"
                                    placeholder="e.g. 21m00Tcm4TlvDq8ikWAM or your cloned voice ID"
                                    class="w-full px-3.5 py-2.5 rounded-lg bg-black/50 text-white border border-white/10 font-mono text-sm focus:outline-none focus:border-[#c6b78e] focus:ring-1 focus:ring-[#c6b78e] placeholder:text-gray-600" />
                            </div>
                            <button type="button" wire:click="saveCustomVoice"
                                class="cursor-pointer px-4 py-2.5 bg-[#c6b78e] hover:bg-[#d4c69d] text-gray-950 font-semibold text-xs rounded-lg transition-all shadow-sm active:scale-95 shrink-0 flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Save Voice ID</span>
                            </button>
                        </div>

                        <div class="rounded-lg bg-black/40 border border-white/5 p-3 text-xs text-gray-300 space-y-1.5">
                            <div class="font-medium text-white flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#c6b78e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>How to find any voice on ElevenLabs:</span>
                            </div>
                            <p class="text-gray-400 leading-relaxed">
                                1. Open <a href="https://elevenlabs.io/app/voice-library" target="_blank" class="text-[#c6b78e] hover:underline">ElevenLabs Voice Library</a> or <a href="https://elevenlabs.io/app/voice-lab" target="_blank" class="text-[#c6b78e] hover:underline">Voice Lab (My Voices)</a>.<br>
                                2. Click any voice (community voice, accent, or your cloned voice) and copy its <strong>Voice ID</strong>.<br>
                                3. Paste the ID above and click <strong>Save Voice ID</strong>. Memo-Mate will use your chosen voice for all future narrations!
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <p class="text-xs text-muted flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Free accounts have access to default standard voices (Bella, Adam, Antoni, Arnold). Paid accounts can use library, cloned, or custom voices.</span>
            </p>
        </div>

        {{-- How to get ElevenLabs API Key --}}
        <div class="mt-4 rounded-lg border border-white/5 bg-black/30 p-4">
            <h5 class="mb-2 text-xs font-semibold uppercase tracking-wider text-muted">How to get your ElevenLabs API key:</h5>
            <ol class="space-y-2 text-xs text-gray-300 list-decimal list-inside">
                <li>Visit <a href="https://elevenlabs.io" target="_blank" class="text-white hover:underline font-medium">ElevenLabs</a> and sign in with your account</li>
                <li>In the bottom-left corner, switch your workspace/suite from <strong>ElevenCreative</strong> to <strong>ElevenAPI</strong> (or click your workspace switcher and select <strong>ElevenAPI</strong>)</li>
                <li>Click <strong>API Keys</strong> in the navigation, then click <strong>Create API Key</strong></li>
                <li>In the endpoint permissions dialog, ensure you grant <strong>Access</strong> to:
                    <ul class="mt-1.5 ml-6 space-y-1 list-disc text-gray-400">
                        <li><strong class="text-gray-200">Text to Speech</strong> &rarr; <span class="text-white font-medium">Access</span> (powers audio narration for your entries)</li>
                        <li><strong class="text-gray-200">User</strong> (under Administration) &rarr; <span class="text-white font-medium">Access</span> (required to test and verify the key)</li>
                    </ul>
                </li>
                <li>Copy the generated key, paste it into the field above, and click <strong>Test Connection</strong></li>
            </ol>
        </div>
    </div>

    {{-- Confirmation Modal for Gemini Key Removal --}}
    <div x-show="confirmGeminiRemoval" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
        @keydown.escape.window="confirmGeminiRemoval = false">
        <div @click.outside="confirmGeminiRemoval = false"
            class="w-full max-w-md rounded-lg border border-red-500/30 bg-gradient-dark p-6 shadow-2xl card-highlight bg-subtle-dots">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 shrink-0 rounded-full bg-red-500/20 flex items-center justify-center text-red-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.332 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>
                <h4 class="text-lg font-bold text-white">Remove Gemini API Key</h4>
            </div>
            <p class="text-muted mb-6 text-sm">
                Are you sure you want to remove your Gemini API key? This will disable AI chat, weekly summaries, and reflection features until a new key is added.
            </p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="confirmGeminiRemoval = false"
                    class="px-4 py-2 rounded-md border border-white/10 bg-white/5 hover:bg-white/10 text-white font-medium text-sm transition cursor-pointer">
                    Cancel
                </button>
                <button type="button" wire:click="removeApiKey" @click="confirmGeminiRemoval = false" wire:loading.attr="disabled"
                    class="px-4 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white font-medium text-sm transition disabled:opacity-50 cursor-pointer">
                    <span wire:loading.remove wire:target="removeApiKey">Yes, Remove Key</span>
                    <span wire:loading wire:target="removeApiKey">Removing...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Confirmation Modal for ElevenLabs Key Removal --}}
    <div x-show="confirmElevenLabsRemoval" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
        @keydown.escape.window="confirmElevenLabsRemoval = false">
        <div @click.outside="confirmElevenLabsRemoval = false"
            class="w-full max-w-md rounded-lg border border-red-500/30 bg-gradient-dark p-6 shadow-2xl card-highlight bg-subtle-dots">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 shrink-0 rounded-full bg-red-500/20 flex items-center justify-center text-red-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.332 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>
                <h4 class="text-lg font-bold text-white">Remove ElevenLabs API Key</h4>
            </div>
            <p class="text-muted mb-6 text-sm">
                Are you sure you want to remove your ElevenLabs voice key? You will not be able to listen to audio narrations of your entries until a new key is added.
            </p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="confirmElevenLabsRemoval = false"
                    class="px-4 py-2 rounded-md border border-white/10 bg-white/5 hover:bg-white/10 text-white font-medium text-sm transition cursor-pointer">
                    Cancel
                </button>
                <button type="button" wire:click="removeElevenLabsKey" @click="confirmElevenLabsRemoval = false" wire:loading.attr="disabled"
                    class="px-4 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white font-medium text-sm transition disabled:opacity-50 cursor-pointer">
                    <span wire:loading.remove wire:target="removeElevenLabsKey">Yes, Remove Key</span>
                    <span wire:loading wire:target="removeElevenLabsKey">Removing...</span>
                </button>
            </div>
        </div>
    </div>
</div>
