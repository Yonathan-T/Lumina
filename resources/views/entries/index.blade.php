<x-layout :showNav="false">
<!-- 
  <h1 class="text-2xl font-bold">
    @if(now()->hour < 12) Good morning, 
    @elseif(now()->hour < 17) Good afternoon,
    @else Good evening, 
    @endif
    {{ auth()->user()->name }}!
  </h1>
  resources/views/dashboard.blade.php -->

<!-- Navbar -->
<header class="fixed w-full h-12 bg-[#1f1f1f] text-white flex items-center justify-between px-4 top-0 left-0 z-50 shadow">
  <!-- Left: Toggle, Logo, Search -->
  <div class="flex items-center gap-4">
    <button id="toggleSidebar" class="text-xl hover:text-[#c2b68e] focus:outline-none">
      ☰
    </button>
    <span class="text-lg font-bold text-[#c2b68e]">Memo Mate</span>

    <!-- 🔍 Search Bar -->
    
  </div>

  <!-- Right: Profile Icon -->
  <div class="flex items-center gap-3">
    <span class="text-sm">{{auth()->user()->name}}</span>
    <img class="w-8 h-8 rounded-full" src="{{ Vite::asset('resources/images/diary.png') }}" alt="Profile" />
  </div>
</header>


<!-- Sidebar -->

<section class=" pt-16 pl-[270px] transition-all duration-300" id="mainContent">
  <!-- NEW user-->
  @livewire('sidebar')



</section>





</x-layout>


<!-- SEARCH BAR 
<div class="relative">
      <input
        type="text"
        placeholder="Search..."
        class="bg-[#2a2a2a] text-white placeholder-gray-400 text-sm rounded-full pl-10 pr-4 py-1.5 focus:outline-none focus:ring-2 focus:ring-[#c2b68e]"
      />
      <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
        
      </span>
    </div> -->