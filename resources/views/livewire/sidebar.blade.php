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
            <a href="{{ route('dashboard') }}"
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


    <div>
        @if (!$selectedEntry || $showNewMemoForm)
            <x-entries.entriesForm />
        @elseif($openSearchForm)
            <!-- there is a bug here -->
            <x-entries.searchForm />
        @elseif($selectedEntry)

            <div class="mt-6 p-6 max-w-3xl mx-auto bg-[#030711] rounded-lg shadow-xl flex flex-col min-h-[450px]">
                <div class="flex-grow">
                    @if ($isEditing)
                        <input type="text"
                            class="w-full bg-transparent border-b border-[#c2b68e] text-2xl font-semibold px-2 mb-4"
                            wire:model.defer="editedTitle" />
                        <textarea class="w-full h-60 bg-transparent border border-[#c2b68e] p-2 text-sm"
                            wire:model.defer="editedContent"></textarea>
                    @else
                        <span class="relative inline-block ml-2">
                            <span class="absolute inset-0 bg-gradient-to-r from-white/15 to-transparent rounded-sm"></span>
                            <h2 class=" text-3xl font-semibold relative font-playfair px-2">{{ $selectedEntry->title }}</h2>
                        </span>
                        <p class="mt-4 text-sm">{!! nl2br(e($selectedEntry->content)) !!}</p>

                    @endif
                </div>

                <!-- Tags Section -->
                <div class="mb-2">
                    @if ($isEditing)
                        <label class="block mt-4 mb-2 text-sm">Add or Edit Tags (comma-separated)</label>
                        <input type="text" wire:model.defer="editedTags"
                            class="w-full bg-transparent border-b border-[#c2b68e] text-sm px-2 mb-2" />
                    @else
                        <div class="mt-2 flex flex-wrap gap-1">
                            @foreach($selectedEntry->tags as $tag)
                                <li
                                    class=" bg-white/5 border hover:border-[#c2b68e] rounded-full py-1 px-2 text-gray-500 hover:text-[#c2b68e] text-xs flex items-center">
                                    <span class="mr-1 font-semibold text-grey">#</span>{{ $tag->name }}
                                </li>
                            @endforeach
                        </div>
                    @endif
                </div>






                <!-- Footer Baby -->
                <div class="mt-auto pt-4 flex justify-between items-center border-t border-white/10">
                    <div class="">
                        <p class="text-sm text-[#c2b68e]/30">Created on: {{ $selectedEntry->created_at->format('M d, Y') }}
                        </p>
                    </div>

                    <div>

                        @if ($isEditing)
                            <x-buttons wire:click="saveEntry" class="!py-2 !px-4">Save</x-buttons>
                        @else
                            <x-buttons wire:click="editEntry" class="!py-2 !px-4">Edit Entry</x-buttons>
                        @endif

                    </div>
                    <!-- cant a user delete anything bro? -->

                </div>
            </div>
        @endif
    </div>

</div>