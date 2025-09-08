@props(['showSidebar' => false, 'showNav' => true])
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
    @livewireStyles
</head>

<body class="bg-[#060b16] text-[#c3beb6] min-h-screen flex flex-col">


    @if ($showNav)

        <x-navs>

            <a href="/" class="ml-3 flex items-center gap-2">

                <svg class="w-10 h-10 rotate-[-45deg]" fill="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <path d="M13,2l9,13.6L13,22ZM11,2,2,15.6,11,22Z"></path>
                    </g>
                </svg>
                <p class="font-playfair font-bold text-xl">LUMINA</p>

            </a>
            <div class="space-x-6 font-bold">
                <x-links href="#features" section="features">Features</x-links>
                <x-links href="#about" section="about">About</x-links>
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
            <aside class="w-64">
                @livewire('sidebar')
            </aside>
            <main class="bg-gradient-dark flex-1 bg-dot-pattern font-inter text-custom">
                {{ $slot }}
            </main>
        </div>
    @else
        <main class="bg-gradient-dark bg-dot-pattern font-inter text-custom">
            {{ $slot }}
        </main>
    @endif
    @livewireScripts

</body>



</html>
<script>
    document.addEventListener('DOMContentLoaded', function () {
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