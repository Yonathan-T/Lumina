{{--
    Skiper 95: Vertical Scroll Progress Indicator with Animated Percentage & Tick Ruler
    Ref: https://skiper-ui.com/v1/skiper95
--}}
<style>
    /* Suppress default native scrollbars wherever custom Skiper 95 indicator is used */
    html, body, main, .overflow-y-auto {
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }
    html::-webkit-scrollbar,
    body::-webkit-scrollbar,
    main::-webkit-scrollbar,
    .overflow-y-auto::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
        background: transparent !important;
    }
</style>

<div x-data="{
    percent: 0,
    hasScroll: false,
    scrollTarget: null,
    updateProgress() {
        const doc = document.documentElement;
        const mainEl = document.querySelector('main.overflow-y-auto') || document.querySelector('main');
        
        let scrollTop = 0;
        let scrollHeight = 0;
        let clientHeight = 0;

        if (mainEl && mainEl.scrollHeight > mainEl.clientHeight + 40) {
            this.scrollTarget = mainEl;
            scrollTop = mainEl.scrollTop;
            scrollHeight = mainEl.scrollHeight;
            clientHeight = mainEl.clientHeight;
        } else {
            this.scrollTarget = window;
            scrollTop = window.scrollY || doc.scrollTop || 0;
            scrollHeight = doc.scrollHeight || document.body.scrollHeight || 0;
            clientHeight = window.innerHeight || 0;
        }

        const maxScroll = Math.max(0, scrollHeight - clientHeight);
        this.hasScroll = maxScroll > 60;

        if (maxScroll > 0) {
            this.percent = Math.min(100, Math.max(0, (scrollTop / maxScroll) * 100));
        } else {
            this.percent = 0;
        }
    },
    jumpTo(event) {
        const gauge = this.$refs.rulerTrack;
        if (!gauge) return;
        const rect = gauge.getBoundingClientRect();
        const clickY = event.clientY - rect.top;
        const ratio = Math.max(0, Math.min(1, clickY / rect.height));

        if (window.__luminaLenis && (!this.scrollTarget || this.scrollTarget === window)) {
            const doc = document.documentElement;
            const maxScroll = (doc.scrollHeight || document.body.scrollHeight) - window.innerHeight;
            window.__luminaLenis.scrollTo(ratio * maxScroll, { duration: 1.2 });
        } else if (this.scrollTarget && this.scrollTarget !== window) {
            const maxScroll = this.scrollTarget.scrollHeight - this.scrollTarget.clientHeight;
            this.scrollTarget.scrollTo({ top: ratio * maxScroll, behavior: 'smooth' });
        } else {
            const doc = document.documentElement;
            const maxScroll = (doc.scrollHeight || document.body.scrollHeight) - window.innerHeight;
            window.scrollTo({ top: ratio * maxScroll, behavior: 'smooth' });
        }
    },
    init() {
        this.updateProgress();

        const onScroll = () => {
            window.requestAnimationFrame(() => this.updateProgress());
        };

        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll, { passive: true });

        const mainEl = document.querySelector('main.overflow-y-auto') || document.querySelector('main');
        if (mainEl) {
            mainEl.addEventListener('scroll', onScroll, { passive: true });
        }

        // Periodic check to adjust if dynamic content expands
        const interval = setInterval(() => this.updateProgress(), 1000);

        this.$cleanup = () => {
            window.removeEventListener('scroll', onScroll);
            window.removeEventListener('resize', onScroll);
            if (mainEl) mainEl.removeEventListener('scroll', onScroll);
            clearInterval(interval);
        };
    }
}"
x-show="hasScroll"
x-transition:enter="transition ease-out duration-300"
x-transition:enter-start="opacity-0 translate-x-4"
x-transition:enter-end="opacity-100 translate-x-0"
x-transition:leave="transition ease-in duration-200"
x-transition:leave-start="opacity-100 translate-x-0"
x-transition:leave-end="opacity-0 translate-x-4"
class="fixed right-3 sm:right-6 top-1/2 -translate-y-1/2 z-40 select-none flex flex-col items-center justify-center cursor-pointer group"
style="display: none;"
x-cloak
title="Scroll Progress"
@click="jumpTo($event)"
>
    <!-- Ruler Gauge Container (192px / h-48) -->
    <div x-ref="rulerTrack" class="relative h-48 w-4 flex items-center justify-center">
        <!-- Inactive Repeating Tick Marks -->
        <div 
            class="absolute inset-0 pointer-events-none"
            style="background-image: repeating-linear-gradient(to bottom, rgba(255, 255, 255, 0.22) 0, rgba(255, 255, 255, 0.22) 1px, transparent 1px, transparent 5px);"
        ></div>

        <!-- Active Filled Repeating Tick Marks via Clip-Path -->
        <div 
            class="absolute inset-0 pointer-events-none transition-[clip-path] duration-75"
            :style="`clip-path: inset(0 0 ${Math.max(0, 100 - percent)}% 0); background-image: repeating-linear-gradient(to bottom, #ffffff 0, #ffffff 1px, transparent 1px, transparent 5px);`"
        ></div>

        <!-- Sliding Horizontal Indicator Tick & Percentage Tag -->
        <div 
            class="absolute left-0 top-0 flex h-px w-8 items-center pointer-events-none bg-white shadow-[0_0_8px_rgba(255,255,255,0.9)] transition-transform duration-75 ease-out"
            :style="`transform: translateY(${ (percent / 100) * 191 }px);`"
        >
            <span 
                class="absolute -right-2 translate-x-full tabular-nums text-[10px] font-mono font-medium text-white/90 bg-[#070e1c]/90 px-1.5 py-0.5 rounded border border-white/20 shadow-md backdrop-blur-md"
                x-text="Math.round(percent) + '%'"
            ></span>
        </div>
    </div>
</div>
