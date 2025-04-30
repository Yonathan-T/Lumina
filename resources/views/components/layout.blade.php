<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memo Mate</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css' , 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#1b1b1b] text-white">
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
                <a href="/register" class="border border-white/25 px-3 py-2 rounded-lg hover:border-[#7c6a54] ">Sign Up</a>  
            </div>
        </x-navs>
        @endif
        <main class="mt-10">
            
            {{$slot}}
        </main>
    </div>
    @livewireScripts
</body>
</html>