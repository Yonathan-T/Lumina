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
    <x-entries.entriesForm/>
@else
<div class="mt-6 p-6 max-w-3xl mx-auto bg-white/5 rounded-lg shadow-xl flex flex-col min-h-[450px]">
    <div class="flex-grow">
              
        @if ($isEditing)
        <input type="text" class="w-full bg-transparent border-b border-[#c2b68e] text-white text-2xl font-semibold px-2 mb-4" wire:model.defer="editedTitle" />
        <textarea class="w-full h-60 bg-transparent border border-[#c2b68e] text-white p-2 text-sm" wire:model.defer="editedContent"></textarea>
        @else
        <span class="relative inline-block ml-2">
            <span class="absolute inset-0 bg-gradient-to-r from-[#7c6a54] to-transparent rounded-sm"></span>
            <h2 class=" text-3xl font-semibold text-white relative  px-2">{{ $selectedEntry->title }}</h2>
        </span>
        <p class="mt-4 text-sm text-white">{{ $selectedEntry->content }}</p>
        @endif
    </div>

    <!-- Tags Section -->
    <div class="mb-2">
    @if ($isEditing)
    <label class="block text-white mt-4 mb-2 text-sm">Add or Edit Tags (comma-separated)</label>
    <input type="text" wire:model.defer="editedTags" class="w-full bg-transparent border-b border-[#c2b68e] text-white text-sm px-2 mb-2" />
@else
    <div class="mt-2 flex flex-wrap gap-1">
    @foreach($selectedEntry->tags as $tag)
                <li class=" bg-white/5 border hover:border-[#c2b68e] rounded-full py-1 px-2 text-gray-500 hover:text-[#c2b68e] text-xs flex items-center">
                    <span class="mr-1 font-semibold text-grey">#</span>{{ $tag->name }}
                </li>
            @endforeach
    </div>
@endif
</div>





<!-- Footer Baby -->
        <div class="mt-auto pt-4 flex justify-between items-center border-t border-white/10">
                <div class="">
                    <p class="text-sm text-[#c2b68e]/30">Created on: {{ $selectedEntry->created_at->format('M d, Y') }}</p>
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
</div>
@endif
</div> 