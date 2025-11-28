@props(['showSidebar' => false, 'showNav' => true, 'isLandingPage' => false])
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lumina</title>
    <link rel="icon" type="image/svg+xml" href="/lumiicon.svg">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@400;500;600;700&family=Lora:wght@400;500;600&family=Caveat:wght@400;700&family=Dancing+Script:wght@400;700&family=Crimson+Text:wght@400;600&family=Merriweather:wght@400;700&family=JetBrains+Mono:wght@400;600&family=Ubuntu:wght@400;500;700&display=swap"
        rel="stylesheet">
    @livewireStyles
    <script>
        // Apply collapsed class ASAP to avoid sidebar flash on refresh
        (function () {
            try {
                if (localStorage.getItem('sidebar-collapsed') === '1') {
                    document.documentElement.classList.add('sc-init');
                }
            } catch (e) { }
        })();
    </script>
</head>

<body
    style="background: linear-gradient(to bottom right, hsl(224, 71%, 4%), hsl(224, 65%, 5%)); background-image: linear-gradient(to bottom right, hsl(224, 71%, 4%), hsl(224, 65%, 5%)), radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px); background-size: auto, 20px 20px;"
    class="bg-dot-pattern text-[#c3beb6] min-h-screen flex flex-col {{ $showSidebar ? 'has-sidebar' : '' }}">


    @if ($showNav)

        <x-navs>
            <a href="/" class="ml-3 flex items-center gap-2 group relative">
                <svg class="w-10 h-10 text-white 
                                   transition-transform duration-700 ease-out 
                                   group-hover:rotate-[720deg] 
                                   -rotate-45" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13,2l9,13.6L13,22ZM11,2L2,15.6L11,22Z" />
                </svg>

                <p class="font-playfair font-bold text-xl text-white 
                                 transition-all duration-500 
                                 group-hover:text-white/40 
                                 group-hover:translate-x-1">
                    LUMINA
                </p>
            </a>
            <div class="space-x-6 font-bold">
                <x-links href="#features" section="features">Features</x-links>
                <x-links href="#pricing" section="pricing">Pricing</x-links>
                <x-links :href="route('blogs.index')" :active="request()->routeIs('blogs.*')">Blogs</x-links>
                <x-links href="#contact" section="contact">Contact</x-links>
            </div>
            <div>
                @auth
                    <a href="/dashboard"
                        class="
                                                                                                                                                                                                border border-white/25 rounded-lg px-3 py-2
                                                                                                                                                                                                bg-[#060b16] text-white font-semibold
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

                    <a href="/auth/register"
                        class="
                                                                                                                                                                                                border border-white/25 rounded-lg px-3 py-2
                                                                                                                                                                                                bg-[#060b16] text-white font-semibold
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
            </div>







        </x-navs>
    @endif

    @if($showSidebar)
        <div class="flex min-h-screen">
            <aside id="appSidebarContainer"
                class="w-64 transition-all duration-300 md:translate-x-0 md:fixed fixed inset-y-0 left-0 z-40">
                @include('components.cached-sidebar')
            </aside>
            <div id="sidebarBackdrop" class="md:hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-30 hidden"></div>
            <main
                class="{{ $isLandingPage ? '' : 'bg-diagonal-lines' }} flex-1 font-inter text-custom relative min-h-screen overflow-y-auto pt-12 md:pt-0">
                <button id="mobileSidebarToggle"
                    class="md:hidden fixed top-4 left-4 z-50 inline-flex items-center justify-center w-10 h-10 rounded-md border border-white/25 bg-[#0b1220]/80 backdrop-blur-sm text-white/90 hover:text-white hover:bg-[#0b1220]/95 transition">
                    <!-- simple hamburger -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                        <path fill-rule="evenodd"
                            d="M3.75 6.75A.75.75 0 0 1 4.5 6h15a.75.75 0 0 1 0 1.5h-15a.75.75 0 0 1-.75-.75Zm0 5.25a.75.75 0 0 1 .75-.75h15a.75.75 0 0 1 0 1.5h-15a.75.75 0 0 1-.75-.75Zm.75 4.5a.75.75 0 0 0 0 1.5h15a.75.75 0 0 0 0-1.5h-15Z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
                {{ $slot }}
            </main>
        </div>
    @else
        <main class="{{ $isLandingPage ? '' : 'bg-diagonal-lines' }} font-inter text-custom">
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
    document.addEventListener('DOMContentLoaded', function () {
        // Restore sidebar collapsed state
        try {
            const collapsed = localStorage.getItem('sidebar-collapsed') === '1';
            if (collapsed) {
                document.body.classList.add('sidebar-collapsed');
            }
            // Remove initial HTML flag now that body is set
            document.documentElement.classList.remove('sc-init');
        } catch (e) { }

        const collapseBtn = document.getElementById('sidebarCollapseToggle');
        if (collapseBtn) {
            collapseBtn.addEventListener('click', function () {
                document.body.classList.toggle('sidebar-collapsed');
                try {
                    const isCollapsed = document.body.classList.contains('sidebar-collapsed');
                    localStorage.setItem('sidebar-collapsed', isCollapsed ? '1' : '0');
                } catch (e) { }
                updateSidebarTitles();
            });
        }

        // Mobile toggle
        const mobileToggle = document.getElementById('mobileSidebarToggle');
        const mobileBackdrop = document.getElementById('sidebarBackdrop');
        if (mobileToggle) {
            mobileToggle.addEventListener('click', function () {
                document.body.classList.toggle('sidebar-open');
                const expanded = document.body.classList.contains('sidebar-open');
                mobileToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                if (mobileBackdrop) mobileBackdrop.classList.toggle('hidden', !expanded);
            });
        }
        if (mobileBackdrop) {
            mobileBackdrop.addEventListener('click', function () {
                document.body.classList.remove('sidebar-open');
                mobileToggle && mobileToggle.setAttribute('aria-expanded', 'false');
                mobileBackdrop.classList.add('hidden');
            });
        }

        // Close sidebar on route navigation in mobile (Livewire navigate)
        window.addEventListener('popstate', function () {
            document.body.classList.remove('sidebar-open');
        });
        function updateSidebarTitles() {
            const isCollapsed = document.body.classList.contains('sidebar-collapsed');
            document.querySelectorAll('#sidebar [data-title]').forEach(function (el) {
                el.title = isCollapsed ? el.getAttribute('data-title') : '';
            });
            if (collapseBtn) {
                collapseBtn.title = isCollapsed ? (collapseBtn.getAttribute('data-title') || 'Toggle sidebar') : '';
                collapseBtn.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
                const iconCollapse = collapseBtn.querySelector('.icon-collapse');
                const iconExpand = collapseBtn.querySelector('.icon-expand');
                if (iconCollapse && iconExpand) {
                    if (isCollapsed) { iconCollapse.classList.add('hidden'); iconExpand.classList.remove('hidden'); }
                    else { iconCollapse.classList.remove('hidden'); iconExpand.classList.add('hidden'); }
                }
            }
        }
        updateSidebarTitles();
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('a[data-section]');

        function highlightNav() {
            let current = '';

            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;

                if (window.scrollY >= (sectionTop - 100)) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('text-white');
                link.classList.add('text-gray-400');

                if (link.getAttribute('data-section') === current) {
                    link.classList.remove('text-gray-400');
                    link.classList.add('text-white');
                    // Show the underline for active section
                    link.querySelector('span').classList.add('scale-x-100');
                    link.querySelector('span').classList.remove('scale-x-0');
                } else {
                    // Ensure other links' underlines are hidden when not active
                    link.querySelector('span').classList.remove('scale-x-100');
                    link.querySelector('span').classList.add('scale-x-0');
                }
            });
        }

        window.addEventListener('scroll', highlightNav);
        highlightNav(); // Run once on page load
    });
</script>