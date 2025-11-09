<div class="container p-6 mx-auto" id="mainContent">

    <div class="space-y-6 relative">

        <div class="mt-4 flex items-center justify-between">
            <h1 class="text-3xl font-bold tracking-tight">New Entry</h1>
            <div class="text-sm text-muted">
                {{ \Carbon\Carbon::now(timezone: 'Africa/Addis_Ababa')->format('l, F j, Y • H:i A') }}
                <!-- this should be based on location instead of hard coding the timezone my self   -->
            </div>
        </div>
        <!-- <form method="POST" action="/entries"> Should i use livewire or controller -->
        <form wire:submit.prevent="save">
            @csrf
            <div class="space-y-4">
                <div>
                    <input type="text" wire:model="title"
                        class="flex h-10 w-full rounded-md px-3 py-2 bg-background text-2xl font-semibold placeholder:text-[rgb(65,74,90)] border-none focus:outline-none focus:ring-0"
                        placeholder="Title your entry" />
                    <x-form-error name="title" />
                </div>
                <textarea wire:model.defer="content"
                    class="flex w-full rounded-md border bg-background px-3 py-2 text-sm sm:min-h-[200px] md:min-h-[300px] lg:min-h-[400px] resize-none border-none  placeholder:text-[rgb(65,74,90)] focus:outline-none focus:ring-0"
                    placeholder="What's on your mind today!"></textarea>
                <x-form-error name="content" />

                <div class="space-y-2">
                    <label class="block text-sm font-medium">Tags</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($selectedTags as $tag)
                            <span class="inline-flex items-center px-2 py-1 bg-background  rounded text-sm">
                                {{ $tag }}
                                <button type="button" wire:click="removeTag('{{ $tag }}')"
                                    class="ml-1 text-red-white hover:rounded-lg hover:text-white/15">&times;</button>
                            </span>
                        @endforeach
                        <input wire:model.defer="newTag" wire:keydown.enter.prevent="addTag" id="tag-input-field"
                            class="flex h-10 rounded-md border border-input px-3 py-2 text-sm w-48 border-none bg-background placeholder:text-[rgb(65,74,90)] focus:outline-none focus:ring-0"
                            placeholder="Add a tag and press Enter" type="text">
                    </div>
                    @if($tagError)
                        <div class="text-red-500 text-sm mt-1">{{ $tagError }}</div>
                    @endif
                </div>

                <div class="flex justify-end gap-2">
                    <button href="/dashboard"
                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors  border border-white/10 bg-background backdrop-blur-sm hover:bg-white/5 transition duration-200 hover:text-accent h-10 px-4 py-2">Discard
                        Draft</button>
                    <x-form-button>Save Entry</x-form-button>
                </div>

            </div>

        </form>

        <!-- Confirmation Modal -->
        @if($showConfirmationModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                <div class="bg-card border border-white/10 rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
                    <div class="text-center space-y-4">
                        <div class="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center mx-auto">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white">Entry Saved Successfully!</h3>
                        <p class="text-muted text-sm">Would you like to talk about what you just wrote? I can help you
                            reflect on your thoughts and feelings.</p>

                        <div class="flex gap-3 pt-2">
                            <button wire:click="goToDashboard"
                                class="flex-1 px-4 py-2 text-sm font-medium text-muted hover:text-white border border-white/10 rounded-md hover:bg-white/5 transition-colors">
                                Maybe Later
                            </button>
                            <button wire:click="startEntryChat"
                                class="flex-1 px-4 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors">
                                Yes, Let's Talk!
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Chat Modal -->
        @if($showQuickChatModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                <div
                    class="bg-card border border-white/10 rounded-lg max-w-2xl w-full mx-4 h-[600px] flex flex-col shadow-xl">
                    <!-- Header -->
                    <div class="flex items-center justify-between p-4 border-b border-white/10">
                        <h3 class="text-lg font-semibold text-white">Chat About Your Entry</h3>
                        <button wire:click="closeQuickChatModal" class="text-muted hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Messages -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-4">
                        @foreach($quickChatMessages as $message)
                            <div class="flex {{ $message['sender'] === 'user' ? 'justify-end' : 'justify-start' }}">
                                <div
                                    class="max-w-[80%] {{ $message['sender'] === 'user' ? 'bg-primary text-primary-foreground' : 'bg-muted/20 text-white' }} rounded-lg p-3">
                                    <p class="text-sm whitespace-pre-wrap">{{ $message['content'] }}</p>
                                    <p class="text-xs opacity-70 mt-1">{{ $message['timestamp'] }}</p>
                                </div>
                            </div>
                        @endforeach

                        @if($quickChatLoading)
                            <div class="flex justify-start">
                                <div class="bg-muted/20 rounded-lg p-3">
                                    <div class="flex items-center space-x-2">
                                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                                        <span class="text-sm text-muted">Thinking...</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Input -->
                    <div class="p-4 border-t border-white/10">
                        <x-chat-form wire-submit="sendQuickChat" wire-model="quickChatInput"
                            placeholder="Share your thoughts..." :is-disabled="$quickChatLoading"
                            :is-typing="$quickChatLoading" submit-icon="send" typing-icon="stop" />
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>