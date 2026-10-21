<div class="m-3 flex flex-col" x-data="{ showDeleteModal: false, showMissingVoiceKey: @entangle('showMissingElevenLabsModal') }">
    {{-- Floating notification toast (sticky on top of the viewport regardless of scroll position) --}}
    <div class="fixed top-6 right-6 z-50 flex flex-col gap-3 max-w-md w-full px-4 sm:px-0 pointer-events-none">
        @if (session('message'))
            <div id="success-message"
                x-data="{
                    show: true,
                    timeout: null,
                    startTimer() {
                        this.clearTimer();
                        this.timeout = setTimeout(() => { this.show = false; }, 4000);
                    },
                    clearTimer() {
                        if (this.timeout) {
                            clearTimeout(this.timeout);
                            this.timeout = null;
                        }
                    }
                }"
                x-init="startTimer()"
                @mouseenter="clearTimer()"
                @mouseleave="startTimer()"
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-2"
                class="pointer-events-auto bg-[#0a1813]/95 border border-emerald-500/40 rounded-xl px-5 py-4 shadow-2xl shadow-emerald-950/50 backdrop-blur-md transition-all duration-300">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center flex-shrink-0 text-emerald-400 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-emerald-100 leading-snug">{{ session('message') }}</span>
                    </div>
                    <button @click="show = false"
                        class="text-gray-400 hover:text-white transition-colors p-1 rounded-md hover:bg-white/10 flex-shrink-0 cursor-pointer"
                        aria-label="Dismiss notification">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div id="error-message"
                x-data="{
                    show: true,
                    timeout: null,
                    startTimer() {
                        this.clearTimer();
                        this.timeout = setTimeout(() => { this.show = false; }, 6000);
                    },
                    clearTimer() {
                        if (this.timeout) {
                            clearTimeout(this.timeout);
                            this.timeout = null;
                        }
                    }
                }"
                x-init="startTimer()"
                @mouseenter="clearTimer()"
                @mouseleave="startTimer()"
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-2"
                class="pointer-events-auto bg-[#1b0d13]/95 border border-red-500/30 rounded-xl px-5 py-4 shadow-2xl backdrop-blur-md transition-all duration-300">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center flex-shrink-0 text-red-400 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-white leading-snug">{{ session('error') }}</span>
                    </div>
                    <button @click="show = false"
                        class="text-gray-400 hover:text-white transition-colors p-1 rounded-md hover:bg-white/10 flex-shrink-0 cursor-pointer"
                        aria-label="Dismiss notification">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <div class="{{ \App\Helpers\FontHelper::getFontClass() }}" data-font-bind data-font-size-bind
            style="font-size: {{ \App\Helpers\FontHelper::getFontSize() }}px;">
            <div class="p-10 flex flex-col flex-1 gap-6">

                @if($isEditing)
                    <!-- Edit Mode -->
                    <form wire:submit="save">
                        <div class="space-y-6">

                            <div class="mb-6 relative group" x-data="{
                                preview: {{ !empty($entry->banner_path && $entry->banner_url) ? "'" . $entry->banner_url . "'" : 'null' }},
                                isUploading: false,
                                uploadProgress: 0,
                                bannerError: null,
                                selectFile(e) {
                                    this.bannerError = null;
                                    const file = e.target.files && e.target.files[0];
                                    if (!file) return;

                                    // 1. Instant 0ms Client-Side Format Validation
                                    const isImage = file.type ? file.type.startsWith('image/') : /\.(jpe?g|png|webp|gif|avif|svg)$/i.test(file.name);
                                    if (!isImage) {
                                        this.bannerError = 'Unsupported format. Please select an image file (JPG, PNG, WEBP, GIF).';
                                        this.clearInput();
                                        return;
                                    }

                                    // 2. Instant 0ms Client-Side Size Validation (Max 10MB)
                                    const maxBytes = 10 * 1024 * 1024;
                                    if (file.size > maxBytes) {
                                        const sizeMb = (file.size / (1024 * 1024)).toFixed(1);
                                        this.bannerError = `This file is ${sizeMb} MB. Maximum allowed image size is 10 MB.`;
                                        this.clearInput();
                                        return;
                                    }

                                    // 3. Instant 0ms visual preview
                                    if (this.preview && this.preview.startsWith('blob:')) {
                                        URL.revokeObjectURL(this.preview);
                                    }
                                    this.preview = URL.createObjectURL(file);
                                    this.isUploading = true;
                                    this.uploadProgress = 15;

                                    // 4. High-speed client upload with progress tracking
                                    const doUpload = (fileToUpload) => {
                                        $wire.upload('banner', fileToUpload, 
                                            () => {
                                                this.isUploading = false;
                                                this.uploadProgress = 100;
                                                this.bannerError = null;
                                            }, 
                                            (err) => {
                                                this.isUploading = false;
                                                this.bannerError = 'Upload failed. Please try a different image.';
                                                console.error('Banner upload error:', err);
                                            },
                                            (event) => {
                                                this.uploadProgress = event.detail.progress;
                                            }
                                        );
                                    };

                                    // Fast in-browser canvas compression if image > 800KB
                                    if (file.type && file.type.startsWith('image/') && !file.type.includes('svg') && !file.type.includes('gif') && file.size > 800 * 1024) {
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
                                                if (blob && blob.size < file.size) {
                                                    const optimized = new File([blob], file.name.replace(/\.[^/.]+$/, '.jpg'), { type: 'image/jpeg' });
                                                    doUpload(optimized);
                                                } else {
                                                    doUpload(file);
                                                }
                                            }, 'image/jpeg', 0.85);
                                        };
                                        img.onerror = () => doUpload(file);
                                        img.src = this.preview;
                                    } else {
                                        doUpload(file);
                                    }
                                },
                                clearInput() {
                                    if (this.$refs.bannerFileInput) {
                                        this.$refs.bannerFileInput.value = '';
                                    }
                                },
                                clear() {
                                    if (this.preview && this.preview.startsWith('blob:')) {
                                        URL.revokeObjectURL(this.preview);
                                    }
                                    this.preview = null;
                                    this.clearInput();
                                    this.bannerError = null;
                                    this.isUploading = false;
                                    $wire.set('banner', null);
                                    $wire.removeBanner();
                                }
                            }">
                                <label class="block text-sm font-medium mb-2 text-muted">Banner Image</label>

                                {{-- Single hidden file input controlled by refs --}}
                                <input type="file" name="banner" x-ref="bannerFileInput" x-on:change="selectFile($event)" class="hidden" accept="image/jpeg,image/png,image/webp,image/gif,image/avif">

                                {{-- Preview Container --}}
                                <div x-show="preview" x-cloak class="relative w-full h-64 rounded-xl overflow-hidden border border-white/10 shadow-2xl">
                                    <img :src="preview" class="w-full h-full object-cover" alt="Cover preview">
                                    <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors"></div>

                                    {{-- Uploading Progress Overlay --}}
                                    <div x-show="isUploading" x-cloak class="absolute inset-0 bg-black/60 backdrop-blur-xs flex flex-col items-center justify-center gap-2 z-20">
                                        <div class="flex items-center gap-2 text-xs font-medium text-white">
                                            <div class="w-3.5 h-3.5 border-2 border-blue-400 border-t-transparent rounded-full animate-spin"></div>
                                            <span>Optimizing & uploading...</span>
                                        </div>
                                        <div class="w-48 bg-white/10 rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-blue-400 h-full transition-all duration-150" :style="'width: ' + uploadProgress + '%'"></div>
                                        </div>
                                    </div>

                                    <button type="button" x-on:click="clear()"
                                        class="absolute top-4 right-4 bg-black/50 text-white p-2 rounded-full hover:bg-red-500/80 transition-all opacity-0 group-hover:opacity-100 backdrop-blur-sm cursor-pointer z-10"
                                        title="Remove Cover">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                    <div class="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity z-10">
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
                                        <p class="text-xs text-muted/60 mt-1">(JPG, PNG, WEBP, GIF) (10MB)</p>
                                    </div>
                                </div>

                                {{-- Client-Side Instant Error Display --}}
                                <div x-show="bannerError" x-cloak class="mt-2.5 flex items-center gap-2 p-3 text-xs rounded-lg bg-red-500/10 border border-red-500/30 text-red-200">
                                    <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span x-text="bannerError"></span>
                                </div>

                                <x-form-error name="banner" />
                            </div>

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
                                <x-memo-canvas-editor wireModel="content" name="content" :initialContent="$content" />
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
                                    <span class="inline-flex items-center gap-2">
                                        <input wire:model="newTag" wire:keydown.enter.prevent="addTag"
                                            id="tag-input-field"
                                            class="flex h-10 rounded-md border border-white/10 px-3 py-2 text-sm w-48 bg-background placeholder:text-[rgb(65,74,90)] focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            placeholder="Add a tag and press Enter" type="text">
                                        <button type="button" wire:click="addTag"
                                            class="px-3 py-2 text-xs font-medium rounded-md bg-white/10 text-white hover:bg-white/20 transition-colors cursor-pointer">
                                            Add
                                        </button>
                                    </span>
                                </div>
                                @if($tagError)
                                    <div class="text-red-500 text-sm mt-1">{{ $tagError }}</div>
                                @endif
                            </div>

                            <div class="flex justify-between items-center mt-8 border-t border-white/10 pt-6">
                                <button type="button" @click="isEditing = false" wire:click="cancelEditing"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium text-white border border-white/10 hover:bg-[rgb(29,40,58)] transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Cancel
                                </button>
                                <div class="flex gap-2">
                                    <button type="submit"
                                        wire:loading.attr="disabled"
                                        wire:target="save, banner"
                                        @click="
                                            const canvas = document.querySelector('[data-memo-canvas]');
                                            if (canvas && canvas.__memoFlushSync) canvas.__memoFlushSync();
                                        "
                                        class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors disabled:opacity-50 cursor-pointer min-w-[130px]">
                                        <span wire:loading.remove wire:target="save, banner">Save Changes</span>
                                        <span wire:loading wire:target="save, banner" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent inline-block"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                @else

                    @if($entry->banner_path && $entry->banner_url)
                        <div class="w-full h-64 rounded-lg overflow-hidden mb-6 border border-white/10">
                            <img src="{{ $entry->banner_url }}" class="w-full h-full object-cover" alt="Entry Banner" onerror="this.parentElement.style.display='none'">
                        </div>
                    @endif

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
                                class="cursor-pointer group relative p-2 rounded-lg border border-white/10 hover:border-purple-500/50 hover:bg-purple-500/10 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                <div wire:loading.remove wire:target="generateAudio">
                                    <x-icon name="voice"
                                        class="w-5 h-5 text-gray-400 group-hover:text-purple-400 transition-colors" />
                                </div>
                                <div wire:loading wire:target="generateAudio">
                                    <svg class="w-5 h-5 text-purple-400 animate-spin" fill="none" viewBox="0 0 24 24">
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
                                    Listen to Entry
                                </span>
                            </button>

                            {{-- PDF Download Button --}}
                            <button wire:click="downloadPdf" wire:loading.attr="disabled"
                                class="cursor-pointer group relative p-2 rounded-lg border border-white/10 hover:border-blue-500/50 hover:bg-blue-500/10 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
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

                    <div class="ml-5 text-md leading-relaxed flex-1 memo-rendered-content">
                        {!! preg_replace('/<ul>(\s*<li[^>]*><input[^>]*type="checkbox")/i', '<ul class="memo-task-list">$1', \Illuminate\Support\Str::markdown($entry->content)) !!}
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
                                    <audio controls autoplay class="w-full h-10 rounded-lg">
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
                            wire:loading.attr="disabled"
                            wire:target="startEditing"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md text-sm font-medium text-white border border-white/10 hover:bg-[rgb(29,40,58)] transition-colors cursor-pointer min-w-[80px]">
                            <span wire:loading.remove wire:target="startEditing" class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                                Edit
                            </span>
                            <span wire:loading wire:target="startEditing" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent inline-block"></span>
                        </button>
                        <button type="button" @click="showDeleteModal = true"
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

    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" @keydown.escape.window="showDeleteModal = false">
        <!-- blur -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showDeleteModal = false"></div>

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
                Are you sure you want to delete this entry?
                <br><br>
                This action cannot be undone and will permanently remove it from your journal.
            </p>

            <div class="flex gap-3">
                <button type="button" @click="showDeleteModal = false"
                    class="flex-1 px-4 py-3 rounded-md text-sm font-medium text-white border border-white/10 hover:bg-[rgb(29,40,58)] transition-colors">
                    Cancel
                </button>
                <button type="button" wire:click="confirmDelete" @click="showDeleteModal = false"
                    class="flex-1 px-4 py-3 rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition-colors inline-flex items-center justify-center gap-2">
                    Delete Entry
                </button>
            </div>
        </div>
    </div>

    <div x-show="showMissingVoiceKey" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="showMissingVoiceKey = false">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showMissingVoiceKey = false"></div>

        <div class="relative bg-gradient-dark border border-purple-500/30 rounded-xl shadow-2xl p-6 sm:p-8 max-w-md w-full mx-4 transform transition-all">
            <div class="flex justify-center mb-5">
                <div class="w-16 h-16 bg-purple-500/20 ring-1 ring-purple-500/30 rounded-2xl flex items-center justify-center">
                    <x-icon name="voice" class="w-8 h-8 text-purple-400" />
                </div>
            </div>

            <h3 class="text-xl font-bold text-center text-white mb-2">ElevenLabs API Key Required</h3>

            <p class="text-gray-300 text-center mb-6 leading-relaxed text-sm">
                To listen to your journal entries with AI voice narration, please add your own ElevenLabs API key. We operate on a Bring-Your-Own-Key model and do not provide a default voice key.
            </p>

            <div class="space-y-3">
                <a href="{{ route('settings.index', ['tab' => 'account']) }}"
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 transition-colors shadow-lg shadow-purple-600/25">
                    <span>Add Key in Settings</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
                <button type="button" @click="showMissingVoiceKey = false"
                    class="w-full px-4 py-2.5 rounded-lg text-sm font-medium text-gray-400 hover:text-white border border-white/10 hover:bg-white/5 transition-colors">
                    Maybe Later
                </button>
            </div>
        </div>
    </div>
</div>