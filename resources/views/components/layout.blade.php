<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memo Mate</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @livewireStyles
</head>

<body class="bg-[#060b16] text-[#c3beb6] bg-dot-pattern min-h-screen flex flex-col">
    <div class="px-18 relative ">

        @if ($showNav ?? true)
            <x-navs>

                <div>
                    <a href="/" class="">
                        <img src="{{Vite::asset('resources/images/logo 3.png')}}" class="h-10 w-auto" alt="">
                    </a>
                </div>
                <div class="space-x-6 font-bold">
                    <x-links>Features</x-links>
                    <x-links>About</x-links>
                    <x-links>Premium</x-links>
                    @auth
                        <x-links href="/dashboard">My Journals</x-links>
                    @endauth
                    <x-links>Contact</x-links>
                </div>
                <div>
                    <a href="/register" class="
                                                border border-white/25 rounded-lg px-3 py-2
                                                bg-[#060b16] text-white font-semibold
                                                shadow-[1px_1px_rgba(255,255,255,0.15),2px_2px_rgba(255,255,255,0.1),3px_3px_rgba(255,255,255,0.07),4px_4px_rgba(255,255,255,0.05)]
                                                hover:border-white/10 hover:shadow-sm
                                                active:translate-y-[2px] active:shadow-[1px_1px_rgba(124,106,84,0.5),1px_1px_rgba(124,106,84,0.5)]
                                                transition-all duration-200 ease-in-out
                                                select-none
                                                inline-block
                                                ">
                        Sign Up
                    </a>
                </div>
            </x-navs>
        @endif
        <main class="mt-10 flex-grow">

            {{$slot}}
        </main>
    </div>
    @livewireScripts
</body>



</html>