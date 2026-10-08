<x-layout :patternOnBody="false" :showNav="true" :showSidebar="false">
    {{-- Full-Page Aurora Fjord Scenic ASCII Art Background --}}
    <div class="page-ascii-container fixed inset-0 pointer-events-none select-none overflow-hidden z-0">
        <ascii-art piece="aurora-fjord" class="page-ascii-art absolute inset-0 w-full h-full block">
        </ascii-art>
        <div class="absolute inset-0 bg-scanlines opacity-10 pointer-events-none"></div>
        <div class="absolute inset-0 bg-[#07090e]/65 pointer-events-none"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-[#07090e]/85 via-transparent to-[#07090e]/95 pointer-events-none"></div>
    </div>

    <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
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
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-xs font-medium text-emerald-400 mb-4">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Defense in Depth</span>
            </div>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-white font-inter">
                Security Architecture
            </h1>
            <p class="mt-3 text-base sm:text-lg text-gray-400 max-w-3xl leading-relaxed">
                A personal journal demands the highest bar of engineering trust. Here is a transparent breakdown of the technical safeguards protecting your data at every layer.
            </p>
            <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-gray-500">
                <span>Infrastructure: Containerized FrankenPHP</span>
                <span>•</span>
                <span>Caddy TLS 1.3</span>
                <span>•</span>
                <span class="text-emerald-400">Zero Plaintext Logging</span>
            </div>
        </div>

        {{-- Quick Stat Highlights (Dashboard Style) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-12">
            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Encryption at Rest</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-white mb-1">AES-256-CBC</div>
                <p class="text-xs text-gray-400 leading-relaxed">External API credentials and tokens are encrypted with OpenSSL AES-256 before disk writes.</p>
            </div>

            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Transit Security</span>
                    <div class="w-8 h-8 rounded-lg bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-white mb-1">TLS 1.3 / HSTS</div>
                <p class="text-xs text-gray-400 leading-relaxed">All network connections require TLS 1.3 with automated certificate renewals and strict HTTPS.</p>
            </div>

            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Access Segregation</span>
                    <div class="w-8 h-8 rounded-lg bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-white mb-1">Row-Level Gating</div>
                <p class="text-xs text-gray-400 leading-relaxed">Every database query enforces strict authenticated user scoping; cross-tenant access is impossible.</p>
            </div>
        </div>

        {{-- Security Document Body --}}
        <div class="space-y-8">
            {{-- Section 1 --}}
            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 font-bold text-sm">
                        01
                    </div>
                    <h2 class="text-xl font-bold text-white">Cryptographic Standards & Key Management</h2>
                </div>
                <div class="text-gray-300 text-sm leading-relaxed space-y-4">
                    <p>
                        Lumina utilizes modern authenticated cryptography to ensure secrets cannot be recovered by unauthorized actors:
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 rounded-lg bg-white/5 border border-white/10 space-y-2">
                            <span class="text-white font-semibold text-xs uppercase tracking-wider block">API Key Protection (BYOK)</span>
                            <p class="text-xs text-gray-400 leading-relaxed">
                                Gemini and ElevenLabs keys are encrypted using OpenSSL AES-256-CBC with randomized initialization vectors and authenticated MACs via Laravel's Crypt system. Plaintext keys only exist in volatile memory during outgoing requests.
                            </p>
                        </div>
                        <div class="p-4 rounded-lg bg-white/5 border border-white/10 space-y-2">
                            <span class="text-white font-semibold text-xs uppercase tracking-wider block">Password Hashing</span>
                            <p class="text-xs text-gray-400 leading-relaxed">
                                User passwords are salted and hashed using Argon2id or Bcrypt with elevated work factors. We never store, log, or have access to raw user passwords.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 2 --}}
            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400 font-bold text-sm">
                        02
                    </div>
                    <h2 class="text-xl font-bold text-white">Application Defense & OWASP Hardening</h2>
                </div>
                <div class="text-gray-300 text-sm leading-relaxed space-y-3">
                    <ul class="list-disc list-inside space-y-2 text-gray-300 pl-2">
                        <li><strong class="text-white">CSRF Protection:</strong> Cross-Site Request Forgery tokens are validated on all mutating HTTP requests and Livewire component dispatches.</li>
                        <li><strong class="text-white">SQL Injection Prevention:</strong> All database queries utilize Eloquent ORM and PDO parameterized statements with bound parameters. Raw SQL concatenation is strictly prohibited.</li>
                        <li><strong class="text-white">Secure Cookie Flags:</strong> Authentication cookies are minted with <code class="text-teal-400 bg-white/5 px-1 py-0.5 rounded text-xs">HttpOnly</code> (blocking JavaScript XSS inspection), <code class="text-teal-400 bg-white/5 px-1 py-0.5 rounded text-xs">SameSite=Lax</code>, and <code class="text-teal-400 bg-white/5 px-1 py-0.5 rounded text-xs">Secure</code> flags.</li>
                        <li><strong class="text-white">Input Sanitization:</strong> Journal HTML content and rich text entries are sanitized to neutralize malicious script injection.</li>
                    </ul>
                </div>
            </div>

            {{-- Section 3 --}}
            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 font-bold text-sm">
                        03
                    </div>
                    <h2 class="text-xl font-bold text-white">Zero Plaintext Logging Policy</h2>
                </div>
                <div class="text-gray-300 text-sm leading-relaxed space-y-3">
                    <p>
                        Lumina enforces strict logging sanitization:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-300 pl-2">
                        <li>API keys, journal content, and authentication passwords are automatically redacted from error traces and production loggers.</li>
                        <li>Temporary ephemeral chat sessions are processed in memory and never written to cold log storage.</li>
                        <li>Outgoing API requests to Google Gemini and ElevenLabs are hardened with strict timeouts to prevent hung requests from leaving unmanaged traces.</li>
                    </ul>
                </div>
            </div>

            {{-- Section 4 --}}
            <div class="card-highlight rounded-xl border border-white/10 bg-gradient-dark p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 font-bold text-sm">
                        04
                    </div>
                    <h2 class="text-xl font-bold text-white">Responsible Disclosure Policy</h2>
                </div>
                <div class="text-gray-300 text-sm leading-relaxed space-y-3">
                    <p>
                        We welcome responsible security research. If you believe you have discovered a potential vulnerability in Lumina, please report it immediately:
                    </p>
                    <div class="p-4 rounded-lg bg-white/5 border border-white/10 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-white font-medium">Security Inquiries:</span>
                            <span class="text-gray-400 ml-1">security@lumina-journal.com</span>
                        </div>
                        <a href="https://github.com/Yonathan-T/Lumina/security/advisories" target="_blank"
                            class="inline-flex items-center gap-1.5 text-emerald-400 hover:text-emerald-300 underline font-medium">
                            <span>GitHub Security Advisory</span>
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
                <a href="{{ route('privacy') }}" wire:navigate.hover class="text-emerald-400 hover:text-emerald-300 underline">Privacy Policy</a>
                <span>•</span>
                <a href="{{ route('terms') }}" wire:navigate.hover class="text-emerald-400 hover:text-emerald-300 underline">Terms of Service</a>
            </div>
        </div>
    </div>

    <style>
        .page-ascii-container {
            background-color: #06090e;
        }
        .page-ascii-art {
            width: 100% !important;
            height: 100% !important;
            opacity: 0.55;
            filter: blur(1.8px);
            transform: scale(1.03);
            transform-origin: center;
        }
        .page-ascii-art canvas {
            width: 100% !important;
            height: 100% !important;
            aspect-ratio: auto !important;
            object-fit: cover !important;
            object-position: center center !important;
            display: block !important;
            filter: blur(1.8px);
        }
    </style>
    <script type="module" src="https://ascii.rest/ascii.js"></script>
</x-layout>
