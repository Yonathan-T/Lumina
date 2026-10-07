@props([
    'title' => 'Sign In',
    'subtitle' => null,
    'footer' => null,
    'mode' => 'login', // 'login' or 'register'
])

<x-layout :showSidebar="false" :showNav="false" :patternOnBody="false">
    <div class="min-h-screen w-full bg-[#07090e] flex items-center justify-center p-4 sm:p-6 lg:p-8"
         x-data="{ currentMode: '{{ $mode }}' }">
        
        <div class="w-full max-w-6xl rounded-3xl bg-[#0d1117] border border-white/10 shadow-2xl overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[640px]">
            
            {{-- Left Column: Clean Full Aesthetic Artwork Card --}}
            <div class="lg:col-span-6 relative overflow-hidden flex flex-col justify-between p-8 sm:p-10 text-white min-h-[380px] lg:min-h-full bg-black/60">
                
                {{-- Background Image with subtle cinematic vignette (zero colorful blobs or noisy patterns) --}}
                <img src="/images/auth-hero.jpg" alt="Lumina Sanctuary"
                     class="absolute inset-0 w-full h-full object-cover object-center filter brightness-[0.7] contrast-[1.05]" />
                
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-black/60"></div>
                <div class="absolute inset-0 bg-black/25"></div>

                {{-- Brand Header / Home Link --}}
                <div class="relative z-10 flex items-center justify-between">
                    <a href="/" wire:navigate.hover class="flex items-center gap-3 group">
                        <div class="w-9 h-9 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-4 h-4 text-white -rotate-45" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13,2l9,13.6L13,22ZM11,2L2,15.6L11,22Z" />
                            </svg>
                        </div>
                        <span class="font-playfair font-bold text-xl tracking-wide text-white">Lumina</span>
                    </a>

                    <a href="/" wire:navigate.hover class="text-xs text-white/70 hover:text-white transition-colors flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/5 border border-white/10 hover:bg-white/10">
                        <span>← Back to home</span>
                    </a>
                </div>

                {{-- Center Callout & Stepper --}}
                <div class="relative z-10 my-auto py-6">
                    <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-white font-inter"
                        x-text="currentMode === 'register' ? 'Get Started with Us' : 'Welcome Back'">
                        {{ $mode === 'register' ? 'Get Started with Us' : 'Welcome Back' }}
                    </h2>
                    <p class="mt-2.5 text-sm text-white/80 max-w-sm leading-relaxed"
                       x-text="currentMode === 'register' ? 'Complete these easy steps to register your account and begin mindful reflection.' : 'Sign in to access your private memories and daily reflections.'">
                        {{ $mode === 'register' ? 'Complete these easy steps to register your account and begin mindful reflection.' : 'Sign in to access your private memories and daily reflections.' }}
                    </p>

                    {{-- Stepper matching OnlyPipe style --}}
                    <div class="mt-8 space-y-3 max-w-sm">
                        <div class="flex items-center gap-3.5 px-4 py-3 rounded-2xl bg-white text-gray-900 shadow-lg font-medium text-sm transition-all"
                             :class="currentMode === 'register' ? 'bg-white text-gray-900' : 'bg-white text-gray-900'">
                            <span class="w-6 h-6 rounded-full bg-gray-900 text-white flex items-center justify-center text-xs font-bold shrink-0">1</span>
                            <span x-text="currentMode === 'register' ? 'Sign up your account' : 'Sign in to your account'">
                                {{ $mode === 'register' ? 'Sign up your account' : 'Sign in to your account' }}
                            </span>
                        </div>

                        <div class="flex items-center gap-3.5 px-4 py-3 rounded-2xl bg-black/40 backdrop-blur-md border border-white/10 text-white/75 font-medium text-sm">
                            <span class="w-6 h-6 rounded-full bg-white/15 text-white flex items-center justify-center text-xs font-bold shrink-0">2</span>
                            <span>Set up your private workspace</span>
                        </div>

                        <div class="flex items-center gap-3.5 px-4 py-3 rounded-2xl bg-black/40 backdrop-blur-md border border-white/10 text-white/75 font-medium text-sm">
                            <span class="w-6 h-6 rounded-full bg-white/15 text-white flex items-center justify-center text-xs font-bold shrink-0">3</span>
                            <span>Reflect & write with Lumi AI</span>
                        </div>
                    </div>
                </div>

                {{-- Bottom Subtle Clean Space --}}
                <div class="relative z-10 text-xs text-white/50">
                    Your story deserves to be written.
                </div>
            </div>

            {{-- Right Column: Form Container (Instant Toggleable) --}}
            <div class="lg:col-span-6 p-6 sm:p-8 lg:p-10 flex flex-col justify-center">
                <div class="max-w-md w-full mx-auto">
                    {{-- Heading --}}
                    <div class="mb-6">
                        <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight font-inter"
                            x-text="currentMode === 'register' ? 'Sign Up Account' : 'Sign In'">
                            {{ $title }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-400"
                           x-text="currentMode === 'register' ? 'Enter your personal data to create your account.' : 'Enter your personal data to access your sanctuary.'">
                            {{ $subtitle }}
                        </p>
                    </div>

                    {{-- Dynamic Slot (Login & Register forms handled seamlessly) --}}
                    {{ $slot }}

                    {{-- Footer Switcher handled cleanly --}}
                    @isset($footer)
                        <div class="mt-7 text-center text-sm text-gray-400">
                            {!! $footer !!}
                        </div>
                    @endisset
                </div>
            </div>

        </div>
    </div>
</x-layout>