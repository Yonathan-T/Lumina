<x-layout :isLandingPage="true" :showSidebar="false" :showNav="true" :patternOnBody="false">
    @php
        $currentHour = (int) now()->format('H');
        $timeScene = ($currentHour >= 5 && $currentHour < 17) 
            ? 'alpine-dawn' 
            : (($currentHour >= 17 && $currentHour < 23) ? 'night-coast' : 'edge-orbit');
        $defaultScene = request('scene', $timeScene);
    @endphp

    {{-- Full-Page Dynamic Time-Aware ASCII Art Background (Exact Match to Landing Page) --}}
    <div id="page-time-container" data-scene="{{ $defaultScene }}" class="page-time-container fixed inset-0 pointer-events-none select-none overflow-hidden z-0">
        <ascii-art id="page-time-piece" piece="{{ $defaultScene }}" class="page-time-art absolute inset-0 w-full h-full block">
        </ascii-art>

        {{-- Subtle scanline texture layer --}}
        <div class="absolute inset-0 bg-scanlines opacity-10 pointer-events-none"></div>

        {{-- Atmospheric Dark Tint & Vignette Overlays for Crisp Content Contrast --}}
        <div class="page-time-tint absolute inset-0 bg-[#07090e] pointer-events-none transition-opacity duration-700"></div>
        <div class="page-time-vignette absolute inset-0 bg-gradient-to-b from-[#07090e]/80 via-transparent to-[#07090e]/85 pointer-events-none transition-opacity duration-700"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-[#07090e]/30 via-transparent to-[#07090e]/30 pointer-events-none"></div>
    </div>

    {{-- Main Content Layer with guaranteed z-10 stacking context --}}
    <div class="relative z-10 flex flex-col min-h-screen">
        @livewire('blog-loader')
    </div>

    <style>
        /* Native Smooth Scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Full-Page Time-Aware Atmosphere Backdrop - Crisp, Unblurred ASCII */
        .page-time-container {
            background-color: #07090e;
        }
        /* Gently dimmed daytime ASCII art (alpine-dawn / ocean-sunset) */
        .page-time-art {
            width: 100% !important;
            height: 100% !important;
            opacity: 0.56;
            filter: none !important;
            transform: scale(1.02);
            transform-origin: center;
            transition: opacity 500ms ease;
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

        /* Daytime overlays: gently atmospheric */
        .page-time-tint {
            opacity: 0.32;
        }
        .page-time-vignette {
            opacity: 0.60;
        }

        /* Dimmed specifically for night-coast scene to prevent white moon/sea glare */
        .page-time-art[piece="night-coast"],
        [data-scene="night-coast"] .page-time-art {
            opacity: 0.52 !important;
            filter: none !important;
        }
        .page-time-art[piece="night-coast"] canvas,
        [data-scene="night-coast"] .page-time-art canvas {
            filter: none !important;
        }

        [data-scene="night-coast"] .page-time-tint {
            opacity: 0.35 !important;
        }
        [data-scene="night-coast"] .page-time-vignette {
            opacity: 0.55 !important;
        }

        /* Specific tuning for edge-orbit */
        .page-time-art[piece="edge-orbit"],
        [data-scene="edge-orbit"] .page-time-art {
            opacity: 0.60 !important;
            filter: none !important;
        }
        [data-scene="edge-orbit"] .page-time-tint {
            opacity: 0.25 !important;
        }
        [data-scene="edge-orbit"] .page-time-vignette {
            opacity: 0.55 !important;
        }
    </style>

    <!-- ASCII.rest Web Component Loader & Automatic Time-Aware Sync -->
    <script type="module" src="https://ascii.rest/ascii.js"></script>
    <script>
        (function () {
            function syncHeroTimeScene() {
                const urlParams = new URLSearchParams(window.location.search);
                const hour = new Date().getHours();
                const timeScene = (hour >= 5 && hour < 17) 
                    ? 'alpine-dawn' 
                    : ((hour >= 17 && hour < 23) ? 'night-coast' : 'edge-orbit');
                const piece = urlParams.get('scene') || timeScene;
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
