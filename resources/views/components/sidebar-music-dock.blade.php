<div 
    id="sidebar-music-dock" 
    x-data="{
        isPlaying: false,
        isSlotOpen: false,
        isDocked: false,

        init() {
            this.syncState();
            window.addEventListener('sanctuary-audio-state', (e) => {
                this.isPlaying = !!(e.detail && e.detail.isPlaying);
                // When audio stops or pauses, vanish immediately!
                if (!this.isPlaying) {
                    this.isSlotOpen = false;
                    this.isDocked = false;
                    window.isMusicFlying = false;
                    try { sessionStorage.removeItem('sanctuary_docked'); } catch(e){}
                }
            });
            window.addEventListener('music-prepare-dock', () => {
                // Smoothly open the docking slot in advance so the incoming card can land and morph into place
                this.isSlotOpen = true;
            });
            window.addEventListener('music-docked', () => {
                // The morph is complete! Reveal the real interactive dock card
                window.isMusicFlying = false;
                this.isSlotOpen = true;
                this.isDocked = true;
                this.isPlaying = true;
                try { sessionStorage.setItem('sanctuary_docked', '1'); } catch(e){}
                this.$nextTick(() => {
                    const btn = document.getElementById('sidebar-music-dock-card');
                    if (btn && document.body.classList.contains('sidebar-collapsed')) {
                        btn.title = btn.getAttribute('data-title') || 'Stop Ambient Music';
                    }
                });
            });
            document.addEventListener('livewire:navigated', () => {
                this.syncState();
            });
        },

        syncState() {
            if (window.SanctuaryAudio) {
                this.isPlaying = window.SanctuaryAudio.getIsPlaying();
            }
            // Visible on sidebar ONLY if music is playing AND already docked AND not currently mid-jump!
            if (!window.isMusicFlying && this.isPlaying && sessionStorage.getItem('sanctuary_docked') === '1') {
                this.isSlotOpen = true;
                this.isDocked = true;
            } else if (!this.isPlaying) {
                this.isSlotOpen = false;
                this.isDocked = false;
                try { sessionStorage.removeItem('sanctuary_docked'); } catch(e){}
            }
        },

        stopMusic() {
            if (window.SanctuaryAudio) {
                window.SanctuaryAudio.pause();
            }
            this.isPlaying = false;
            this.isSlotOpen = false;
            this.isDocked = false;
            window.isMusicFlying = false;
            try { sessionStorage.removeItem('sanctuary_docked'); } catch(e){}
        }
    }"
    class="w-full transition-all duration-300 ease-out"
    :class="isSlotOpen ? 'max-h-16 mb-2 overflow-hidden' : 'max-h-0 m-0 overflow-hidden'"
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

    <button 
        id="sidebar-music-dock-card"
        type="button"
        @click="stopMusic()"
        class="group relative flex items-center justify-between w-full h-10 px-4 py-2 rounded-md border border-white/10 hover:bg-blue-300/15 text-white select-none transition-colors duration-150 cursor-pointer focus:outline-none"
        :class="isDocked ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'"
        data-title="Ambient Music (Sanctuary) - Click to Stop"
    >
        <!-- Left: Spinning Vinyl Disc & Track Name -->
        <div class="flex items-center gap-2 min-w-0">
            <span 
                class="w-5 h-5 flex items-center justify-center shrink-0 sidebar-icon transition-transform duration-300 animate-[spin_4s_linear_infinite] text-white"
            >
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 14.5c-2.49 0-4.5-2.01-4.5-4.5S9.51 7.5 12 7.5s4.5 2.01 4.5 4.5-2.01 4.5-4.5 4.5zm0-5.5c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1z"/>
                </svg>
            </span>
            <span class="sidebar-label text-sm font-medium text-white truncate">Sanctuary</span>
        </div>

        <!-- Right: Animated Soundwaves & Stop Button -->
        <div class="sidebar-label flex items-center gap-2.5 shrink-0">
            <!-- Animated Soundwaves -->
            <div class="flex items-center gap-[2px] h-3.5 px-1">
                <span class="w-[2px] rounded-full bg-white/80 skiper-gold-bar-1 inline-block"></span>
                <span class="w-[2px] rounded-full bg-white/80 skiper-gold-bar-2 inline-block"></span>
                <span class="w-[2px] rounded-full bg-white/80 skiper-gold-bar-3 inline-block"></span>
                <span class="w-[2px] rounded-full bg-white/80 skiper-gold-bar-4 inline-block"></span>
                <span class="w-[2px] rounded-full bg-white/80 skiper-gold-bar-5 inline-block"></span>
            </div>

            <!-- Stop Button Icon -->
            <span 
                class="p-1 rounded text-white/70 group-hover:text-white group-hover:bg-white/10 transition"
                title="Stop Ambient Music"
            >
                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                    <path d="M6 6h12v12H6z"/>
                </svg>
            </span>
        </div>
    </button>
</div>
