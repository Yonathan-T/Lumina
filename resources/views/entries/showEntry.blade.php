<x-layout :showNav="false" :showSidebar="true" class="flex ">
    <div class="flex items-center justify-center min-h-screen bg-gradient-dark">
        <div
            class="flex-center rounded-lg border border-white/15 text-card-foreground shadow-sm card-highlight bg-gradient-dark w-full max-w-3xl mx-auto">
            <div class="p-10 flex flex-col gap-6 min-h-[70vh]">
                <div class="flex items-start justify-between mb-2">
                    <h1 class="text-3xl font-bold">{{ $entry->title }}</h1>
                    <div class="text-muted-foreground text-sm ml-4 whitespace-nowrap mt-1">
                        {{ $entry->created_at->format('M d, Y') }}
                    </div>
                </div>
                <div class="text-lg leading-relaxed flex-1">{{ $entry->content }}</div>
                <div class="text-lg leading-relaxed flex-1">
                    @foreach($entry->tags as $tag)
                        <span class="inline-block mr-2">#{{ $tag->name }}</span>
                    @endforeach
                </div>
                <div class="flex gap-4 justify-end items-center mt-8">
                    <a href="/entry/edit?entry={{ $entry->id }}">
                        <x-buttons>Edit</x-buttons>
                    </a>
                    <form action="/entries/{{ $entry->id }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this entry?');"
                        class="flex items-center">
                        @csrf
                        @method('DELETE')
                        <x-buttons class="bg-red-600 hover:bg-red-700 border-red-600">Delete</x-buttons>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>