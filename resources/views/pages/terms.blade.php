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
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-xs font-medium text-blue-400 mb-4">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                <span>User Agreement</span>
            </div>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-white font-inter">
                Terms of Service
            </h1>
            <p class="mt-3 text-base sm:text-lg text-gray-400 max-w-3xl leading-relaxed">
                Clear, transparent, and fair guidelines for using Lumina. We believe terms should protect your freedom to express yourself without hidden traps.
            </p>
            <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-gray-500">
                <span>Effective Date: October 2026</span>
                <span>•</span>
                <span>Version 2.0</span>
                <span>•</span>
                <span class="text-teal-400">Standard Consumer License</span>
            </div>
        </div>

        {{-- Quick Stat Highlights (Dashboard Style) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-12">
            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Your Copyright</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-white mb-1">100% Yours</div>
                <p class="text-xs text-gray-400 leading-relaxed">You own all rights, title, and intellectual property to every word you write in Lumina.</p>
            </div>

            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Medical Disclaimer</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-white mb-1">Wellness Only</div>
                <p class="text-xs text-gray-400 leading-relaxed">Lumina AI offers personal reflection and self-awareness tools, not licensed medical or psychiatric care.</p>
            </div>

            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Cancellation</span>
                    <div class="w-8 h-8 rounded-lg bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-white mb-1">Anytime</div>
                <p class="text-xs text-gray-400 leading-relaxed">Cancel plans instantly through the billing portal with no lock-ins, penalties, or hidden hurdles.</p>
            </div>
        </div>

        {{-- Terms Document Body --}}
        <div class="space-y-8">
            {{-- Section 1 --}}
            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 font-bold text-sm">
                        01
                    </div>
                    <h2 class="text-xl font-bold text-white">Acceptance of Terms</h2>
                </div>
                <div class="text-gray-300 text-sm leading-relaxed space-y-3">
                    <p>
                        By accessing, creating an account, or using Lumina (also referred to as Memo-Mate), you agree to be bound by these Terms of Service and our Privacy Policy. If you do not agree with any provision of these terms, you should not access or use the application.
                    </p>
                    <p>
                        Lumina is available to individuals who are at least 13 years old (or the age of majority in your jurisdiction).
                    </p>
                </div>
            </div>

            {{-- Section 2 --}}
            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400 font-bold text-sm">
                        02
                    </div>
                    <h2 class="text-xl font-bold text-white">Content Ownership & Intellectual Property</h2>
                </div>
                <div class="text-gray-300 text-sm leading-relaxed space-y-3">
                    <p>
                        <strong class="text-white">Your Journal Is Exclusively Yours:</strong> We do not assert any intellectual property rights or ownership interest over your memos, entries, reflections, or personal writing. 
                    </p>
                    <p>
                        You grant Lumina solely the limited technical license strictly necessary to store, render, index, and process your data (including sending prompts to your configured AI services) for the exclusive purpose of operating the application for you.
                    </p>
                </div>
            </div>

            {{-- Section 3 --}}
            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 font-bold text-sm">
                        03
                    </div>
                    <h2 class="text-xl font-bold text-white">AI Self-Reflection & Medical Disclaimer</h2>
                </div>
                <div class="text-gray-300 text-sm leading-relaxed space-y-4">
                    <div class="p-4 rounded-lg bg-amber-500/10 border border-amber-500/20 text-xs text-amber-300 space-y-2">
                        <div class="font-semibold text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Important Health & Mental Wellness Notice</span>
                        </div>
                        <p class="leading-relaxed">
                            Lumina and its AI features (including Guided Reflection, Weekly Summaries, and Chat Insights) are designed solely as personal development and self-reflection tools. Lumina is NOT a healthcare provider, does NOT offer therapy or psychological counsel, and should NEVER replace professional medical advice, diagnosis, or clinical psychiatric care.
                        </p>
                        <p class="leading-relaxed font-semibold">
                            If you are in immediate distress or facing a mental health crisis, please contact emergency services or reach out to local crisis hotlines immediately (e.g., dialing 988 in the US/Canada or 111 in the UK).
                        </p>
                    </div>
                </div>
            </div>

            {{-- Section 4 --}}
            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 font-bold text-sm">
                        04
                    </div>
                    <h2 class="text-xl font-bold text-white">Bring-Your-Own-Key (BYOK) & Third-Party APIs</h2>
                </div>
                <div class="text-gray-300 text-sm leading-relaxed space-y-3">
                    <p>
                        When configuring personal API keys (such as Google Gemini or ElevenLabs):
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-300 pl-2">
                        <li>You are responsible for obtaining and maintaining valid keys in compliance with respective provider terms.</li>
                        <li>Any usage charges, rate limits, or billing quotas incurred on your personal third-party accounts are solely your responsibility.</li>
                        <li>Lumina stores keys in encrypted form (AES-256) and never exposes your raw keys to unauthorized parties.</li>
                    </ul>
                </div>
            </div>

            {{-- Section 5 --}}
            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-400 font-bold text-sm">
                        05
                    </div>
                    <h2 class="text-xl font-bold text-white">Termination & Service Continuity</h2>
                </div>
                <div class="text-gray-300 text-sm leading-relaxed space-y-3">
                    <p>
                        You may terminate your account at any time directly through the Settings panel. Upon account deletion, all journal entries, uploaded assets, and personal keys are permanently deleted.
                    </p>
                    <p>
                        We reserve the right to suspend or terminate accounts that engage in malicious attacks, unauthorized probing of infrastructure, or violations of applicable criminal law.
                    </p>
                </div>
            </div>
        </div>

        {{-- Legal Switcher Nav --}}
        <div class="mt-12 pt-8 border-t border-white/10 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-gray-400">
            <span>Explore other legal documentation:</span>
            <div class="flex items-center gap-4">
                <a href="{{ route('privacy') }}" wire:navigate.hover class="text-blue-400 hover:text-blue-300 underline">Privacy Policy</a>
                <span>•</span>
                <a href="{{ route('security') }}" wire:navigate.hover class="text-blue-400 hover:text-blue-300 underline">Security Architecture</a>
            </div>
        </div>
    </div>
</x-layout>
