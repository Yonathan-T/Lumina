{{--
    Dashboard Compact Music Button with Trajectory Jumping Animation to Sidebar
    Theme: #c2b68e warm gold & dark sidebar-gradient
    When clicked to PLAY: Leaps off the dashboard, hops in a high arc OVER the KPI cards,
    bounces off the UI, vaults OVER the AI action buttons, and docks right into the sidebar above Manage Plan!
--}}
<div 
    id="skiper25-music-player"
    x-data="{
        isPlaying: false,
        isDocked: false,

        init() {
            this.syncState();
            window.addEventListener('sanctuary-audio-state', (e) => {
                this.isPlaying = !!(e.detail && e.detail.isPlaying);
                if (!this.isPlaying) {
                    this.isDocked = false;
                }
            });
            window.addEventListener('music-docked', () => {
                this.isDocked = true;
            });
            window.addEventListener('focus', () => {
                this.syncState();
            });
            document.addEventListener('livewire:navigated', () => {
                this.syncState();
            });
        },

        syncState() {
            if (window.SanctuaryAudio) {
                this.isPlaying = window.SanctuaryAudio.getIsPlaying();
            }
            this.isDocked = Boolean(this.isPlaying || window.isMusicFlying);
        },

        handleClick() {
            if (!this.isPlaying) {
                // Immediately vanish header button as jumper takes flight!
                this.isDocked = true;
                window.isMusicFlying = true;
                try { sessionStorage.removeItem('sanctuary_docked'); } catch(e){}

                if (window.triggerMusicJumpToSidebar) {
                    window.triggerMusicJumpToSidebar(() => {
                        window.isMusicFlying = false;
                        this.isDocked = true;
                    });
                }
                if (window.SanctuaryAudio) {
                    window.SanctuaryAudio.play();
                }
            } else {
                if (window.SanctuaryAudio) {
                    window.SanctuaryAudio.pause();
                }
                this.isDocked = false;
                window.isMusicFlying = false;
                try { sessionStorage.removeItem('sanctuary_docked'); } catch(e){}
            }
        }
    }" 
    class="relative inline-flex items-center select-none"
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

    <!-- Music Button on Dashboard -->
    <button 
        id="skiper25-music-player-btn"
        type="button"
        @click="handleClick()"
        :title="isPlaying ? 'Pause Ambient Music' : 'Play Ambient Music (Jump to Sidebar)'"
        class="group relative flex items-center cursor-pointer transition-all duration-300 overflow-hidden shadow-sm"
        :class="isDocked 
            ? 'opacity-0 scale-50 pointer-events-none w-0 h-0 p-0 border-0 m-0' 
            : (isPlaying 
                ? 'h-10 w-28 px-3 rounded-md justify-between border border-white/10 hover:bg-blue-300/15 text-white' 
                : 'h-10 w-10 rounded-md justify-center border border-white/10 hover:bg-blue-300/15 text-white/80 hover:text-white')"
    >
        <!-- Vinyl Disc Icon -->
        <span 
            class="flex items-center justify-center shrink-0 w-4 h-4 transition-transform duration-300"
            :class="isPlaying ? 'animate-[spin_4s_linear_infinite] text-white' : 'text-white/80 group-hover:scale-110'"
        >
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 14.5c-2.49 0-4.5-2.01-4.5-4.5S9.51 7.5 12 7.5s4.5 2.01 4.5 4.5-2.01 4.5-4.5 4.5zm0-5.5c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1z"/>
            </svg>
        </span>

        <!-- Soundwaves when playing -->
        <div 
            x-show="isPlaying && !isDocked" 
            class="flex items-center gap-[2.5px] h-4 px-1 ml-auto"
        >
            <span class="w-[2px] rounded-full bg-white/80 skiper-gold-bar-1 inline-block"></span>
            <span class="w-[2px] rounded-full bg-white/80 skiper-gold-bar-2 inline-block"></span>
            <span class="w-[2px] rounded-full bg-white/80 skiper-gold-bar-3 inline-block"></span>
            <span class="w-[2px] rounded-full bg-white/80 skiper-gold-bar-4 inline-block"></span>
            <span class="w-[2px] rounded-full bg-white/80 skiper-gold-bar-5 inline-block"></span>
        </div>
    </button>
</div>
