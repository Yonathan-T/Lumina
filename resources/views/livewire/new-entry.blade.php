<div class="container p-6 mx-auto" id="mainContent">

    <div class="space-y-6 relative">

        <div class="mt-4 flex items-center justify-between">
            <h1 class="text-3xl font-bold tracking-tight">New Entry</h1>
            <div class="text-sm text-muted">
                {{ \Carbon\Carbon::now()->format('l, F j, Y • H:i A') }}
            </div>
        </div>
        <!-- <form method="POST" action="/entries"> Should i use livewire or controller -->
        <form wire:submit="save">
            @csrf
            <div class="space-y-4">
                <div class="relative group" x-data="{
                        preview: null,
                        selectFile(e) {
                            const file = e.target.files[0];
                            if (!file) return;

                            // 1. Instant 0ms visual preview
                            this.preview = URL.createObjectURL(file);

                            // 2. High-speed client optimization before upload
                            const upload = (fileToUpload) => {
                                $wire.upload('banner', fileToUpload, () => {}, (err) => {
                                    console.error('Banner upload error:', err);
                                });
                            };

                            if (file.type && file.type.startsWith('image/') && file.size > 800 * 1024) {
                                const img = new Image();
                                img.onload = () => {
                                    const maxDim = 1920;
                                    let w = img.width;
                                    let h = img.height;
                                    if (w > maxDim || h > maxDim) {
                                        if (w > h) {
                                            h = Math.round((h * maxDim) / w);
                                            w = maxDim;
                                        } else {
                                            w = Math.round((w * maxDim) / h);
                                            h = maxDim;
                                        }
                                    }
                                    const canvas = document.createElement('canvas');
                                    canvas.width = w;
                                    canvas.height = h;
                                    const ctx = canvas.getContext('2d');
                                    ctx.drawImage(img, 0, 0, w, h);
                                    canvas.toBlob((blob) => {
                                        if (blob) {
                                            const optimized = new File([blob], file.name.replace(/\.[^/.]+$/, '.jpg'), { type: 'image/jpeg' });
                                            upload(optimized);
                                        } else {
                                            upload(file);
                                        }
                                    }, 'image/jpeg', 0.85);
                                };
                                img.onerror = () => upload(file);
                                img.src = this.preview;
                            } else {
                                upload(file);
                            }
                        },
                        clear() {
                            this.preview = null;
                            if (this.$refs.bannerFileInput) {
                                this.$refs.bannerFileInput.value = '';
                            }
                            $wire.set('banner', null);
                        }
                    }">

                    {{-- Single hidden file input controlled by refs --}}
                    <input type="file" x-ref="bannerFileInput" x-on:change="selectFile($event)" class="hidden" accept="image/*">

                    {{-- Preview Container --}}
                    <div x-show="preview" x-cloak class="relative w-full h-64 rounded-xl overflow-hidden border border-white/10 shadow-2xl">
                        <img :src="preview" class="w-full h-full object-cover" alt="Cover preview">
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors"></div>

                        <button type="button" x-on:click="clear()"
                            class="absolute top-4 right-4 bg-black/50 text-white p-2 rounded-full hover:bg-red-500/80 transition-all opacity-0 group-hover:opacity-100 backdrop-blur-sm cursor-pointer"
                            title="Remove Cover">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        <div class="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button type="button" x-on:click="$refs.bannerFileInput.click()"
                                class="cursor-pointer bg-black/50 text-white px-4 py-2 rounded-lg hover:bg-white/20 backdrop-blur-sm text-sm font-medium flex items-center gap-2 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Change Cover
                            </button>
                        </div>
                    </div>

                    {{-- Dropzone --}}
                    <div x-show="!preview"
                        x-on:click="$refs.bannerFileInput.click()"
                        class="cursor-pointer group flex flex-col items-center justify-center w-full h-32 rounded-xl border-2 border-dashed border-white/10 hover:border-white/20 hover:bg-white/5 transition-all duration-200">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6 text-muted group-hover:text-white transition-colors">
                            <svg class="w-8 h-8 mb-3 opacity-50 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-sm font-medium">Add Cover Image</p>
                        </div>
                    </div>

                    <x-form-error name="banner" />
                </div>

                <div>
                    <input type="text" wire:model.defer="title"
                        class="flex h-10 w-full rounded-md px-3 py-2 bg-background text-2xl font-semibold placeholder:text-[rgb(65,74,90)] border-none focus:outline-none focus:ring-0"
                        placeholder="Title your entry" />
                    <x-form-error name="title" />
                </div>

                <textarea wire:model.defer="content"
                    class="flex w-full rounded-md border bg-background px-3 py-2 text-sm sm:min-h-[200px] md:min-h-[300px] lg:min-h-[400px] resize-none border-none  placeholder:text-[rgb(65,74,90)] focus:outline-none focus:ring-0"
                    placeholder="What's on your mind today!"></textarea>
                <x-form-error name="content" />

                <div class="space-y-2" x-data="{
                        tags: $wire.entangle('selectedTags'),
                        newTag: '',
                        error: null,
                        add() {
                            const t = this.newTag.trim();
                            if (t === '') { this.error = 'Tag cannot be empty.'; return; }
                            if (/\s/.test(t)) { this.error = 'Tags cannot contain spaces.'; return; }
                            if (this.tags.map(x => x.toLowerCase()).includes(t.toLowerCase())) { this.error = 'You already added this tag.'; return; }
                            this.tags.push(t);
                            this.newTag = '';
                            this.error = null;
                        },
                        remove(t) { this.tags = this.tags.filter(x => x.toLowerCase() !== t.toLowerCase()); }
                    }">
                    <label class="block text-sm font-medium">Tags</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="tag in tags" :key="tag">
                            <span class="inline-flex items-center px-2 py-1 bg-background rounded text-sm">
                                <span x-text="tag"></span>
                                <button type="button" x-on:click="remove(tag)"
                                    class="ml-1 text-red-white hover:rounded-lg hover:text-white/15">&times;</button>
                            </span>
                        </template>
                        <input x-model="newTag" x-on:keydown.enter.prevent="add()" id="tag-input-field"
                            class="flex h-10 rounded-md border border-input px-3 py-2 text-sm w-48 border-none bg-background placeholder:text-[rgb(65,74,90)] focus:outline-none focus:ring-0"
                            placeholder="Add a tag and press Enter" type="text">
                    </div>
                    <div x-show="error" x-cloak x-text="error" class="text-red-500 text-sm mt-1"></div>
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('dashboard') }}" wire:navigate.hover
                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors border border-white/10 bg-background backdrop-blur-sm hover:bg-white/5 transition duration-200 hover:text-accent h-10 px-4 py-2 text-white">
                        Discard Draft
                    </a>
                    <button type="submit" 
                        wire:loading.attr="disabled"
                        wire:target="save, banner"
                        class="inline-flex items-center justify-center gap-2 rounded-md bg-white text-black px-5 py-2 text-sm font-semibold hover:bg-gray-200 transition disabled:opacity-50 cursor-pointer min-w-[120px]">
                        <span wire:loading.remove wire:target="save, banner">Save Entry</span>
                        <div wire:loading wire:target="save, banner" class="h-4 w-4 animate-spin rounded-full border-2 border-black border-t-transparent"></div>
                    </button>
                </div>

            </div>

        </form>

        <!-- Confirmation Modal -->
        @if($showConfirmationModal)
            <div x-data="{ show: true }" x-show="show" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                @keydown.escape.window="show = false; $wire.goToDashboard()"
                @click.self="show = false; $wire.goToDashboard()">
                <div class="bg-card border border-white/10 rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl animate-in fade-in zoom-in-95 duration-150"
                    @click.stop>
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
                            <a href="{{ route('dashboard') }}" wire:navigate.hover
                                class="flex-1 px-4 py-2 text-sm font-medium text-muted hover:text-white border border-white/10 rounded-md hover:bg-white/5 transition-colors inline-flex items-center justify-center cursor-pointer">
                                Maybe Later
                            </a>
                            <button wire:click="startEntryChat"
                                wire:loading.attr="disabled"
                                wire:target="startEntryChat"
                                class="flex-1 px-4 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors inline-flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer min-h-[38px]">
                                <span wire:loading.remove wire:target="startEntryChat">Yes, Let's Talk!</span>
                                <div wire:loading wire:target="startEntryChat" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Chat Modal -->
        @if($showQuickChatModal)
            <div x-data="{ show: true }" x-show="show" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                @keydown.escape.window="show = false; $wire.closeQuickChatModal()"
                @click.self="show = false; $wire.closeQuickChatModal()">
                <div class="bg-card border border-white/10 rounded-lg max-w-2xl w-full mx-4 h-[600px] flex flex-col shadow-xl animate-in fade-in zoom-in-95 duration-150"
                    @click.stop>
                    <!-- Header -->
                    <div class="flex items-center justify-between p-4 border-b border-white/10">
                        <h3 class="text-lg font-semibold text-white">Chat About Your Entry</h3>
                        <button type="button" @click="show = false; $wire.closeQuickChatModal()"
                            class="p-1 text-muted hover:text-white hover:bg-white/10 rounded-lg transition-colors cursor-pointer"
                            aria-label="Close chat">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Messages & Input container with Alpine for optimistic rendering -->
                    <div class="flex-1 flex flex-col overflow-hidden"
                        x-data="{
                            pendingMessage: '',
                            scrollToBottom() {
                                $nextTick(() => {
                                    const container = this.$refs.quickChatScroll;
                                    if (container) {
                                        container.scrollTop = container.scrollHeight;
                                    }
                                });
                            },
                            submitMessage() {
                                const form = this.$refs.quickChatForm;
                                const textarea = form ? form.querySelector('textarea') : null;
                                const msg = textarea ? textarea.value.trim() : '';
                                if (!msg) return;

                                this.pendingMessage = msg;
                                textarea.value = '';
                                textarea.style.height = 'auto';
                                this.scrollToBottom();

                                $wire.sendQuickChat(msg).then(() => {
                                    this.pendingMessage = '';
                                    this.scrollToBottom();
                                }).catch(() => {
                                    this.pendingMessage = '';
                                    this.scrollToBottom();
                                });
                            }
                        }"
                        x-init="scrollToBottom()"
                        x-effect="scrollToBottom()">

                        <!-- Messages -->
                        <div x-ref="quickChatScroll" class="flex-1 overflow-y-auto p-4 space-y-4 scrollbar-none">
                            @foreach($quickChatMessages as $message)
                                <div class="flex {{ $message['sender'] === 'user' ? 'justify-end' : 'justify-start' }}">
                                    <div
                                        class="max-w-[80%] {{ $message['sender'] === 'user' ? 'bg-primary text-primary-foreground' : 'bg-muted/20 text-white' }} rounded-lg p-3">
                                        <p class="text-sm whitespace-pre-wrap">{{ $message['content'] }}</p>
                                        <p class="text-xs opacity-70 mt-1">{{ $message['timestamp'] }}</p>
                                    </div>
                                </div>
                            @endforeach

                            <!-- Optimistic Pending User Message -->
                            <div x-show="pendingMessage" x-cloak class="flex justify-end">
                                <div class="max-w-[80%] bg-primary text-primary-foreground rounded-lg p-3">
                                    <p class="text-sm whitespace-pre-wrap" x-text="pendingMessage"></p>
                                    <p class="text-xs opacity-70 mt-1">Just now</p>
                                </div>
                            </div>

                            <!-- Thinking Indicator -->
                            <div wire:loading wire:target="sendQuickChat" class="flex justify-start">
                                <div class="bg-muted/20 rounded-lg p-3">
                                    <div class="flex items-center space-x-2">
                                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                                        <span class="text-sm text-muted">Thinking...</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Input -->
                        <div class="p-4 border-t border-white/10" x-ref="quickChatForm" @submit.prevent="submitMessage()">
                            <x-chat-form wire-model="quickChatInput"
                                placeholder="Share your thoughts..." :is-disabled="$quickChatLoading" :is-typing="$quickChatLoading"
                                submit-icon="send" typing-icon="stop" :use-client-submit="true" form-id="entry-quick-chat-form" />
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
