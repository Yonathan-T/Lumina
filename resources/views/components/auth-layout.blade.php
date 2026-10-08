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
            
            {{-- Left Column: High-Res Auth Artwork with Glassy Lumina Medallion & Stepper --}}
            <div id="liquid-auth-hero" class="lg:col-span-6 relative overflow-hidden flex flex-col justify-between px-6 sm:px-8 pt-7 sm:pt-8 pb-5 sm:pb-6 text-white min-h-[440px] lg:min-h-full bg-[#05070c]">
                
                {{-- Curated High-Res Background Image with Soft Blur --}}
                <img src="/images/auth.jpg" alt="Lumina Sanctuary"
                     class="absolute inset-0 w-full h-full object-cover object-center filter brightness-[0.72] contrast-[1.05] blur-[4px] scale-110 pointer-events-none transition-transform duration-1000 ease-out" />

                {{-- Soft Vignette & Contrast Overlay --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-black/30 pointer-events-none z-1"></div>

                {{-- Brand Header (Positioned down and scaled smaller) --}}
                <div class="relative z-10 flex items-center justify-start pt-8 sm:pt-10">
                    <a href="/" wire:navigate.hover class="flex items-center gap-2 group">
                        <svg class="w-5 h-5 text-white -rotate-45 group-hover:scale-105 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M13,2l9,13.6L13,22ZM11,2L2,15.6L11,22Z" />
                        </svg>
                        <span class="font-playfair font-semibold text-base sm:text-lg tracking-wide text-white/95">Lumina</span>
                    </a>
                </div>

                {{-- Callout & Stepper (Shifted left and bottom with extra spacing between steps) --}}
                <div class="relative z-10 mt-auto max-w-sm -ml-0.5 sm:-ml-1.5 pb-1">
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-white font-inter drop-shadow-md text-left"
                        x-text="currentMode === 'register' ? 'Get Started with Us' : 'Welcome Back'">
                        {{ $mode === 'register' ? 'Get Started with Us' : 'Welcome Back' }}
                    </h2>
                    <p class="mt-1.5 text-xs sm:text-sm text-white/90 leading-relaxed font-normal drop-shadow-sm max-w-sm text-left"
                       x-text="currentMode === 'register' ? 'Complete these easy steps to register your account and begin mindful reflection.' : 'Sign in to access your private memories and daily reflections.'">
                        {{ $mode === 'register' ? 'Complete these easy steps to register your account and begin mindful reflection.' : 'Sign in to access your private memories and daily reflections.' }}
                    </p>

                    {{-- Stepper matching OnlyPipe style with more space between 1, 2, 3 --}}
                    <div class="mt-5 space-y-3.5 max-w-sm">
                        <div class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl bg-white text-gray-900 shadow-lg font-semibold text-xs transition-all"
                             :class="currentMode === 'register' ? 'bg-white text-gray-900' : 'bg-white text-gray-900'">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white flex items-center justify-center text-[10px] font-bold shrink-0">1</span>
                            <span x-text="currentMode === 'register' ? 'Sign up your account' : 'Sign in to your account'">
                                {{ $mode === 'register' ? 'Sign up your account' : 'Sign in to your account' }}
                            </span>
                        </div>

                        <div class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl bg-black/45 backdrop-blur-md border border-white/10 text-white/85 font-medium text-xs">
                            <span class="w-5 h-5 rounded-full bg-white/15 text-white flex items-center justify-center text-[10px] font-bold shrink-0">2</span>
                            <span>Set up your private workspace</span>
                        </div>

                        <div class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl bg-black/45 backdrop-blur-md border border-white/10 text-white/85 font-medium text-xs">
                            <span class="w-5 h-5 rounded-full bg-white/15 text-white flex items-center justify-center text-[10px] font-bold shrink-0">3</span>
                            <span>Reflect & write with Lumi AI</span>
                        </div>
                    </div>
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