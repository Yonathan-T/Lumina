<div class="m-3 flex flex-col">
    <div class="mt-3 flex flex-1 items-center justify-center">

        @if (session('message'))
            <div id="success-message"
                class="bg-gradient-dark card-highlight border border-white/10 Rounded-lg px-6 py-4 shadow-lg backdrop-blur-sm border border-green-500/20">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="font-medium text-white">{{ session('message') }}</span>
                    </div>
                    <button onclick="dismissMessage('success-message')"
                        class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif


        @if (session('error'))
            <div id="error-message"
                class="bg-gradient-dark card-highlight rounded-lg px-6 py-4 shadow-lg backdrop-blur-sm border border-red-500/20">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span class="font-medium text-white">{{ session('error') }}</span>
                    </div>
                    <button onclick="dismissMessage('error-message')"
                        class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif


    </div>
    <div class="w-full max-w-3xl mx-auto pt-8 mb-3">
        <a href="{{ route('archive.entries') }}"
            class="inline-flex items-center text-sm text-muted hover:text-white hover:bg-[rgb(29,40,58)] transition-colors px-4 py-2 rounded">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to History
        </a>
    </div>



    <div id="entry-card"
        class="rounded-lg border border-[rgb(29,40,58)]/10 shadow-lg card-highlight bg-gradient-dark w-full max-w-3xl mx-auto min-h-[400px] flex flex-col">
        <div class="{{ \App\Helpers\FontHelper::getFontClass() }}" data-font-bind data-font-size-bind style="font-size: {{ \App\Helpers\FontHelper::getFontSize() }}px;">
            <div class="p-10 flex flex-col flex-1 gap-6">

                @if($isEditing)
                    <!-- Edit Mode -->
                    <form wire:submit.prevent="save">
                        <div class="space-y-6">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <input type="text" wire:model="title"
                                        class="flex h-12 w-full rounded-md px-3 py-2 bg-background text-3xl font-bold placeholder:text-[rgb(65,74,90)] border-none focus:outline-none focus:ring-0"
                                        placeholder="Title your entry" />
                                    <x-form-error name="title" />
                                </div>
                                <div class="text-muted text-sm ml-4 whitespace-nowrap mt-1">
                                    {{ $entry->created_at->format('M d, Y') }}
                                </div>
                            </div>

                            <div class="ml-5">
                                <textarea wire:model="content"
                                    class="flex w-full rounded-md border bg-background px-3 py-2 text-md leading-relaxed  resize-none border-none placeholder:text-[rgb(65,74,90)] focus:outline-none focus:ring-0 min-h-[300px]"
                                    placeholder="What's on your mind today!"></textarea>
                                <x-form-error name="content" />
                            </div>

                            <div class="ml-5 space-y-2">
                                <label class="block text-sm font-medium">Tags</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($selectedTags as $tag)
                                        <span class="inline-flex items-center px-2 py-1 bg-background rounded text-sm">
                                            {{ $tag }}
                                            <button type="button" wire:click="removeTag('{{ $tag }}')"
                                                class="ml-1 text-red-white hover:rounded-lg hover:text-white/15">&times;</button>
                                        </span>
                                    @endforeach
                                    <input wire:model.defer="newTag" wire:keydown.enter.prevent="addTag"
                                        id="tag-input-field"
                                        class="flex h-10 rounded-md border border-input px-3 py-2 text-sm w-48 border-none bg-background placeholder:text-[rgb(65,74,90)] focus:outline-none focus:ring-0"
                                        placeholder="Add a tag and press Enter" type="text">
                                </div>
                                @if($tagError)
                                    <div class="text-red-500 text-sm mt-1">{{ $tagError }}</div>
                                @endif
                            </div>

                            <div class="flex justify-between items-center mt-8 border-t border-white/10 pt-6">
                                <button type="button" wire:click="cancelEditing"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium text-white border border-white/10 hover:bg-[rgb(29,40,58)] transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Cancel
                                </button>
                                <div class="flex gap-2">
                                    <button type="submit"
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Save Changes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                @else

                    <div class="text-muted text-sm ml-4 whitespace-nowrap mt-1">
                        {{ $entry->created_at->format('M d, Y') }}

                    </div>
                    <div class="flex items-start justify-between mb-2">
                        <span class="relative inline-block ml-2">
                            <span
                                class="absolute inset-0 bg-gradient-to-r from-cyan-500/30 via-blue-400/10 to-transparent rounded-sm"></span>
                            <span
                                class="absolute inset-0 bg-gradient-to-r from-white/10 via-blue-200/10 to-transparent rounded-sm"></span>
                            <span class="text-3xl font-bold px-2">{{ $entry->title }}</span>
                        </span>

                        <div class="text-muted text-sm ml-4 whitespace-nowrap mt-1 flex gap-2">
                            {{-- Audio Player Button --}}
                            <button wire:click="generateAudio" wire:loading.attr="disabled"
                                class="cursor-pointer group relative p-2 rounded-lg border border-white/10 hover:border-purple-500/50 hover:bg-purple-500/10 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                title="Listen to Entry">
                                <div wire:loading.remove wire:target="generateAudio">
                                    <x-icon name="voice"
                                        class="w-5 h-5 text-gray-400 group-hover:text-purple-400 transition-colors" />

                                </div>
                                <div wire:loading wire:target="generateAudio">
                                    <x-icon name="voice"
                                        class="w-5 h-5 text-gray-400 group-hover:text-purple-400 transition-colors" />
                                </div>

                                {{-- Tooltip --}}
                                <span
                                    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                    Listen to Entry
                                </span>
                            </button>

                            {{-- PDF Download Button --}}
                            <button wire:click="downloadPdf" wire:loading.attr="disabled"
                                class="cursor-pointer group relative p-2 rounded-lg border border-white/10 hover:border-blue-500/50 hover:bg-blue-500/10 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                title="Download PDF">
                                <div wire:loading.remove wire:target="downloadPdf">
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-400 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </div>
                                <div wire:loading wire:target="downloadPdf">
                                    <svg class="w-5 h-5 text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>

                                {{-- Tooltip --}}
                                <span
                                    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                    Download PDF
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="ml-5  text-md leading-relaxed flex-1">
                        {!! nl2br(e($entry->content)) !!}
                    </div>

                    {{-- Audio Player --}}
                    @if($audioUrl)
                        <div id="audioCard"
                            class="mt-6 p-4 bg-gradient-to-r from-purple-500/10 to-blue-500/10 rounded-lg border border-purple-500/20 relative">
                            <div class="flex items-center gap-3">

                                <div class="flex-shrink-0">
                                    <x-icon name="voice"
                                        class="w-6 h-6 text-purple-400 group-hover:text-purple-400 transition-colors" />
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-white mb-1">Audio Reading</h4>
                                    <audio controls class="w-full h-10 rounded-lg">
                                        <source src="{{ $audioUrl }}" type="audio/mpeg">
                                        Your browser does not support the audio element.
                                    </audio>
                                </div>
                            </div>
                            <button onclick="dismissMessage('audioCard')"
                                class="absolute top-2 right-2 text-gray-400 hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                    @endif

                    <div>
                        @foreach($entry->tags as $tag)
                            <span
                                class="inline-block bg-[rgb(15,23,42)] text-white text-xs px-3 py-1 rounded-full ml-5 mr-2 mb-2">#{{ $tag->name }}</span>
                        @endforeach
                    </div>

                    <div class="flex justify-between items-center mt-8 border-t border-white/10 pt-6">
                        <button wire:click="startEditing"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium text-white border border-white/10 hover:bg-[rgb(29,40,58)] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            Edit
                        </button>
                        <button wire:click="showDeleteConfirmation"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                            Delete
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>


@if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <!-- blur -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="hideDeleteConfirmation"></div>

        <div
            class="relative bg-gradient-dark border border-[rgb(29,40,58)]/10 rounded-lg shadow-2xl p-8 max-w-md w-full mx-4 transform transition-all">
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                        </path>
                    </svg>
                </div>
            </div>

            <h3 class="text-xl font-bold text-center mb-2">Delete Entry</h3>

            <p class="text-muted text-center mb-8 leading-relaxed">
                Are you sure you want to delete this entry"?
                <br><br>
                This action cannot be undone and will permanently remove it from your journal.
            </p>

            <div class="flex gap-3">
                <button wire:click="hideDeleteConfirmation"
                    class="flex-1 px-4 py-3 rounded-md text-sm font-medium text-white border border-white/10 hover:bg-[rgb(29,40,58)] transition-colors">
                    Cancel
                </button>
                <button wire:click="confirmDelete"
                    class="flex-1 px-4 py-3 rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition-colors">
                    Delete Entry
                </button>
            </div>
        </div>
    </div>
@endif
</div>