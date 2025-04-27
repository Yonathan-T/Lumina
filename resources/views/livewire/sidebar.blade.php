<div> 
    <aside id="sidebar" class="fixed top-12 left-0 bottom-0 w-[270px] bg-white/5 rounded-r-lg border-[#c2b68e] flex flex-col z-40 transition-transform duration-300">
        <div class="mt-4 p-4 overflow-y-auto">
            <x-buttons class="mb-4" wire:click="openNewMemoForm">
                <x-icons type="chat" /> New Memo
            </x-buttons>
            <div class="flex-1 overflow-y-auto p-2">
                <div class="mb-3">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider px-0 py-1">Today</p>
<!-- day tracking logic above -->
                    @foreach ($entries as $entry)
                        <button 
                            wire:click="selectEntry({{ $entry->id }})" 
                            class="block w-full  text-left px-3 py-2 text-sm rounded-lg hover:bg-[#c2b68e]/10 text-white {{ $selectedEntryId === $entry->id ? 'bg-[#c2b68e]/20' : '' }}">
                            {{ $entry->title }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </aside>
@if (!$selectedEntry  ||  $showNewMemoForm)
    <x-entries.authed-user/>
@else
<div class="mt-6 p-6 max-w-3xl mx-auto bg-white/5 rounded-lg shadow-xl flex flex-col min-h-[300px]">
    <div class="flex-grow"> <!-- This pushes the footer down -->
                <span class="relative inline-block ml-2">
                    <span class="absolute inset-0 bg-gradient-to-r from-[#7c6a54] to-transparent rounded-sm"></span>
                    <h2 class=" text-3xl font-semibold text-white relative  px-2">{{ $selectedEntry->title }}</h2>
                </span>
        <p class="mt-4  text-sm text-white ">{{ $selectedEntry->content }}</p>
    </div>

    <div class="mt-auto pt-4 flex justify-between items-center border-t border-white/10">
        <div class="flex flex-start">
            <p class="text-sm text-gray-500">Created on: {{ $selectedEntry->created_at->format('M d, Y') }}</p>

        </div>
        <div class="justify-end space-x-4">
            <x-buttons href="/entry/edit/{{ $selectedEntry->id }}" class="!py-2 !px-4">Edit Entry</x-buttons>
<x-form-button :buttonsDelete="true">Delete</x-form-button>
        </div>
    </div>
</div>
@endif
</div> 