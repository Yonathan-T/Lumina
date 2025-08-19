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

            <a href="{{ route('entries.create') }}" wire:navigate
                class="flex items-center gap-3 rounded-md px-3 py-2 transition-colors hover:text-white hover:bg-blue-300/15 {{ request()->routeIs('entries.create') ? 'bg-blue-300/15 text-white' : '' }}">

                <x-iconpark-writingfluently-o class="w-4 h-4" />
                New Entry
            </a>

            <a href="{{ route('archive.entries') }}" wire:navigate
                class="flex items-center gap-3 rounded-md px-3 py-2 transition-colors hover:text-white hover:bg-blue-300/15 {{ request()->routeIs('entries.index') ? 'bg-blue-300/15 text-white' : '' }}">

                <x-heroicon-o-calendar-days class="w-4 h-4" />
                History
            </a>

            <a href="{{ route('tags.index') }}" wire:navigate
                class="flex items-center gap-3 rounded-md px-3 py-2 transition-colors hover:text-white hover:bg-blue-300/15 {{ request()->routeIs('tags.index') ? 'bg-blue-300/15 text-white' : '' }}">

                <x-heroicon-o-tag class="w-4 h-4" />
                Tags
            </a>

            <a href="{{ route('chat.index') }}" wire:navigate
                class="flex items-center gap-3 rounded-md px-3 py-2 transition-colors hover:text-white hover:bg-blue-300/15 {{ request()->routeIs('chat.index') ? 'bg-blue-300/15 text-white' : '' }}">

                <x-icon name="chatbubbles-outline" class="w-4 h-4" />
                Chat
            </a>
            <a href="{{ route('insights.index') }}" wire:navigate
                class="flex items-center gap-3 rounded-md px-3 py-2 transition-colors hover:text-white hover:bg-blue-300/15 {{ request()->routeIs('insights.index') ? 'bg-blue-300/15 text-white' : '' }}">

                <x-sui-graph-bar class="w-4 h-4" />
                Insights
            </a>

            <a href="{{ route('settings.index') }}" wire:navigate
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

        @php
            $profilePhotoUrl = auth()->user()->profile_photo_url ?? null;
            $userEmail = auth()->user()->email ?? 'guest@example.com';
        @endphp

        <button id="profileButton"
            class="flex items-center gap-2 px-4 py-4 border-white/10 border-t text-white cursor-pointer">
            @if($profilePhotoUrl)
                <img src="{{ $profilePhotoUrl }}" alt="{{ $userName }}"
                    class="w-8 h-8 rounded-full border border-white object-cover" />
            @else
                <div class="w-8 h-8 rounded-full border border-white bg-gray-700 flex items-center justify-center text-sm font-semibold uppercase"
                    title="{{ $userName }}">
                    {{ $firstLetter }}
                </div>
            @endif

            <div class="flex flex-col">
                <span class="text-sm font-medium">{{ $userName }}</span>
                <span class="text-xs text-muted">Free Plan</span>
            </div>
        </button>
        <div id="profileMenu"
            class="hidden sidebar-gradient absolute left-0 right-0 bottom-16 mx-4 w-auto bg-white/10 border border-white/10 rounded-lg shadow-lg z-50 backdrop-blur-md">
            <div class="flex items-center gap-3 p-3 border-b border-white/10">
                @if($profilePhotoUrl)
                    <img src="{{ $profilePhotoUrl }}" alt="{{ $userName }}"
                        class="w-10 h-10 rounded-full border border-white object-cover" />
                @else
                    <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center text-base font-semibold uppercase"
                        title="{{ $userName }}">
                        {{ $firstLetter }}
                    </div>
                @endif

                <div class="flex flex-col">
                    <span class="font-medium text-white truncate">{{ $userName }}</span>
                    <span class="text-xs text-gray-300 truncate">{{ $userEmail }}</span>
                </div>
            </div>
            <ul class="py-2">

                <li class="flex items-center px-4 py-2 hover:bg-blue-300/15 text-white transition-colors rounded-md">
                    <div class="flex items-center gap-2">
                        <x-icon name="moon" class="w-4 h-4" />
                        <span>Dark Mode</span>
                    </div>
                    <div class="ml-auto">
                        <x-toggle :model="'darkMode'" />
                    </div>
                </li>
                <li>
                    <a href="#"
                        class="flex items-center gap-2 px-4 py-2 hover:bg-blue-300/15 text-white transition-colors rounded-md">
                        <x-icon name="sparkles" class="w-4 h-4" />
                        Upgrade plan
                    </a>
                </li>
                <li>
                    <a href="{{ route('settings.index', ['tab' => 'account']) }}"
                        class="flex items-center gap-2 px-4 py-2 hover:bg-blue-300/15 text-white transition-colors rounded-md">
                        <x-icon name="user-round" class="w-4 h-4" />
                        Account
                    </a>
                </li>

                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit"
                            class="cursor-pointer w-full text-left px-4 py-2 hover:bg-blue-300/15 text-white transition-colors rounded-md flex items-center gap-2">
                            <x-icon name="log-out" class="w-4 h-4" />
                            <span>sign out</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>


    </aside>




</div>