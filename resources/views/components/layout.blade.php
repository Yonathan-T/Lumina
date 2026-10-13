@props(['showSidebar' => false, 'showNav' => true, 'isLandingPage' => false, 'patternOnBody' => false, 'isChatPage' => false])
<!DOCTYPE html>
<html lang="en" class="{{ $isChatPage ? 'is-chat-page h-full h-[100dvh] overflow-hidden overscroll-none' : '' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, interactive-widget=resizes-content">
    <title>Lumina</title>
    <link rel="icon" type="image/svg+xml" href="/lumiicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@400;500;600;700&family=Lora:wght@400;500;600&family=Caveat:wght@400;700&family=Dancing+Script:wght@400;700&family=Crimson+Text:wght@400;600&family=Merriweather:wght@400;700&family=JetBrains+Mono:wght@400;600&family=Ubuntu:wght@400;500;700&display=swap"
        media="print"
        onload="this.media='all'"
        onerror="this.remove()">
    @livewireStyles
    <script>
        // Apply collapsed class ASAP to avoid sidebar flash on refresh
        (function () {
            try {
                if (localStorage.getItem('sidebar-collapsed') === '1') {
                    document.documentElement.classList.add('sc-init');
                }
                if (localStorage.getItem('chat-nav-collapsed') === '1' && window.innerWidth >= 768) {
                    document.documentElement.classList.add('chat-nav-collapsed');
                }
                const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                if (tz && !document.cookie.includes('user_timezone=' + encodeURIComponent(tz))) {
                    document.cookie = 'user_timezone=' + encodeURIComponent(tz) + '; path=/; max-age=31536000; SameSite=Lax';
                }
            } catch (e) { }
        })();

        // Persistent Sanctuary Ambient Audio Manager (Survives all Livewire SPA navigations)
        window.SanctuaryAudio = window.SanctuaryAudio || (function () {
            let audio = null;
            let preloader = null;
            let isPlaying = false;
            let isLooping = false;
            let audioCtx = null;
            let synthNodes = [];

            const playlist = [
                { title: '01-Ladyfingers-Lofi', url: '/audio/01-Ladyfingers-Lofi.m4a' },
                { title: '02-Golden-Brown', url: '/audio/02-Golden-Brown.m4a' },
                { title: '03-where-am-i-supposed-to-go', url: '/audio/03-where-am-i-supposed-to-go.m4a' },
                { title: '04-ridgeclub - biting bullets', url: '/audio/04-ridgeclub%20-%20biting%20bullets.m4a' },
                { title: '05-falling-forwards', url: '/audio/05-falling-forwards.m4a' },
                { title: '06-Solace', url: '/audio/06-Solace.m4a' },
                { title: '07-Idea-22', url: '/audio/07-Idea-22.m4a' },
                { title: '08-Idea-7', url: '/audio/08-Idea-7.m4a' },
                { title: '09-Idea-10', url: '/audio/09-Idea-10.m4a' }
            ];
            let currentTrackIdx = 0;

            function getCurrentTrack() {
                return playlist[currentTrackIdx] || playlist[0];
            }

            function preloadAdjacentTracks() {
                try {
                    const nextIdx = (currentTrackIdx + 1) % playlist.length;
                    const nextUrl = playlist[nextIdx].url;
                    if (!preloader) {
                        preloader = new Audio();
                        preloader.preload = 'auto';
                        preloader.volume = 0;
                    }
                    if (preloader.src !== window.location.origin + nextUrl && preloader.src !== nextUrl) {
                        preloader.src = nextUrl;
                        preloader.load();
                    }
                } catch (e) { }
            }

            function getAudioInstance() {
                if (!audio) {
                    audio = new Audio();
                    audio.loop = isLooping;
                    audio.preload = 'auto';
                    audio.volume = 0.7;
                    audio.src = getCurrentTrack().url;

                    audio.addEventListener('play', function () {
                        isPlaying = true;
                        notifyState();
                        preloadAdjacentTracks();
                    });
                    audio.addEventListener('pause', function () {
                        isPlaying = false;
                        notifyState();
                    });
                    audio.addEventListener('ended', function () {
                        if (isLooping) {
                            audio.currentTime = 0;
                            audio.play().catch(function() {});
                        } else {
                            next();
                        }
                    });
                    audio.addEventListener('error', function (err) {
                        console.warn('Sanctuary audio source error, switching to next track', err);
                        next();
                    });
                }
                return audio;
            }

            function loadTrack(idx, autoPlay = true) {
                currentTrackIdx = (idx + playlist.length) % playlist.length;
                const track = getCurrentTrack();
                const a = getAudioInstance();
                
                // Immediately notify state for instant UI update
                notifyState();

                a.src = track.url;
                a.currentTime = 0;
                a.loop = isLooping;

                if (autoPlay || isPlaying) {
                    play();
                }
                
                preloadAdjacentTracks();
            }

            function next() {
                loadTrack(currentTrackIdx + 1, isPlaying);
            }

            function prev() {
                loadTrack(currentTrackIdx - 1, isPlaying);
            }

            function toggleLoop() {
                isLooping = !isLooping;
                if (audio) {
                    audio.loop = isLooping;
                }
                notifyState();
                return isLooping;
            }

            function getIsLooping() {
                return Boolean(isLooping);
            }

            function notifyState() {
                try {
                    sessionStorage.setItem('sanctuary_audio_active', isPlaying ? '1' : '0');
                } catch (e) { }
                window.dispatchEvent(new CustomEvent('sanctuary-audio-state', {
                    detail: { 
                        isPlaying: isPlaying,
                        isLooping: isLooping,
                        track: getCurrentTrack()
                    }
                }));
            }

            function play() {
                isPlaying = true;
                stopSynth();
                const a = getAudioInstance();
                const playPromise = a.play();
                if (playPromise !== undefined) {
                    playPromise.then(function () {
                        isPlaying = true;
                        notifyState();
                    }).catch(function (err) {
                        console.warn('Audio play rejection, trying fallback', err);
                        startSynth();
                    });
                }
            }

            function pause() {
                isPlaying = false;
                if (audio) {
                    audio.pause();
                }
                stopSynth();
                notifyState();
            }

            function toggle() {
                if (getIsPlaying()) {
                    pause();
                } else {
                    play();
                }
            }

            function getIsPlaying() {
                return Boolean(isPlaying || (audio && !audio.paused && audio.currentTime > 0) || (synthNodes && synthNodes.length > 0));
            }

            function startSynth() {
                try {
                    if (!audioCtx) {
                        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    }
                    if (audioCtx.state === 'suspended') {
                        audioCtx.resume();
                    }
                    stopSynth();
                    synthNodes = [];
                    const freqs = [146.83, 220.00, 369.99];
                    freqs.forEach(function (f) {
                        const osc = audioCtx.createOscillator();
                        const gain = audioCtx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(f, audioCtx.currentTime);
                        gain.gain.setValueAtTime(0.02, audioCtx.currentTime);
                        osc.connect(gain);
                        gain.connect(audioCtx.destination);
                        osc.start();
                        synthNodes.push({ osc: osc, gain: gain });
                    });
                    isPlaying = true;
                    notifyState();
                } catch (e) {
                    console.log('WebAudio synth fallback error', e);
                }
            }

            function stopSynth() {
                if (synthNodes && synthNodes.length > 0) {
                    synthNodes.forEach(function (n) {
                        try {
                            n.osc.stop();
                            n.osc.disconnect();
                        } catch (e) { }
                    });
                    synthNodes = [];
                }
            }

            // Silently warm up first track and preloader in background for instant 0ms playback
            try {
                if (typeof window !== 'undefined') {
                    const warmUp = function () {
                        getAudioInstance();
                        preloadAdjacentTracks();
                    };
                    if ('requestIdleCallback' in window) {
                        requestIdleCallback(warmUp);
                    } else {
                        setTimeout(warmUp, 600);
                    }
                }
            } catch (e) { }

            return {
                play: play,
                pause: pause,
                toggle: toggle,
                next: next,
                prev: prev,
                toggleLoop: toggleLoop,
                getIsLooping: getIsLooping,
                getCurrentTrack: getCurrentTrack,
                getIsPlaying: getIsPlaying,
                getInstance: getAudioInstance
            };
        })();
    </script>

    @if(config('services.google_analytics.id'))
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google_analytics.id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ config('services.google_analytics.id') }}');
        </script>
    @endif
</head>

<body class="brand-page {{ (!$isLandingPage && ($patternOnBody || !$showSidebar)) ? 'bg-diagonal-lines' : '' }} text-[#c3beb6] min-h-screen flex flex-col {{ $showSidebar ? 'has-sidebar' : '' }} {{ $isLandingPage ? 'scrollbar-none' : '' }} {{ $isChatPage ? 'is-chat-page h-full h-[100dvh] max-h-[100dvh] overflow-hidden overscroll-none fixed inset-0 w-full' : '' }}">


    @if ($showNav)

        <x-navs x-data="{ mobileNavOpen: false }">
            <a href="/" wire:navigate.hover class="ml-1 sm:ml-3 flex items-center gap-1.5 sm:gap-2 group relative shrink-0">
                <svg class="w-7 h-7 sm:w-10 sm:h-10 text-white
                                                       transition-transform duration-700 ease-out
                                                       group-hover:rotate-[720deg]
                                                       -rotate-45" fill="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M13,2l9,13.6L13,22ZM11,2L2,15.6L11,22Z" />
                </svg>

                <p class="font-playfair font-bold text-base sm:text-xl text-white tracking-wide
                                                     transition-all duration-500
                                                     group-hover:text-white/40
                                                     group-hover:translate-x-1">
                    LUMINA
                </p>
            </a>
            <div class="hidden md:flex space-x-6 font-bold">
                <x-links href="{{ $isLandingPage ? '#features' : '/#features' }}" section="features">Features</x-links>
                <x-links href="{{ $isLandingPage ? '#faq' : '/#faq' }}" section="faq">FAQs</x-links>
                <x-links :href="route('blogs.index')" :active="request()->routeIs('blogs.*')" wire:navigate.hover>Blogs</x-links>
            </div>
            <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                @auth
                    <a href="/dashboard"
                        wire:navigate.hover
                        class="
                            border border-white/25 rounded-lg px-2.5 py-1.5 sm:px-3 sm:py-2
                            bg-[#060b16] text-white font-semibold text-xs sm:text-sm
                            shadow-[1px_1px_rgba(255,255,255,0.15),2px_2px_rgba(255,255,255,0.1),3px_3px_rgba(255,255,255,0.07),4px_4px_rgba(255,255,255,0.05)]
                            active:translate-y-[2px]
                            active:shadow-[inset_2px_2px_5px_rgba(0,0,0,0.3)]
                            active:border-gray-600
                            transition-all duration-200 ease-in-out
                            select-none
                            inline-block
                        ">
                        Dashboard
                    </a>
                @endauth
                @guest
                    <a href="/auth/login"
                        wire:navigate.hover
                        class="text-xs sm:text-sm font-semibold text-white/80 hover:text-white mr-1.5 sm:mr-3 transition-colors">
                        Sign in
                    </a>
                    <a href="/auth/register"
                        wire:navigate.hover
                        class="
                            border border-white/25 rounded-lg px-2.5 py-1.5 sm:px-3 sm:py-2
                            bg-[#060b16] text-white font-semibold text-xs sm:text-sm
                            shadow-[1px_1px_rgba(255,255,255,0.15),2px_2px_rgba(255,255,255,0.1),3px_3px_rgba(255,255,255,0.07),4px_4px_rgba(255,255,255,0.05)]
                            active:translate-y-[2px]
                            active:shadow-[inset_2px_2px_5px_rgba(0,0,0,0.3)]
                            active:border-gray-600
                            transition-all duration-200 ease-in-out
                            select-none
                            inline-block
                        ">
                        Sign up
                    </a>

                @endguest

                {{-- Mobile Hamburger (Only visible on screens < md) --}}
                <button 
                    type="button"
                    @click="mobileNavOpen = !mobileNavOpen"
                    class="md:hidden p-1.5 sm:p-2 text-white/80 hover:text-white focus:outline-none rounded-lg border border-white/25 bg-[#060b16] active:translate-y-[2px] transition-all cursor-pointer shrink-0"
                    aria-label="Toggle navigation menu"
                >
                    <svg class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-90': mobileNavOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileNavOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobileNavOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Mobile Dropdown Menu Popover --}}
            <div 
                x-show="mobileNavOpen"
                @click.outside="mobileNavOpen = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-2 scale-98"
                class="md:hidden absolute top-full left-0 right-0 mt-3 z-50"
                style="display: none;"
            >
                <div class="rounded-3xl border border-white/15 bg-[#060b16]/95 backdrop-blur-2xl p-4 shadow-2xl space-y-1.5 divide-y divide-white/5">
                    <a 
                        href="{{ $isLandingPage ? '#features' : '/#features' }}" 
                        @click="mobileNavOpen = false"
                        class="block px-4 py-2.5 rounded-xl text-base font-semibold text-gray-200 hover:text-white hover:bg-white/10 transition-colors"
                    >
                        Features
                    </a>
                    <a 
                        href="{{ $isLandingPage ? '#faq' : '/#faq' }}" 
                        @click="mobileNavOpen = false"
                        class="block px-4 py-2.5 rounded-xl text-base font-semibold text-gray-200 hover:text-white hover:bg-white/10 transition-colors"
                    >
                        FAQs
                    </a>
                    <a 
                        href="{{ route('blogs.index') }}" 
                        wire:navigate.hover
                        @click="mobileNavOpen = false"
                        class="block px-4 py-2.5 rounded-xl text-base font-semibold text-gray-200 hover:text-white hover:bg-white/10 transition-colors"
                    >
                        Blogs
                    </a>
                </div>
            </div>
        </x-navs>
    @endif

    @if($showSidebar)
        <div class="flex {{ $isChatPage ? 'h-screen h-[100dvh] overflow-hidden' : 'min-h-screen' }}">
            <aside id="appSidebarContainer"
                class="w-64 transition-all duration-300 md:translate-x-0 md:fixed fixed inset-y-0 left-0 z-40">
                @include('components.cached-sidebar')
            </aside>
            <div id="sidebarBackdrop" class="md:hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-30 hidden"></div>
            <main
                class="flex-1 font-inter text-custom relative {{ $isChatPage ? 'h-screen h-[100dvh] overflow-hidden overscroll-none pt-0' : 'min-h-screen overflow-y-auto scrollbar-none pt-12 md:pt-0' }} {{ (!$isLandingPage && !$patternOnBody && !$isChatPage) ? 'bg-diagonal-lines' : '' }}">
                @if(!$isChatPage)
                    <button id="mobileSidebarToggle"
                        class="md:hidden fixed top-4 left-4 z-50 inline-flex items-center justify-center w-10 h-10 rounded-md border border-white/25 bg-[#0b1220]/80 backdrop-blur-sm text-white/90 hover:text-white hover:bg-[#0b1220]/95 transition">
                        <!-- simple hamburger -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                            <path fill-rule="evenodd"
                                d="M3.75 6.75A.75.75 0 0 1 4.5 6h15a.75.75 0 0 1 0 1.5h-15a.75.75 0 0 1-.75-.75Zm0 5.25a.75.75 0 0 1 .75-.75h15a.75.75 0 0 1 0 1.5h-15a.75.75 0 0 1-.75-.75Zm.75 4.5a.75.75 0 0 0 0 1.5h15a.75.75 0 0 0 0-1.5h-15Z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                @endif
                {{ $slot }}
            </main>
        </div>
    @else
        <main class="flex-1 font-inter text-custom scrollbar-none {{ (!$isLandingPage && !$patternOnBody && $showSidebar) ? 'bg-diagonal-lines' : '' }}">
            {{ $slot }}
        </main>
    @endif
    @livewireScripts
    <script>
        // Global listener to update all font preview areas instantly
        window.addEventListener('font-changed', (e) => {
            const font = e.detail.font;
            // Toggle class on elements marked for font-binding
            document.querySelectorAll('[data-font-bind]')
                .forEach(el => {
                    el.classList.remove('font-inter', 'font-poppins', 'font-ubuntu', 'font-playfair', 'font-lora', 'font-crimson', 'font-merriweather', 'font-caveat', 'font-dancing', 'font-jetbrains');
                    el.classList.add('font-' + font);
                });
        });

        // Global listener for font size changes
        window.addEventListener('font-size-changed', (e) => {
            const size = e.detail.size;
            // Apply font size to all elements marked for font-size binding
            document.querySelectorAll('[data-font-size-bind]')
                .forEach(el => {
                    el.style.fontSize = size + 'px';
                });
        });
    </script>



</body>



</html>
<script>
    (function () {
        function updateSidebarTitles() {
            const isCollapsed = document.body.classList.contains('sidebar-collapsed');
            const collapseBtn = document.getElementById('sidebarCollapseToggle');

            document.querySelectorAll('#sidebar [data-title]').forEach(function (el) {
                el.title = isCollapsed ? el.getAttribute('data-title') : '';
            });

            if (!collapseBtn) {
                return;
            }

            collapseBtn.title = isCollapsed ? (collapseBtn.getAttribute('data-title') || 'Toggle sidebar') : '';
            collapseBtn.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');

            const iconCollapse = collapseBtn.querySelector('.icon-collapse');
            const iconExpand = collapseBtn.querySelector('.icon-expand');

            if (iconCollapse && iconExpand) {
                if (isCollapsed) {
                    iconCollapse.classList.add('hidden');
                    iconExpand.classList.remove('hidden');
                } else {
                    iconCollapse.classList.remove('hidden');
                    iconExpand.classList.add('hidden');
                }
            }
        }

        function highlightNav() {
            const sections = document.querySelectorAll('section[id]');
            const navLinks = document.querySelectorAll('a[data-section]');

            if (!sections.length || !navLinks.length) {
                return;
            }

            let current = '';

            sections.forEach(section => {
                if (window.scrollY >= (section.offsetTop - 100)) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                const underline = link.querySelector('span');

                link.classList.remove('text-white');
                link.classList.add('text-gray-400');

                if (link.getAttribute('data-section') === current) {
                    link.classList.remove('text-gray-400');
                    link.classList.add('text-white');
                    underline?.classList.add('scale-x-100');
                    underline?.classList.remove('scale-x-0');
                } else {
                    underline?.classList.remove('scale-x-100');
                    underline?.classList.add('scale-x-0');
                }
            });
        }

        function initLayoutUi() {
            try {
                const collapsed = localStorage.getItem('sidebar-collapsed') === '1';
                document.body.classList.toggle('sidebar-collapsed', collapsed);
                document.documentElement.classList.remove('sc-init');
            } catch (e) { }

            const isChat = !!document.getElementById('chatRoot');
            document.body.classList.toggle('is-chat-page', isChat);
            document.documentElement.classList.toggle('is-chat-page', isChat);
            if (!isChat) {
                document.body.classList.remove('chat-open');
            }

            document.body.classList.remove('sidebar-open');
            updateSidebarTitles();
            highlightNav();
        }

        if (!window.__luminaLayoutHandlersBound) {
            window.__luminaLayoutHandlersBound = true;

            document.addEventListener('click', function (event) {
                const collapseBtn = event.target.closest('#sidebarCollapseToggle');
                if (collapseBtn) {
                    document.body.classList.toggle('sidebar-collapsed');
                    try {
                        const isCollapsed = document.body.classList.contains('sidebar-collapsed');
                        localStorage.setItem('sidebar-collapsed', isCollapsed ? '1' : '0');
                    } catch (e) { }
                    updateSidebarTitles();
                    return;
                }

                const mobileToggle = event.target.closest('#mobileSidebarToggle');
                if (mobileToggle) {
                    document.body.classList.toggle('sidebar-open');
                    const expanded = document.body.classList.contains('sidebar-open');
                    mobileToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                    document.getElementById('sidebarBackdrop')?.classList.toggle('hidden', !expanded);
                    return;
                }

                const mobileBackdrop = event.target.closest('#sidebarBackdrop');
                if (mobileBackdrop) {
                    document.body.classList.remove('sidebar-open');
                    document.getElementById('mobileSidebarToggle')?.setAttribute('aria-expanded', 'false');
                    mobileBackdrop.classList.add('hidden');
                    return;
                }

                // If on mobile and a link or button inside sidebar is clicked, close sidebar drawer
                if (window.innerWidth <= 768 && document.body.classList.contains('sidebar-open')) {
                    const sidebarLink = event.target.closest('#sidebar a, #sidebar button:not(#profileButton)');
                    if (sidebarLink) {
                        document.body.classList.remove('sidebar-open');
                        document.getElementById('mobileSidebarToggle')?.setAttribute('aria-expanded', 'false');
                        document.getElementById('sidebarBackdrop')?.classList.add('hidden');
                    }
                }
            });

            document.addEventListener('keydown', function (event) {
                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'b') {
                    const activeEl = document.activeElement;
                    const isInput = activeEl && (
                        activeEl.tagName === 'INPUT' ||
                        activeEl.tagName === 'TEXTAREA' ||
                        activeEl.isContentEditable
                    );

                    // If user is inside an input/textarea/editor, allow standard behavior
                    if (!isInput) {
                        event.preventDefault();
                        const toggleBtn = document.getElementById('sidebarCollapseToggle');
                        if (toggleBtn) {
                            toggleBtn.click();
                        } else if (document.body.classList.contains('has-sidebar')) {
                            document.body.classList.toggle('sidebar-collapsed');
                            try {
                                const isCollapsed = document.body.classList.contains('sidebar-collapsed');
                                localStorage.setItem('sidebar-collapsed', isCollapsed ? '1' : '0');
                            } catch (e) { }
                            updateSidebarTitles();
                        }
                    }
                }
            });

            window.addEventListener('popstate', function () {
                document.body.classList.remove('sidebar-open');
            });

            window.addEventListener('scroll', highlightNav, { passive: true });
            document.addEventListener('livewire:navigated', initLayoutUi);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initLayoutUi, { once: true });
        } else {
            initLayoutUi();
        }
    })();
</script>