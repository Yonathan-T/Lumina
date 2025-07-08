<x-layout :showNav="false" :showSidebar="true">
    <div class="min-h-screen flex flex-col">
        <div class="w-full max-w-3xl mx-auto pt-8">
            <a href="{{ route('archive.entries') }}"
                class="inline-flex items-center text-sm text-muted hover:text-white hover:bg-[rgb(29,40,58)] transition-colors px-4 py-2 rounded">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to History
            </a>
        </div>
        <div class="flex flex-1 items-center justify-center">
            <div id="entry-card"
                class="rounded-lg border border-white/15  shadow-sm card-highlight bg-gradient-dark w-full max-w-3xl mx-auto min-h-[400px] flex flex-col">
                <div class="p-10 flex flex-col flex-1 gap-6">
                    <div class="flex items-start justify-between mb-2">
                        <span class="relative inline-block ml-2">
                            <span
                                class="absolute inset-0 bg-gradient-to-r from-white/10 via-blue-200/10 to-transparent rounded-sm"></span>
                            <span class="text-3xl font-bold px-2">{{ $entry->title }}</span>
                        </span>
                        <div class="text-muted text-sm ml-4 whitespace-nowrap mt-1">
                            {{ $entry->created_at->format('M d, Y') }}
                        </div>
                    </div>
                    <div class="ml-5 font-inter text-md leading-relaxed flex-1 ">
                        {{ $entry->content }}
                    </div>
                    <div>
                        @foreach($entry->tags as $tag)
                            <span
                                class="inline-block bg-[rgb(15,23,42)] text-white text-xs px-3 py-1 rounded-full ml-5  mr-2 mb-2">#{{ $tag->name }}</span>
                        @endforeach
                    </div>
                    <div class="flex justify-between items-center mt-8 border-t border-white/10 pt-6">
                        <a href="#"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium text-white border border-white/10 hover:bg-[rgb(29,40,58)] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            Edit
                        </a>
                        <button onclick="confirmDelete()"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>