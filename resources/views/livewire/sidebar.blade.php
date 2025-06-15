<div>
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-40 w-64 flex flex-col sidebar-gradient border-r border-[#c2b68e] bg-white/5 transition-transform duration-300">

        {{-- Brand / Logo --}}
        <div class="flex items-center justify-between h-14 border-white/10 border-b px-4">
            <div class="flex items-center gap-2 font-semibold text-white">
                <x-custom-icon />
                <span class="text-lg font-bold">Memo Mate</span>
            </div>
        </div>
        {{-- Navigation --}}
        <nav class="flex-1 overflow-auto p-4 space-y-2 text-sm text-gray-400">
            <a href="{{ route('dashboard') }}" wire:navigate
                class="flex items-center gap-3 rounded-md px-3 py-2 transition-colors hover:text-white hover:bg-blue-300/15 {{ request()->routeIs('dashboard') ? 'bg-blue-300/15 text-white' : '' }}">
                <x-ri-dashboard-line class="w-4 h-4" />
                Dashboard
            </a>

            <a href="{{ route('entries.create') }}"
                class="flex items-center gap-3 rounded-md px-3 py-2 transition-colors hover:text-white hover:bg-blue-300/15 {{ request()->routeIs('entries.create') ? 'bg-blue-300/15 text-white' : '' }}">

                <x-iconpark-writingfluently-o class="w-4 h-4" />
                New Entry
            </a>

            <a href="{{ route('entries.index') }}"
                class="flex items-center gap-3 rounded-md px-3 py-2 transition-colors hover:text-white hover:bg-blue-300/15 {{ request()->routeIs('entries.index') ? 'bg-blue-300/15 text-white' : '' }}">

                <x-heroicon-o-calendar-days class="w-4 h-4" />
                History
            </a>

            <a href="{{ route('tags.index') }}"
                class="flex items-center gap-3 rounded-md px-3 py-2 transition-colors hover:text-white hover:bg-blue-300/15 {{ request()->routeIs('tags.index') ? 'bg-blue-300/15 text-white' : '' }}">

                <x-heroicon-o-tag class="w-4 h-4" />
                Tags
            </a>

            <a href="{{ route('insights.index') }}"
                class="flex items-center gap-3 rounded-md px-3 py-2 transition-colors hover:text-white hover:bg-blue-300/15 {{ request()->routeIs('insights.index') ? 'bg-blue-300/15 text-white' : '' }}">

                <x-sui-graph-bar class="w-4 h-4" />
                Insights
            </a>

            <a href="{{ route('settings.index') }}"
                class="flex items-center gap-3 rounded-md px-3 py-2 transition-colors hover:text-white hover:bg-blue-300/15 {{ request()->routeIs('settings.index') ? 'bg-blue-300/15 text-white' : 'text-gray-400' }}">

                <x-elemplus-setting class="w-4 h-4" />
                Settings
            </a>
        </nav>

        {{-- Upgrade Button --}}
        <div class=" p-4">
            <a href="#" type="button"
                class="z-222 h-10 px-4 py-2 w-full border border-white/10 hover:bg-blue-300/15 flex items-center rounded-md justify-center gap-2">
                <x-iconsax-lin-verify class="w-5 h-5" />
                Upgrade to Pro
            </a>
        </div>
        {{-- User Profile --}}
        @php
            use Illuminate\Support\Str;

            $userName = auth()->user()->name ?? 'Guest';
            $firstLetter = Str::ucfirst(Str::substr($userName, 0, 1));
        @endphp

        <div class="flex items-center gap-2 px-4 py-4 border-white/10 border-t text-white">
            @if(auth()->user() && auth()->user()->profile_photo_url)
                <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ $userName }}"
                    class="w-8 h-8 rounded-full border border-white object-cover" />
            @else
                <div class="w-8 h-8 rounded-full border border-white bg-gray-700 flex items-center justify-center text-sm font-semibold uppercase"
                    title="{{ $userName }}">
                    {{ $firstLetter }}
                </div>
            @endif

            <div class="flex flex-col">
                <span class="text-sm font-medium">{{ $userName }}</span>
                <span class="text-xs text-muted-foreground">Free Plan</span>
            </div>
        </div>


    </aside>




</div>