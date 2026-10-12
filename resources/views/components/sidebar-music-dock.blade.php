<div 
    id="sidebar-music-dock" 
    x-data="{
        isPlaying: false,
        isSlotOpen: false,
        isDocked: false,
        isExpanded: false, // Compressed by default into the top row
        isLooping: false,
        trackTitle: '01-Ladyfingers-Lofi',

        init() {
            this.syncState();
            window.addEventListener('sanctuary-audio-state', (e) => {
                if (e.detail) {
                    this.isPlaying = !!e.detail.isPlaying;
                    this.isLooping = !!e.detail.isLooping;
                    if (e.detail.track && e.detail.track.title) {
                        this.trackTitle = e.detail.track.title;
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
                this.isExpanded = false; // Start compressed
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
                const cur = typeof window.SanctuaryAudio.getCurrentTrack === 'function' ? window.SanctuaryAudio.getCurrentTrack() : null;
                if (cur && cur.title) {
                    this.trackTitle = cur.title;
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

        togglePlay() {
            if (window.SanctuaryAudio && typeof window.SanctuaryAudio.toggle === 'function') {
                window.SanctuaryAudio.toggle();
            }
        },

        nextTrack() {
            if (window.SanctuaryAudio && typeof window.SanctuaryAudio.next === 'function') {
                window.SanctuaryAudio.next();
                const cur = typeof window.SanctuaryAudio.getCurrentTrack === 'function' ? window.SanctuaryAudio.getCurrentTrack() : null;
                if (cur && cur.title) {
                    this.trackTitle = cur.title;
                }
                this.isPlaying = true;
            }
        },

        prevTrack() {
            if (window.SanctuaryAudio && typeof window.SanctuaryAudio.prev === 'function') {
                window.SanctuaryAudio.prev();
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
            <!-- Left: Spinning Vinyl Disc & Track Name -->
            <div 
                class="flex items-center gap-2 min-w-0"
                :class="document.body.classList.contains('sidebar-collapsed') ? 'w-full justify-center' : ''"
            >
                <span 
                    class="w-5 h-5 flex items-center justify-center shrink-0 sidebar-icon text-white transition-transform duration-300"
                    :class="isPlaying ? 'animate-[spin_4s_linear_infinite]' : ''"
                    title="Ambient Music"
                >
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 14.5c-2.49 0-4.5-2.01-4.5-4.5S9.51 7.5 12 7.5s4.5 2.01 4.5 4.5-2.01 4.5-4.5 4.5zm0-5.5c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1z"/>
                    </svg>
                </span>
                <span class="sidebar-label text-sm font-medium text-white truncate" x-text="trackTitle">01-Ladyfingers-Lofi</span>
            </div>

            <!-- Right: Animated Soundwaves, Chevron Toggle & Close/Stop Button -->
            <div class="sidebar-label flex items-center gap-1.5 shrink-0">
                <!-- Animated Soundwaves -->
                <div x-show="isPlaying" class="flex items-center gap-[2px] h-3 px-1 mr-0.5">
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

        <!-- Bottom Row: Smoothly Morphing Collapsible Controls Container -->
        <div 
            class="grid transition-all duration-300 ease-in-out w-full overflow-hidden"
            :class="isExpanded ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0 pointer-events-none'"
        >
            <div class="overflow-hidden min-h-0 w-full">
                <div class="pt-2 mt-2 border-t border-white/10 flex items-center justify-between px-1.5 w-full">
                    <!-- Previous Track < -->
                    <button 
                        type="button" 
                        @click.stop="prevTrack()" 
                        class="flex items-center justify-center w-7 h-7 rounded-md text-white/70 hover:text-white hover:bg-white/10 transition cursor-pointer"
                        title="Previous Track"
                    >
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                            <path d="M6 6h2v12H6zm3.5 6l8.5 6V6z"/>
                        </svg>
                    </button>

                    <!-- Play / Pause -->
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

                    <!-- Next Track > -->
                    <button 
                        type="button" 
                        @click.stop="nextTrack()" 
                        class="flex items-center justify-center w-7 h-7 rounded-md text-white/70 hover:text-white hover:bg-white/10 transition cursor-pointer"
                        title="Next Track"
                    >
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                            <path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/>
                        </svg>
                    </button>

                    <!-- Loop Toggle (Blue when active, ample space, no clipping) -->
                    <button 
                        type="button" 
                        @click.stop="toggleLoop()" 
                        class="flex items-center justify-center w-7 h-7 rounded-md transition-all cursor-pointer relative"
                        :class="isLooping 
                            ? 'text-blue-400 bg-blue-500/20 border border-blue-400/40 shadow-[0_0_8px_rgba(59,130,246,0.3)]' 
                            : 'text-white/40 hover:text-white hover:bg-white/10 border border-transparent'"
                        :title="isLooping ? 'Loop: ON (Repeating current track)' : 'Loop: OFF (Auto-advancing to next track)'"
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
