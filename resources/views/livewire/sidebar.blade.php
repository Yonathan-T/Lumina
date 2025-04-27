<div> 
    <aside id="sidebar" class="fixed top-12 left-0 bottom-0 w-[270px] bg-white/5 rounded-r-lg border-[#c2b68e] flex flex-col z-40 transition-transform duration-300">
        <div class="p-4 overflow-y-auto">
            <x-buttons class="mb-4" href="/entries">
                <x-icons type="chat" /> New Memo
            </x-buttons>
            <div class="flex-1 overflow-y-auto p-2">
                <div class="mb-3">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider px-0 py-1">Today</p>

                    @foreach (auth()->user()->entries as $entry)
                        <button 
                            wire:click="selectEntry({{ $entry->id }})" 
                            class="block w-full text-left px-3 py-2 text-sm rounded hover:bg-[#c2b68e]/15 text-white">
                            {{ $entry->title }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </aside>

    <!-- Debugging: Show a test message -->
     @if (! $selectedEntry)
        <x-entries.authed-user/>
    @else
    <div class="mt-6 p-6 max-w-3xl mx-auto bg-white/5 rounded-lg shadow-xl flex flex-col min-h-[300px]">
    <div class="flex-grow"> <!-- This pushes the footer down -->
        <h2 class="text-3xl font-semibold text-white">{{ $selectedEntry->title }}</h2>
        <p class="mt-4 text-sm text-white">{{ $selectedEntry->content }}</p>
    </div>
    
    <div class="mt-auto pt-4 flex justify-between items-center border-t border-white/10">
        <p class="text-sm text-gray-500">Created on: {{ $selectedEntry->created_at->format('M d, Y') }}</p>
        <x-buttons href="/entry/edit/{{ $selectedEntry->id }}" class="!py-2 !px-4">Edit Entry</x-buttons>
    </div>
</div>
@endif
</div> 