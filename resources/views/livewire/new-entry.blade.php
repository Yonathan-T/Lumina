<div class="container mx-auto">
    <div class="space-y-6 relative">

        <div class="mt-4 flex items-center justify-between">
            <h1 class="text-3xl font-bold tracking-tight">New Entry</h1>
            <div class="text-sm text-muted">
                {{ \Carbon\Carbon::now('Africa/Addis_Ababa')->format('l, F j, Y • H:i A') }}
            </div>
        </div>
        <div class="space-y-4">
            <div>
                <input type="text"
                    class="flex h-10 w-full rounded-md px-3 py-2 bg-background text-2xl font-semibold placeholder:text-muted/5 border-none focus:outline-none focus:ring-0"
                    placeholder="Title your entry" />
            </div>
            <textarea
                class="flex w-full rounded-md border bg-background px-3 py-2 text-sm sm:min-h-[200px] md:min-h-[300px] lg:min-h-[400px] resize-none border-none  placeholder:text-muted/5 focus:outline-none focus:ring-0"
                placeholder="What's on your mind today!"></textarea>

            <div class="space-y-2">
                <label class="block text-sm font-medium">Tags</label>
                <div id="tag-input-wrapper" class="flex flex-wrap gap-2">
                    <input id="tag-input-field"
                        class="flex h-10 rounded-md border border-input px-3 py-2 text-sm w-48 border-none bg-background placeholder:text-muted/5 focus:outline-none focus:ring-0"
                        placeholder="Add a tag and press Enter" type="text">
                </div>
                <input type="hidden" name="tags" id="hidden-tags-input">
            </div>
            <div id="tag-error-message" class="hidden"></div>

            <div class="flex justify-end gap-2">
                <button
                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors  border border-white/10 bg-background backdrop-blur-sm hover:bg-white/5 transition duration-200 hover:text-accent h-10 px-4 py-2">Discard
                    Draft</button>
                <x-form-button>Save Entry</x-form-button>
            </div>

        </div>


    </div>
</div>