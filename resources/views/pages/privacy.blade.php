<x-layout :patternOnBody="true" :showNav="true" :showSidebar="false">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {{-- Breadcrumb / Back --}}
        <div class="mb-8">
            <a href="{{ route('landingPage') }}" wire:navigate.hover
                class="inline-flex items-center text-sm text-gray-400 hover:text-white transition-colors gap-2 px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Back to Home</span>
            </a>
        </div>

        {{-- Hero Header --}}
        <div class="mb-10 text-center sm:text-left">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-500/10 border border-teal-500/20 text-xs font-medium text-teal-400 mb-4">
                <span class="w-1.5 h-1.5 rounded-full bg-teal-400 animate-pulse"></span>
                <span>Privacy First Architecture</span>
            </div>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-white font-inter">
                Privacy Policy
            </h1>
            <p class="mt-3 text-base sm:text-lg text-gray-400 max-w-3xl leading-relaxed">
                Your thoughts, memories, and vulnerable reflections belong exclusively to you. Lumina is engineered around strict privacy isolation, zero advertising, and Bring-Your-Own-Key (BYOK) architecture.
            </p>
            <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-gray-500">
                <span>Effective Date: October 2026</span>
                <span>•</span>
                <span>Version 2.0</span>
                <span>•</span>
                <span class="text-emerald-400">GDPR & CCPA Compliant</span>
            </div>
        </div>

        {{-- Quick Stat Highlights (Dashboard Style) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-12">
            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Data Monetization</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-white mb-1">0% Sold</div>
                <p class="text-xs text-gray-400 leading-relaxed">We never sell, rent, or commercialize your personal journal entries or private data.</p>
            </div>

            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">AI Training Isolation</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-white mb-1">BYOK Isolated</div>
                <p class="text-xs text-gray-400 leading-relaxed">Your Google Gemini and ElevenLabs API calls run with your keys and are not used to train foundation models.</p>
            </div>

            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Your Ownership</span>
                    <div class="w-8 h-8 rounded-lg bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-white mb-1">100% Exportable</div>
                <p class="text-xs text-gray-400 leading-relaxed">Download complete JSON archives or branded PDF books anytime with single-click export.</p>
            </div>
        </div>

        {{-- Policy Document Body --}}
        <div class="space-y-8">
            {{-- Section 1 --}}
            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400 font-bold text-sm">
                        01
                    </div>
                    <h2 class="text-xl font-bold text-white">Information We Collect</h2>
                </div>
                <div class="text-gray-300 text-sm leading-relaxed space-y-3">
                    <p>
                        Lumina collects only the minimal data required to provide you with a resilient, reflective journaling experience:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-300 pl-2">
                        <li><strong class="text-white">Account Information:</strong> Your name, email address, and securely hashed passwords (Argon2id/Bcrypt) used exclusively for authentication.</li>
                        <li><strong class="text-white">Journal Entries & Media:</strong> Your entries, mood tags, titles, timestamps, and optional banner images. All entries are row-scoped strictly to your authenticated user account.</li>
                        <li><strong class="text-white">Third-Party API Credentials:</strong> Your personal API keys (such as Google Gemini and ElevenLabs) provided under our Bring-Your-Own-Key model. These are encrypted with AES-256 before disk storage.</li>
                        <li><strong class="text-white">Billing Records:</strong> Handled securely by our merchant of record (Polar). Lumina never sees or stores full credit card numbers or banking secrets.</li>
                    </ul>
                </div>
            </div>

            {{-- Section 2 --}}
            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 font-bold text-sm">
                        02
                    </div>
                    <h2 class="text-xl font-bold text-white">How Artificial Intelligence Interacts With Your Data</h2>
                </div>
                <div class="text-gray-300 text-sm leading-relaxed space-y-4">
                    <p>
                        Unlike conventional AI applications that train corporate models on user journal entries, Lumina employs an isolated architecture:
                    </p>
                    <div class="rounded-lg bg-blue-500/10 border border-blue-500/20 p-4 text-xs text-blue-300 space-y-2">
                        <div class="font-semibold text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>The Bring-Your-Own-Key (BYOK) Guarantee</span>
                        </div>
                        <p class="leading-relaxed">
                            When you interact with Lumi AI, prompts are communicated using your personal Google Gemini API key or narrated through ElevenLabs. Under Google AI Studio and ElevenLabs API terms, data submitted via paid/BYOK developer API endpoints is not used to train foundation models.
                        </p>
                    </div>
                    <p>
                        Ephemeral chat interactions (such as Quick Chat) are discarded immediately and never permanently persisted to database storage unless you explicitly choose to archive the session.
                    </p>
                </div>
            </div>

            {{-- Section 3 --}}
            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 font-bold text-sm">
                        03
                    </div>
                    <h2 class="text-xl font-bold text-white">Data Retention & Deletion Rights</h2>
                </div>
                <div class="text-gray-300 text-sm leading-relaxed space-y-3">
                    <p>
                        You maintain unconditional control over your data lifecycle:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-300 pl-2">
                        <li><strong class="text-white">Individual Entry Deletion:</strong> Deleting an entry immediately executes hard database deletion and deletes any associated banner images from storage.</li>
                        <li><strong class="text-white">Account Expungement:</strong> When you delete your Lumina account from Settings, all your database rows, entries, conversations, tags, and encrypted keys are purged permanently.</li>
                        <li><strong class="text-white">Data Portability:</strong> You can download a complete archive of your journal history in standardized JSON and PDF formats at any time.</li>
                    </ul>
                </div>
            </div>

            {{-- Section 4 --}}
            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 font-bold text-sm">
                        04
                    </div>
                    <h2 class="text-xl font-bold text-white">Cookies, Telemetry & Trackers</h2>
                </div>
                <div class="text-gray-300 text-sm leading-relaxed space-y-3">
                    <p>
                        Lumina believes web analytics have no place in a private journal:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-300 pl-2">
                        <li>We do <strong class="text-white">not</strong> use third-party tracking pixels, Facebook Pixel, Google Analytics, or invasive ad tech.</li>
                        <li>Cookies used are strictly functional HTTP session cookies required to keep you signed in securely (<code class="text-emerald-400 bg-white/5 px-1 py-0.5 rounded text-xs">HttpOnly</code>, <code class="text-emerald-400 bg-white/5 px-1 py-0.5 rounded text-xs">SameSite=Lax</code>, and <code class="text-emerald-400 bg-white/5 px-1 py-0.5 rounded text-xs">Secure</code>).</li>
                    </ul>
                </div>
            </div>

            {{-- Section 5 --}}
            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 font-bold text-sm">
                        05
                    </div>
                    <h2 class="text-xl font-bold text-white">Contact & Data Protection Officer</h2>
                </div>
                <div class="text-gray-300 text-sm leading-relaxed space-y-3">
                    <p>
                        For any inquiries, requests to exercise your data rights, or clarification regarding our privacy practices, contact us directly:
                    </p>
                    <div class="p-4 rounded-lg bg-white/5 border border-white/10 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-white font-medium">Privacy Inquiries:</span>
                            <span class="text-gray-400 ml-1">privacy@lumina-journal.com</span>
                        </div>
                        <a href="https://github.com/Yonathan-T/Lumina/issues" target="_blank"
                            class="inline-flex items-center gap-1.5 text-teal-400 hover:text-teal-300 underline font-medium">
                            <span>Open GitHub Discussion</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Legal Switcher Nav --}}
        <div class="mt-12 pt-8 border-t border-white/10 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-gray-400">
            <span>Explore other legal documentation:</span>
            <div class="flex items-center gap-4">
                <a href="{{ route('terms') }}" wire:navigate.hover class="text-teal-400 hover:text-teal-300 underline">Terms of Service</a>
                <span>•</span>
                <a href="{{ route('security') }}" wire:navigate.hover class="text-teal-400 hover:text-teal-300 underline">Security Architecture</a>
            </div>
        </div>
    </div>
</x-layout>
