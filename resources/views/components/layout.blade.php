<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memo Mate</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css' , 'resources/js/app.js'])
</head>
<body class="bg-[#1b1b1b] text-white">
    <div class="px-18 relative "> 
        <!-- fixed and glassyy top-0 inset-x-0 -->
        <nav class=" px-4 py-4 mt-5 flex justify-between items-center  border rounded-4xl border-white/10 backdrop-blur-2xl sticky top-5 z-10  ">
            <div>
                <a href="/" class="">
                    <img src="{{Vite::asset('resources/images/logo 3.png')}}" class="h-10 w-auto" alt="">
                </a>
            </div>
            <div class="space-x-6 font-bold">
                <x-navs>Features</x-navs>
                <x-navs>About</x-navs>
                <x-navs>Premium</x-navs>
                @auth
                <x-navs>My Journals</x-navs>
                @endauth
                <x-navs>Contact</x-navs>
            </div>
            <div>
                <a href="/register" class="border border-white/25 px-3 py-2 rounded-lg hover:border-[#7c6a54] ">Sign Up</a>  
            </div>
        </nav>
        <main class="mt-10">
           
            {{$slot}}
        </main>
    </div>
</body>
</html>