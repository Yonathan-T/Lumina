<div class="max-w-4xl mx-auto">
    <div class="rounded-lg border border-white/15 text-card-foreground shadow-sm card-highlight bg-gradient-dark p-6">
        <h2 class="text-2xl font-semibold mb-6">Create New Entry</h2>

        <form wire:submit="save" class="space-y-6">
            <div>
                <label for="title" class="block text-sm font-medium mb-2">Title</label>
                <input type="text" wire:model="title" id="title"
                    class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    placeholder="Give your entry a title">
                @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="content" class="block text-sm font-medium mb-2">Content</label>
                <textarea wire:model="content" id="content" rows="10"
                    class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    placeholder="Write your thoughts here..."></textarea>
                @error('content') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Tags</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($availableTags as $tag)
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model="selectedTags" value="{{ $tag->id }}"
                                class="form-checkbox h-4 w-4 text-primary">
                            <span class="ml-2 text-sm">#{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('selectedTags') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    Save Entry
                </button>
            </div>
        </form>
    </div>
</div>