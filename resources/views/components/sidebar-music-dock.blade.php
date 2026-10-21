<div 
    id="sidebar-music-dock" 
    x-data="{
        isPlaying: false,
        isConnecting: false,
        isBuffering: false,
        isSlotOpen: false,
        isDocked: false,
        isExpanded: false, // Compressed by default into the top row
        isLooping: false,
        trackTitle: '01-Ladyfingers-Lofi',
        audioMode: localStorage.getItem('sanctuary_audio_mode') || 'sanctuary', // 'sanctuary' | 'youtube'
        youtubeUrlInput: '',
        youtubeLoadedTitle: localStorage.getItem('sanctuary_youtube_title') || '',
        hasYouTubeTrack: Boolean(localStorage.getItem('sanctuary_youtube_url')),
        youtubeError: '',

        init() {
            this.syncState();
            window.addEventListener('sanctuary-audio-state', (e) => {
                if (e.detail) {
                    this.isPlaying = !!e.detail.isPlaying;
                    this.isLooping = !!e.detail.isLooping;
                    this.isConnecting = !!e.detail.isConnecting;
                    this.isBuffering = !!e.detail.isBuffering;
                    if (e.detail.mode) {
                        this.audioMode = e.detail.mode;
                    }
                    if (e.detail.track && e.detail.track.title) {
                        this.trackTitle = e.detail.track.title;
                    }
                    if (e.detail.youtube) {
                        this.hasYouTubeTrack = Boolean(e.detail.youtube.videoId || localStorage.getItem('sanctuary_youtube_url'));
                        this.youtubeLoadedTitle = e.detail.youtube.title || localStorage.getItem('sanctuary_youtube_title') || '';
                    }
                }
            });
            window.addEventListener('music-prepare-dock', () => {
                this.isSlotOpen = true;
            });
            window.addEventListener('music-docked', () => {
                window.isMusicFlying = false;
                this.isSlotOpen = true;
                this.isDocked = true;
                this.isPlaying = true;
                this.isExpanded = false;
                try { sessionStorage.setItem('sanctuary_docked', '1'); } catch(e){}
                this.syncState();
                this.$nextTick(() => {
                    const btn = document.getElementById('sidebar-music-dock-card');
                    if (btn && document.body.classList.contains('sidebar-collapsed')) {
                        btn.title = 'Ambient Music - ' + this.trackTitle;
                    }
                });
            });
            window.addEventListener('music-undocked', () => {
                this.isSlotOpen = false;
                this.isDocked = false;
                this.isPlaying = false;
                this.isExpanded = false;
            });
            document.addEventListener('livewire:navigated', () => {
                this.syncState();
            });
        },

        syncState() {
            if (window.SanctuaryAudio) {
                this.isPlaying = typeof window.SanctuaryAudio.getIsPlaying === 'function' ? window.SanctuaryAudio.getIsPlaying() : false;
                this.isLooping = typeof window.SanctuaryAudio.getIsLooping === 'function' ? window.SanctuaryAudio.getIsLooping() : false;
                if (typeof window.SanctuaryAudio.getMode === 'function') {
                    this.audioMode = window.SanctuaryAudio.getMode();
                }
                const cur = typeof window.SanctuaryAudio.getCurrentTrack === 'function' ? window.SanctuaryAudio.getCurrentTrack() : null;
                if (cur && cur.title) {
                    this.trackTitle = cur.title;
                }
                if (typeof window.SanctuaryAudio.getYouTubeData === 'function') {
                    const yt = window.SanctuaryAudio.getYouTubeData();
                    if (yt) {
                        this.hasYouTubeTrack = Boolean(yt.videoId || localStorage.getItem('sanctuary_youtube_url'));
                        this.youtubeLoadedTitle = yt.title || localStorage.getItem('sanctuary_youtube_title') || '';
                        this.isConnecting = !!yt.isConnecting;
                        this.isBuffering = !!yt.isBuffering;
                    }
                }
            }
            if (!window.isMusicFlying && (sessionStorage.getItem('sanctuary_docked') === '1' || sessionStorage.getItem('sanctuary_audio_active') === '1')) {
                this.isSlotOpen = true;
                this.isDocked = true;
            } else if (!this.isPlaying && sessionStorage.getItem('sanctuary_docked') !== '1') {
                this.isSlotOpen = false;
                this.isDocked = false;
            }
        },

        switchMode(mode) {
            this.audioMode = mode;
            this.youtubeError = '';
            if (mode === 'youtube') {
                const storedUrl = localStorage.getItem('sanctuary_youtube_url');
                if (storedUrl) {
                    this.hasYouTubeTrack = true;
                    this.youtubeLoadedTitle = localStorage.getItem('sanctuary_youtube_title') || this.youtubeLoadedTitle || 'YouTube Stream';
                }
            }
            if (window.SanctuaryAudio && typeof window.SanctuaryAudio.setMode === 'function') {
                window.SanctuaryAudio.setMode(mode, true);
            }
        },

        submitYouTube() {
            if (!this.youtubeUrlInput || !this.youtubeUrlInput.trim()) return;
            if (window.SanctuaryAudio && typeof window.SanctuaryAudio.loadYouTube === 'function') {
                const res = window.SanctuaryAudio.loadYouTube(this.youtubeUrlInput.trim(), true);
                if (!res.success) {
                    this.youtubeError = res.error || 'Invalid YouTube link';
                } else {
                    this.youtubeError = '';
                    this.youtubeUrlInput = '';
                    this.hasYouTubeTrack = true;
                    this.audioMode = 'youtube';
                    this.isPlaying = true;
                    this.isConnecting = true;
                }
            }
        },

        clearYouTube() {
            if (window.SanctuaryAudio && typeof window.SanctuaryAudio.clearYouTube === 'function') {
                window.SanctuaryAudio.clearYouTube();
            }
            this.hasYouTubeTrack = false;
            this.youtubeLoadedTitle = '';
            this.youtubeUrlInput = '';
            this.youtubeError = '';
            this.audioMode = 'sanctuary';
        },

        togglePlay() {
            if (window.SanctuaryAudio && typeof window.SanctuaryAudio.toggle === 'function') {
                window.SanctuaryAudio.toggle();
            }
        },

        nextTrack() {
            if (window.SanctuaryAudio && typeof window.SanctuaryAudio.next === 'function') {
                window.SanctuaryAudio.next(true);
                const cur = typeof window.SanctuaryAudio.getCurrentTrack === 'function' ? window.SanctuaryAudio.getCurrentTrack() : null;
                if (cur && cur.title) {
                    this.trackTitle = cur.title;
                }
                this.isPlaying = true;
            }
        },

        prevTrack() {
            if (window.SanctuaryAudio && typeof window.SanctuaryAudio.prev === 'function') {
                window.SanctuaryAudio.prev(true);
                const cur = typeof window.SanctuaryAudio.getCurrentTrack === 'function' ? window.SanctuaryAudio.getCurrentTrack() : null;
                if (cur && cur.title) {
                    this.trackTitle = cur.title;
                }
                this.isPlaying = true;
            }
        },

        toggleLoop() {
            if (window.SanctuaryAudio && typeof window.SanctuaryAudio.toggleLoop === 'function') {
                this.isLooping = window.SanctuaryAudio.toggleLoop();
            } else {
                this.isLooping = !this.isLooping;
            }
        },

        stopMusic() {
            if (window.SanctuaryAudio && typeof window.SanctuaryAudio.pause === 'function') {
                window.SanctuaryAudio.pause();
            }
            this.isPlaying = false;
            this.isSlotOpen = false;
            this.isDocked = false;
            this.isExpanded = false;
            window.isMusicFlying = false;
            try { sessionStorage.removeItem('sanctuary_docked'); } catch(e){}
            window.dispatchEvent(new CustomEvent('music-undocked'));
            window.dispatchEvent(new CustomEvent('sanctuary-audio-state', { detail: { isPlaying: false } }));
        }
    }"
    class="w-full transition-all duration-300 ease-in-out"
    :class="isSlotOpen ? 'mb-2' : 'max-h-0 m-0 overflow-hidden'"
>
    <style>
        @keyframes skiperSidebarWaveA { 0%, 100% { height: 3px; } 50% { height: 13px; } }
        @keyframes skiperSidebarWaveB { 0%, 100% { height: 11px; } 50% { height: 4px; } }
        @keyframes skiperSidebarWaveC { 0%, 100% { height: 4px; } 50% { height: 15px; } }
        @keyframes skiperSidebarWaveD { 0%, 100% { height: 13px; } 50% { height: 3px; } }
        @keyframes skiperSidebarWaveE { 0%, 100% { height: 3px; } 50% { height: 10px; } }

        .skiper-gold-bar-1 { animation: skiperSidebarWaveA 1.05s ease-in-out infinite; }
        .skiper-gold-bar-2 { animation: skiperSidebarWaveB 0.90s ease-in-out infinite 0.15s; }
        .skiper-gold-bar-3 { animation: skiperSidebarWaveC 1.25s ease-in-out infinite 0.30s; }
        .skiper-gold-bar-4 { animation: skiperSidebarWaveD 0.98s ease-in-out infinite 0.08s; }
        .skiper-gold-bar-5 { animation: skiperSidebarWaveE 1.20s ease-in-out infinite 0.22s; }
    </style>

    <div 
        id="sidebar-music-dock-card"
        @click="if (document.body.classList.contains('sidebar-collapsed')) { togglePlay(); } else { isExpanded = !isExpanded; }"
        class="group relative flex flex-col justify-center w-full px-3 py-2 min-h-[40px] rounded-md border border-white/10 hover:bg-blue-300/15 text-white select-none transition-all duration-300 ease-in-out cursor-pointer overflow-hidden shadow-sm"
        :class="[
            isDocked ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none',
            isExpanded ? 'bg-blue-300/10 border-white/20' : ''
        ]"
        data-title="Ambient Music (Sanctuary)"
    >
        <!-- Top Row: Vinyl Disc, Title, Soundwaves, Chevron Toggle, Stop/Close -->
        <div class="flex items-center justify-between w-full">
            <!-- Left: Spinning Vinyl Disc / Connection Spinner & Track Name -->
            <div 
                class="flex items-center gap-2 min-w-0"
                :class="document.body.classList.contains('sidebar-collapsed') ? 'w-full justify-center' : ''"
            >
                <!-- Loading / Connecting Spinner Ring -->
                <template x-if="isConnecting || isBuffering">
                    <span class="w-5 h-5 flex items-center justify-center shrink-0 sidebar-icon text-blue-400" :title="isConnecting ? 'Connecting to stream...' : 'Buffering...'">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                    </span>
                </template>
                <!-- Vinyl Disc -->
                <template x-if="!(isConnecting || isBuffering)">
                    <span 
                        class="w-5 h-5 flex items-center justify-center shrink-0 sidebar-icon text-white transition-transform duration-300"
                        :class="isPlaying ? 'animate-[spin_4s_linear_infinite]' : ''"
                        title="Ambient Music"
                    >
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 14.5c-2.49 0-4.5-2.01-4.5-4.5S9.51 7.5 12 7.5s4.5 2.01 4.5 4.5-2.01 4.5-4.5 4.5zm0-5.5c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1z"/>
                        </svg>
                    </span>
                </template>
                <span class="sidebar-label text-sm font-medium text-white truncate" x-text="isConnecting ? 'Connecting...' : (isBuffering ? 'Buffering...' : trackTitle)">01-Ladyfingers-Lofi</span>
            </div>

            <!-- Right: Animated Soundwaves, Chevron Toggle & Close/Stop Button -->
            <div class="sidebar-label flex items-center gap-1.5 shrink-0">

                <!-- Animated Soundwaves -->
                <div x-show="isPlaying && !isConnecting && !isBuffering" class="flex items-center gap-[2px] h-3 px-1 mr-0.5">
                    <span class="w-[2px] rounded-full bg-white/80 skiper-gold-bar-1 inline-block"></span>
                    <span class="w-[2px] rounded-full bg-white/80 skiper-gold-bar-2 inline-block"></span>
                    <span class="w-[2px] rounded-full bg-white/80 skiper-gold-bar-3 inline-block"></span>
                    <span class="w-[2px] rounded-full bg-white/80 skiper-gold-bar-4 inline-block"></span>
                    <span class="w-[2px] rounded-full bg-white/80 skiper-gold-bar-5 inline-block"></span>
                </div>

                <!-- Expand / Compress Chevron Button -->
                <button 
                    type="button" 
                    @click.stop="isExpanded = !isExpanded"
                    class="p-1 rounded text-white/60 hover:text-white hover:bg-white/10 transition cursor-pointer"
                    :title="isExpanded ? 'Compress controls' : 'Show playback controls'"
                >
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-in-out" :class="isExpanded ? 'rotate-180 text-blue-400' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Stop Button (Undock) -->
                <button 
                    type="button" 
                    @click.stop="stopMusic()" 
                    class="p-1 rounded text-white/40 hover:text-white hover:bg-white/10 transition cursor-pointer"
                    title="Close & Return to Dashboard"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Bottom Row: Collapsible Controls Container -->
        <div 
            class="grid transition-all duration-300 ease-in-out w-full overflow-hidden"
            :class="isExpanded ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0 pointer-events-none'"
        >
            <div class="overflow-hidden min-h-0 w-full">
                <div class="pt-2 mt-2 border-t border-white/10 flex flex-col w-full">
                    
                    <!-- Segmented Mode Switcher: 2 Icons (Home Sanctuary vs YouTube) -->
                    <div class="flex items-center gap-1 p-0.5 rounded-lg bg-black/40 border border-white/10 mb-2">
                        <button 
                            type="button" 
                            @click.stop="switchMode('sanctuary')"
                            class="flex-1 py-1.5 px-2 rounded-md transition cursor-pointer flex items-center justify-center"
                            :class="audioMode === 'sanctuary' ? 'bg-blue-500/25 text-blue-400 border border-blue-400/30 shadow-xs' : 'text-gray-400 hover:text-white border border-transparent'"
                            title="Sanctuary Built-in Playlist"
                        >
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                            </svg>
                        </button>

                        <button 
                            type="button" 
                            @click.stop="switchMode('youtube')"
                            class="flex-1 py-1.5 px-2 rounded-md transition cursor-pointer flex items-center justify-center"
                            :class="audioMode === 'youtube' ? 'bg-red-500/20 text-red-500 border border-red-500/30 shadow-xs' : 'text-gray-400 hover:text-white border border-transparent'"
                            title="YouTube Audio / Custom Stream"
                        >
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                            </svg>
                        </button>
                    </div>

                    <!-- YouTube Mode Input / Track View -->
                    <div x-show="audioMode === 'youtube'" class="mb-2">
                        <!-- Loaded YouTube Track Pill -->
                        <div 
                            x-show="hasYouTubeTrack" 
                            class="p-1.5 rounded-md bg-white/[0.04] border border-white/10 flex items-center justify-between gap-1.5 mb-1.5 transition-all duration-200"
                            :class="(isConnecting || isBuffering) ? 'opacity-40 blur-[0.5px] pointer-events-none select-none' : ''"
                        >
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="w-2 h-2 rounded-full bg-red-500 shrink-0" :class="isPlaying && !isConnecting && !isBuffering ? 'animate-pulse' : ''"></span>
                                <span class="text-[11px] text-gray-200 truncate" x-text="youtubeLoadedTitle || 'YouTube Stream'"></span>
                            </div>
                            <button 
                                type="button" 
                                @click.stop="clearYouTube()" 
                                :disabled="isConnecting || isBuffering"
                                class="text-[10px] text-gray-400 hover:text-white hover:underline shrink-0 cursor-pointer disabled:opacity-40"
                                title="Paste a different link"
                            >
                                Change
                            </button>
                        </div>

                        <!-- Input to Paste Any YouTube Link -->
                        <div x-show="!hasYouTubeTrack" class="space-y-1">
                            <div class="flex items-center gap-1.5">
                                <input 
                                    type="text" 
                                    x-model="youtubeUrlInput" 
                                    @click.stop 
                                    @keydown.enter.stop="submitYouTube()" 
                                    placeholder="Paste YouTube link or stream..." 
                                    class="w-full text-xs bg-black/50 border border-white/15 focus:border-blue-400/70 rounded-md px-2 py-1.5 text-gray-200 placeholder:text-gray-500 focus:outline-none" 
                                />
                                <button 
                                    type="button" 
                                    @click.stop="submitYouTube()" 
                                    :disabled="isConnecting"
                                    class="w-7 h-7 rounded-md bg-[#131d31] hover:bg-[#1c2a45] text-blue-300 hover:text-white border border-blue-500/30 hover:border-blue-400/50 transition shrink-0 cursor-pointer shadow-xs flex items-center justify-center disabled:opacity-50"
                                    title="Play YouTube Link"
                                >
                                    <svg class="w-3.5 h-3.5 fill-current ml-0.5" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </button>
                            </div>
                            <div x-show="youtubeError" class="text-[10px] text-rose-400" x-text="youtubeError"></div>
                            <p class="text-[10px] text-gray-500">Play any video, music, or 24/7 stream</p>
                        </div>
                    </div>

                    <!-- Controls Row -->
                    <div class="flex items-center justify-between px-1.5 w-full">
                        <!-- Previous Track < (Only in Sanctuary Mode) -->
                        <button 
                            x-show="audioMode === 'sanctuary'"
                            type="button" 
                            @click.stop="prevTrack()" 
                            class="flex items-center justify-center w-7 h-7 rounded-md text-white/70 hover:text-white hover:bg-white/10 transition cursor-pointer"
                            title="Previous Track"
                        >
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                <path d="M6 6h2v12H6zm3.5 6l8.5 6V6z"/>
                            </svg>
                        </button>

                        <div x-show="audioMode === 'youtube'" class="w-7"></div>

                        <!-- Play / Pause Button -->
                        <button 
                            type="button" 
                            @click.stop="togglePlay()" 
                            class="flex items-center justify-center w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-white transition cursor-pointer shadow-sm"
                            :title="isPlaying ? 'Pause' : 'Play'"
                        >
                            <template x-if="isPlaying">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                    <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                                </svg>
                            </template>
                            <template x-if="!isPlaying">
                                <svg class="w-3.5 h-3.5 fill-current ml-0.5" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </template>
                        </button>

                        <!-- Next Track > (Only in Sanctuary Mode) -->
                        <button 
                            x-show="audioMode === 'sanctuary'"
                            type="button" 
                            @click.stop="nextTrack()" 
                            class="flex items-center justify-center w-7 h-7 rounded-md text-white/70 hover:text-white hover:bg-white/10 transition cursor-pointer"
                            title="Next Track"
                        >
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                <path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/>
                            </svg>
                        </button>

                        <div x-show="audioMode === 'youtube'" class="w-7"></div>

                        <!-- Loop Toggle -->
                        <button 
                            type="button" 
                            @click.stop="toggleLoop()" 
                            class="flex items-center justify-center w-7 h-7 rounded-md transition-all cursor-pointer relative"
                            :class="isLooping 
                                ? 'text-blue-400 bg-blue-500/20 border border-blue-400/40 shadow-[0_0_8px_rgba(59,130,246,0.3)]' 
                                : 'text-white/40 hover:text-white hover:bg-white/10 border border-transparent'"
                            :title="isLooping ? 'Loop: ON (Repeating)' : 'Loop: OFF'"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span x-show="isLooping" class="absolute -top-0.5 -right-0.5 w-1.5 h-1.5 rounded-full bg-blue-400 shadow-[0_0_6px_rgba(96,165,250,1)]"></span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
