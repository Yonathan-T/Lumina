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
    <span class="text-sm">Yonathan</span>
    <img class="w-8 h-8 rounded-full" src="{{ Vite::asset('resources/images/diary.png') }}" alt="Profile" />
  </div>
</header>


<!-- Sidebar -->
<aside id="sidebar" class="fixed top-12 left-0 bottom-0 w-[270px] bg-white/5 rounded-r-lg border-[#c2b68e] flex flex-col z-40 transition-transform duration-300">
  <!-- Sidebar content -->
  <div class="p-4 overflow-y-auto">
    <!-- Same links and sections you had -->
    <x-buttons class="mb-4" href="/entries"><x-icons type="chat"/> New Memo</x-buttons>
    <div class="flex-1 overflow-y-auto p-2">
    <div class="mb-3">
      <p class="text-xs font-medium text-gray-500 uppercase tracking-wider px-0 py-1">Today</p>
      <a href="#" class="block px-3 py-2 text-sm rounded hover:bg-[#c2b68e]/15 text-white">Meeting Notes</a>
      <a href="#" class="block px-3 py-2 text-sm rounded hover:bg-[#c2b68e]/15 text-white">Daily Reflection</a>
    </div>
    
    <div class="mb-3">
      <p class="text-xs font-medium text-gray-500 uppercase tracking-wider px-0 py-1">Yesterday</p>
      <a href="#" class="block px-3 py-2 text-sm rounded hover:bg-[#c2b68e]/15 text-white">Weekly Goals</a>
      <a href="#" class="block px-3 py-2 text-sm rounded hover:bg-[#c2b68e]/15 text-white">Book Highlights</a>
    </div>
    <!-- ... -->
  </div>
</aside>

<section class=" pt-16 pl-[270px] transition-all duration-300" id="mainContent">
  <!-- NEW user-->
   @if(auth()->user()->entries)  
   <x-entries.authed-user/>
   @endif
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