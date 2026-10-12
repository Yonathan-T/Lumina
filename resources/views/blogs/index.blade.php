<x-layout :isLandingPage="true" :showSidebar="false" :showNav="true" :patternOnBody="false">
    @php
        $currentHour = (int) now()->format('H');
        $defaultScene = ($currentHour >= 5 && $currentHour < 12) ? 'alpine-dawn' : (($currentHour >= 12 && $currentHour < 19) ? 'ocean-sunset' : 'night-coast');
    @endphp

    {{-- Full-Page Dynamic Time-Aware ASCII Art Background (Exact Match to Landing Page) --}}
    <div id="page-time-container" data-scene="{{ $defaultScene }}" class="page-time-container fixed inset-0 pointer-events-none select-none overflow-hidden z-0">
        <ascii-art id="page-time-piece" piece="{{ $defaultScene }}" class="page-time-art absolute inset-0 w-full h-full block">
        </ascii-art>

        {{-- Subtle scanline texture layer --}}
        <div class="absolute inset-0 bg-scanlines opacity-10 pointer-events-none"></div>

        {{-- Atmospheric Dark Tint & Vignette Overlays for Crisp Content Contrast --}}
        <div class="page-time-tint absolute inset-0 bg-[#07090e]/50 pointer-events-none transition-opacity duration-700"></div>
        <div class="page-time-vignette absolute inset-0 bg-gradient-to-b from-[#0a0c10]/80 via-transparent to-[#0a0c10]/85 pointer-events-none transition-opacity duration-700"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-[#0a0c10]/40 via-transparent to-[#0a0c10]/40 pointer-events-none"></div>
    </div>

    {{-- Main Content Layer with guaranteed z-10 stacking context --}}
    <div class="relative z-10 flex flex-col min-h-screen">
        @livewire('blog-loader')
    </div>

    <style>
        /* Full-Page Time-Aware Atmosphere Backdrop */
        .page-time-container {
            background-color: #07090e;
        }
        .page-time-art {
            width: 100% !important;
            height: 100% !important;
            opacity: 0.65;
            filter: blur(1.5px);
            transform: scale(1.03);
            transform-origin: center;
            transition: filter 500ms ease, opacity 500ms ease;
        }
        .page-time-art canvas {
            width: 100% !important;
            height: 100% !important;
            aspect-ratio: auto !important;
            object-fit: cover !important;
            object-position: center top !important;
            display: block !important;
            filter: none !important;
        }

        /* Specifically watered-down blur & luminous contrast for night-coast */
        .page-time-art[piece="night-coast"],
        [data-scene="night-coast"] .page-time-art {
            opacity: 0.85 !important;
            filter: blur(1.8px) brightness(1.15) contrast(1.08) !important;
        }
        .page-time-art[piece="night-coast"] canvas,
        [data-scene="night-coast"] .page-time-art canvas {
            filter: none !important;
        }

        /* Water down dark overlays specifically for night-coast so it is not pitch black */
        [data-scene="night-coast"] .page-time-tint {
            opacity: 0.28 !important;
        }
        [data-scene="night-coast"] .page-time-vignette {
            opacity: 0.60 !important;
        }
    </style>

    <!-- ASCII.rest Web Component Loader & Automatic Time-Aware Sync -->
    <script type="module" src="https://ascii.rest/ascii.js"></script>
    <script>
        (function () {
            function syncHeroTimeScene() {
                const hour = new Date().getHours();
                const piece = (hour >= 5 && hour < 12) ? 'alpine-dawn' : ((hour >= 12 && hour < 19) ? 'ocean-sunset' : 'night-coast');
                const container = document.getElementById('page-time-container');
                if (container) {
                    container.setAttribute('data-scene', piece);
                }
                const el = document.getElementById('page-time-piece');
                if (el && el.getAttribute('piece') !== piece) {
                    el.setAttribute('piece', piece);
                }
            }

            syncHeroTimeScene();
            document.addEventListener('livewire:navigated', syncHeroTimeScene);
        })();
    </script>
</x-layout>
