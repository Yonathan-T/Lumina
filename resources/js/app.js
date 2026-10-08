import './bootstrap';
import Chart from 'chart.js/auto';

// Make Chart.js globally available
window.Chart = Chart;

import.meta.glob([
    '../images/**'
]);
// const toggleBtn = document.getElementById('toggleSidebar');
// const sidebar = document.getElementById('sidebar');
// const mainContent = document.getElementById('mainContent');

// let isSidebarVisible = true;

// toggleBtn.addEventListener('click', () => {
//   isSidebarVisible = !isSidebarVisible;

//   if (isSidebarVisible) {
//     sidebar.classList.remove('-translate-x-full');
//     mainContent.classList.remove('pl-0');
//     mainContent.classList.add('pl-[270px]');
//   } else {
//     sidebar.classList.add('-translate-x-full');
//     mainContent.classList.remove('pl-[270px]');
//     mainContent.classList.add('pl-0');
//   }
// });
// public/js/search.js
class SearchModal {
    constructor(searchUrl, options = {}) {
        this.searchUrl = searchUrl;
        this.options = {
            debounceMs: 300,
            minQueryLength: 2,
            ...options
        };
        
        this.modal = document.getElementById('searchModal');
        this.input = document.getElementById('searchInput');
        this.trigger = document.getElementById('searchTrigger');
        this.results = document.getElementById('searchResults');
        this.emptyState = document.getElementById('emptyState');
        this.noResults = document.getElementById('noResults');
        
        this.debounceTimer = null;
        this.currentQuery = '';
        
        this.init();
    }
    
    init() {
        // Trigger button click
        this.trigger?.addEventListener('click', () => this.open());
        
        // Keyboard shortcut (Ctrl+K / Cmd+K)
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.open();
            }
        });
        
        // Modal events
        this.modal?.addEventListener('click', (e) => {
            if (e.target === this.modal) this.close();
        });
        
        // Input events
        this.input?.addEventListener('input', (e) => {
            this.handleSearch(e.target.value);
        });
        
        // Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) {
                this.close();
            }
        });
    }
    
    open() {
        this.modal?.classList.remove('hidden');
        this.modal?.classList.add('flex');
        this.input?.focus();
        document.body.style.overflow = 'hidden';
    }
    
    close() {
        this.modal?.classList.add('hidden');
        this.modal?.classList.remove('flex');
        this.input.value = '';
        this.currentQuery = '';
        this.showEmptyState();
        document.body.style.overflow = '';
    }
    
    isOpen() {
        return !this.modal?.classList.contains('hidden');
    }
    
    handleSearch(query) {
        clearTimeout(this.debounceTimer);
        
        if (query.length < this.options.minQueryLength) {
            this.showEmptyState();
            return;
        }
        
        this.debounceTimer = setTimeout(() => {
            this.performSearch(query);
        }, this.options.debounceMs);
    }
    
    async performSearch(query) {
        if (query === this.currentQuery) return;
        this.currentQuery = query;
        
        try {
            const response = await fetch(`${this.searchUrl}?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });
            
            const data = await response.json();
            this.displayResults(data.results || []);
        } catch (error) {
            console.error('Search error:', error);
            this.showNoResults();
        }
    }
    
    displayResults(results) {
        if (results.length === 0) {
            this.showNoResults();
            return;
        }
        
        this.hideStates();
        this.results.classList.remove('hidden');
        
        this.results.innerHTML = results.map(result => this.renderResult(result)).join('');
        
        // Add click handlers
        this.results.querySelectorAll('[data-result-id]').forEach(element => {
            element.addEventListener('click', () => {
                const url = element.dataset.url;
                if (url) {
                    window.location.href = url;
                }
                this.close();
            });
        });
    }
    
    renderResult(result) {
        const tags = result.tags?.map(tag => 
            `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-secondary text-secondary-foreground">#${tag}</span>`
        ).join('') || '';
        
        return `
            <div 
                class="p-3 rounded-lg hover:bg-accent/50 cursor-pointer transition-colors border border-transparent hover:[background-color:rgb(29,40,58)]"
                data-result-id="${result.id}"
                data-url="${result.url || ''}"
            >
                <div class="flex items-start justify-between mb-1">
                    <h3 class="font-medium text-sm">${this.escapeHtml(result.title)}</h3>
                    <span class="text-xs text-muted-foreground whitespace-nowrap ml-2">${this.escapeHtml(result.date)}</span>
                </div>
                ${result.preview ? `<p class="text-sm text-muted-foreground line-clamp-2 mb-2">${this.escapeHtml(result.preview)}</p>` : ''}
                <div class="flex flex-wrap gap-1">
                    ${tags}
                </div>
            </div>
        `;
    }
    
    showEmptyState() {
        this.hideStates();
        this.emptyState.classList.remove('hidden');
    }
    
    showNoResults() {
        this.hideStates();
        this.noResults.classList.remove('hidden');
    }
    
    hideStates() {
        this.emptyState.classList.add('hidden');
        this.noResults.classList.add('hidden');
        this.results.classList.add('hidden');
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

function initSearchModal() {
    const searchModal = document.getElementById('searchModal');

    if (!searchModal || searchModal.dataset.initialized === 'true') {
        return;
    }

    searchModal.dataset.initialized = 'true';
    new SearchModal('/search');
}



self.addEventListener('push', event => {
    const data = event.data.json();
    self.registration.showNotification(data.title, {
        body: data.body,
        icon: data.icon,
        data: { url: data.data.url }
    });
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    event.waitUntil(clients.openWindow(event.notification.data.url));
});

function initProfileMenu() {
    const profileButton = document.getElementById('profileButton');
    const profileMenu = document.getElementById('profileMenu');

    if (!profileButton || !profileMenu || profileButton.dataset.initialized === 'true') {
        return;
    }

    profileButton.dataset.initialized = 'true';

    profileButton.addEventListener('click', () => {
        profileMenu.classList.toggle('hidden');
    });
}

document.addEventListener('click', (event) => {
    const profileButton = document.getElementById('profileButton');
    const profileMenu = document.getElementById('profileMenu');

    if (!profileButton || !profileMenu) {
        return;
    }

    if (!profileButton.contains(event.target) && !profileMenu.contains(event.target)) {
        profileMenu.classList.add('hidden');
    }
});



// Error message handler
// Make dismissMessage globally accessible
window.dismissMessage = function(messageId) {
    const message = document.getElementById(messageId);
    if (message) {
        message.style.opacity = '0';
        message.style.transform = 'translateY(-10px)';
        message.style.transition = 'all 0.3s ease-out';

        setTimeout(() => {
            message.remove();
        }, 300);
    }
};

function initFlashMessages() {
    const successMessage = document.getElementById('success-message');
    const errorMessage = document.getElementById('error-message');

    if (successMessage && !successMessage.dataset.autoDismissed) {
        successMessage.dataset.autoDismissed = 'true';
        setTimeout(() => {
            dismissMessage('success-message');
        }, 5000);
    }

    if (errorMessage && !errorMessage.dataset.autoDismissed) {
        errorMessage.dataset.autoDismissed = 'true';
        setTimeout(() => {
            dismissMessage('error-message');
        }, 5000);
    }
}

function initPageUi() {
    initSearchModal();
    initProfileMenu();
    initFlashMessages();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPageUi, { once: true });
} else {
    initPageUi();
}

// Also handle Livewire updates - messages might be added dynamically
document.addEventListener('livewire:init', () => {
    Livewire.hook('message.processed', (message, component) => {
        setTimeout(initFlashMessages, 100);
    });
});

document.addEventListener('livewire:navigated', initPageUi);

/**
 * Dashboard Ambient Music Trajectory Jumping Animation
 * Launches from dashboard header, vaults over KPI cards and action buttons,
 * and docks directly into the sidebar above the Manage Plan button.
 */
window.triggerMusicJumpToSidebar = function (onComplete) {
    window.isMusicFlying = true;
    try { sessionStorage.removeItem('sanctuary_docked'); } catch(e){}

    const originBtn = document.getElementById('skiper25-music-player-btn');
    if (!originBtn) {
        window.isMusicFlying = false;
        if (onComplete) onComplete();
        return;
    }

    const managePlanBtn = document.getElementById('sidebar-manage-plan-btn') 
        || document.querySelector('#sidebar [href*="subscription"]')
        || document.querySelector('#sidebar .p-4 a');
    const dockSlot = document.getElementById('sidebar-music-dock-slot');
    const dockCard = document.getElementById('sidebar-music-dock-card');

    const fromRect = originBtn.getBoundingClientRect();
    let endX, endY;

    if (dockCard && dockCard.getBoundingClientRect().width > 0) {
        const cRect = dockCard.getBoundingClientRect();
        endX = cRect.left + cRect.width / 2;
        endY = cRect.top + cRect.height / 2;
    } else if (dockSlot) {
        const slotRect = dockSlot.getBoundingClientRect();
        endX = slotRect.left + slotRect.width / 2;
        endY = slotRect.top + 20;
    } else if (managePlanBtn) {
        const planRect = managePlanBtn.getBoundingClientRect();
        endX = planRect.left + planRect.width / 2;
        endY = planRect.top - 24;
    } else {
        endX = 120;
        endY = window.innerHeight - 100;
    }

    const kpiEl = document.getElementById('dashboard-kpis-grid') || document.querySelector('.grid.gap-4.md\\:grid-cols-2');
    const actionsEl = document.getElementById('dashboard-action-buttons') || document.querySelector('.mt-12.space-y-4');

    const kpiRect = kpiEl ? kpiEl.getBoundingClientRect() : null;
    const actionsRect = actionsEl ? actionsEl.getBoundingClientRect() : null;

    const startX = fromRect.left + fromRect.width / 2;
    const startY = fromRect.top + fromRect.height / 2;

    // Hop 1 Apex (Soaring high OVER the KPI cards!)
    const hop1ApexX = startX - (startX - endX) * 0.35;
    const hop1ApexY = kpiRect ? Math.max(15, kpiRect.top - 75) : startY - 80;

    // Bounce 1 (Touching down between KPIs and Action Buttons)
    const bounce1X = startX - (startX - endX) * 0.62;
    const bounce1Y = (kpiRect && actionsRect) 
        ? (kpiRect.bottom + actionsRect.top) / 2 
        : (startY + (endY - startY) * 0.45);

    // Hop 2 Apex (Vaulting high OVER the Action Buttons!)
    const hop2ApexX = startX - (startX - endX) * 0.83;
    const hop2ApexY = actionsRect 
        ? Math.max(bounce1Y - 85, actionsRect.top - 65) 
        : bounce1Y - 75;

    // Create Flying Jumper Card matching manage plan styling
    const jumper = document.createElement('div');
    jumper.id = 'music-flying-jumper';
    jumper.style.position = 'fixed';
    jumper.style.zIndex = '99999';
    jumper.style.pointerEvents = 'none';
    jumper.style.left = '0px';
    jumper.style.top = '0px';
    jumper.style.width = '112px';
    jumper.style.height = '36px';
    jumper.style.boxSizing = 'border-box';
    jumper.style.transformOrigin = 'center center';
    jumper.style.willChange = 'transform, width, height, opacity';
    jumper.className = 'flex items-center justify-between px-3 py-1.5 rounded-md border border-white/15 bg-[#0f111a]/95 text-white shadow-[0_12px_36px_rgba(0,0,0,0.9),0_0_15px_rgba(255,255,255,0.08)] backdrop-blur-md select-none overflow-hidden';

    jumper.innerHTML = `
        <div class="flex items-center gap-2 shrink-0">
            <span class="w-5 h-5 flex items-center justify-center shrink-0 animate-[spin_4s_linear_infinite] text-white">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 14.5c-2.49 0-4.5-2.01-4.5-4.5S9.51 7.5 12 7.5s4.5 2.01 4.5 4.5-2.01 4.5-4.5 4.5zm0-5.5c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1z"/>
                </svg>
            </span>
            <span class="jumper-label text-sm font-medium text-white whitespace-nowrap">Sanctuary</span>
        </div>
        <div class="jumper-label flex items-center gap-2 ml-auto">
            <div class="flex items-center gap-[2px] h-3.5 px-1">
                <span class="w-[2px] rounded-full bg-white/80 skiper-gold-bar-1 inline-block"></span>
                <span class="w-[2px] rounded-full bg-white/80 skiper-gold-bar-2 inline-block"></span>
                <span class="w-[2px] rounded-full bg-white/80 skiper-gold-bar-3 inline-block"></span>
                <span class="w-[2px] rounded-full bg-white/80 skiper-gold-bar-4 inline-block"></span>
                <span class="w-[2px] rounded-full bg-white/80 skiper-gold-bar-5 inline-block"></span>
            </div>
            <div class="jumper-stop-btn opacity-0 transition-opacity duration-200 p-1 text-white/70">
                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                    <path d="M6 6h12v12H6z"/>
                </svg>
            </div>
        </div>
    `;

    document.body.appendChild(jumper);

    // Particles Array (musical notes & stars) with clean white glow
    const particles = ['♪', '♫', '✦', '★', '•'];
    let lastParticleTime = 0;

    function spawnParticle(x, y) {
        const p = document.createElement('div');
        const symbol = particles[Math.floor(Math.random() * particles.length)];
        p.innerText = symbol;
        p.style.position = 'fixed';
        p.style.zIndex = '99998';
        p.style.pointerEvents = 'none';
        p.style.left = (x + (Math.random() * 16 - 8)) + 'px';
        p.style.top = (y + (Math.random() * 16 - 8)) + 'px';
        p.style.color = '#ffffff';
        p.style.fontSize = (Math.random() * 6 + 12) + 'px';
        p.style.fontWeight = 'bold';
        p.style.textShadow = '0 0 10px rgba(255, 255, 255, 0.9)';
        p.style.transition = 'transform 0.6s ease-out, opacity 0.6s ease-out';
        p.style.transform = 'translate(-50%, -50%) scale(0.6)';
        p.style.opacity = '1';
        document.body.appendChild(p);

        requestAnimationFrame(() => {
            p.style.transform = `translate(-50%, -50%) translate(${(Math.random() - 0.5) * 50}px, ${-30 - Math.random() * 30}px) scale(1.35)`;
            p.style.opacity = '0';
        });
        setTimeout(() => p.remove(), 620);
    }

    function spawnRipple(x, y) {
        const ring = document.createElement('div');
        ring.style.position = 'fixed';
        ring.style.zIndex = '99997';
        ring.style.pointerEvents = 'none';
        ring.style.left = x + 'px';
        ring.style.top = y + 'px';
        ring.style.width = '28px';
        ring.style.height = '28px';
        ring.style.borderRadius = '9999px';
        ring.style.border = '2px solid rgba(255, 255, 255, 0.8)';
        ring.style.boxShadow = '0 0 16px rgba(255, 255, 255, 0.4)';
        ring.style.transform = 'translate(-50%, -50%) scale(0.3)';
        ring.style.opacity = '1';
        ring.style.transition = 'transform 0.55s ease-out, opacity 0.55s ease-out';
        document.body.appendChild(ring);

        requestAnimationFrame(() => {
            ring.style.transform = 'translate(-50%, -50%) scale(3.2)';
            ring.style.opacity = '0';
        });
        setTimeout(() => ring.remove(), 580);
    }

    // Trajectory timing (~1.38s total flight + 0.23s morph)
    const hop1Duration = 620;
    const bouncePause = 60;
    const hop2Duration = 700;
    const totalDuration = hop1Duration + bouncePause + hop2Duration;

    const startTime = performance.now();

    function bezier2(t, p0, p1, p2) {
        const inv = 1 - t;
        return inv * inv * p0 + 2 * inv * t * p1 + t * t * p2;
    }

    let bounced = false;
    let dockPrepared = false;
    let morphStarted = false;

    function frame(now) {
        if (morphStarted) return;
        const elapsed = now - startTime;

        if (elapsed < hop1Duration) {
            // HOP 1: Flying high over the KPIs!
            const t = elapsed / hop1Duration;
            const easeT = t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;

            const curX = bezier2(easeT, startX, hop1ApexX, bounce1X);
            const curY = bezier2(easeT, startY, hop1ApexY, bounce1Y);

            const rot = (easeT < 0.5) ? -16 * (1 - easeT * 2) : 14 * ((easeT - 0.5) * 2);
            const scale = 1 + Math.sin(t * Math.PI) * 0.18;

            jumper.style.transform = `translate(${curX - 56}px, ${curY - 18}px) rotate(${rot}deg) scale(${scale})`;

            if (now - lastParticleTime > 40) {
                spawnParticle(curX, curY);
                lastParticleTime = now;
            }

            requestAnimationFrame(frame);
        } else if (elapsed < hop1Duration + bouncePause) {
            // BOUNCE 1 CONTACT: Tap the surface between KPIs and buttons!
            if (!bounced) {
                bounced = true;
                spawnRipple(bounce1X, bounce1Y);
                for (let i = 0; i < 6; i++) spawnParticle(bounce1X, bounce1Y);
            }
            jumper.style.transform = `translate(${bounce1X - 56}px, ${bounce1Y - 18}px) scale(1.24, 0.76)`;
            requestAnimationFrame(frame);
        } else if (elapsed < totalDuration) {
            // HOP 2: Vaulting over the Action Buttons straight into the Sidebar!
            const t = (elapsed - (hop1Duration + bouncePause)) / hop2Duration;
            const easeT = t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;

            // Trigger dock slot expansion as the card vaults towards the sidebar
            if (t >= 0.1 && !dockPrepared) {
                dockPrepared = true;
                window.dispatchEvent(new CustomEvent('music-prepare-dock'));
            }

            // Dynamically lock onto the exact center of sidebar-music-dock-card
            const currentCard = document.getElementById('sidebar-music-dock-card');
            if (currentCard) {
                const cRect = currentCard.getBoundingClientRect();
                if (cRect.width > 0 && cRect.height > 0) {
                    endX = cRect.left + cRect.width / 2;
                    endY = cRect.top + cRect.height / 2;
                }
            }

            const curX = bezier2(easeT, bounce1X, hop2ApexX, endX);
            const curY = bezier2(easeT, bounce1Y, hop2ApexY, endY);

            const rot = (easeT < 0.5) ? -22 * (1 - easeT * 2) : -4 * (1 - easeT);
            const scale = 1 + Math.sin(t * Math.PI) * 0.14;

            jumper.style.transform = `translate(${curX - 56}px, ${curY - 18}px) rotate(${rot}deg) scale(${scale})`;

            if (now - lastParticleTime > 40) {
                spawnParticle(curX, curY);
                lastParticleTime = now;
            }

            requestAnimationFrame(frame);
        } else {
            // TOUCHDOWN & MORPH INTO THE DOCK!
            morphStarted = true;

            const targetCard = document.getElementById('sidebar-music-dock-card');
            const targetRect = targetCard ? targetCard.getBoundingClientRect() : null;

            if (targetRect && targetRect.width > 0) {
                const isSidebarCollapsed = document.body.classList.contains('sidebar-collapsed');
                const targetLeft = targetRect.left;
                const targetTop = targetRect.top;
                const targetWidth = targetRect.width;
                const targetHeight = targetRect.height;

                // Center touchdown point
                const landCenterX = targetLeft + targetWidth / 2;
                const landCenterY = targetTop + targetHeight / 2;

                spawnRipple(landCenterX, landCenterY);
                for (let i = 0; i < 6; i++) spawnParticle(landCenterX, landCenterY);

                // Set unrotated position at touchdown point
                jumper.style.transform = `translate(${landCenterX - 56}px, ${landCenterY - 18}px) scale(1) rotate(0deg)`;

                // Force reflow
                void jumper.offsetWidth;

                // MORPH: smoothly expand or shrink to own its place in the sidebar!
                jumper.style.transition = 'all 0.22s cubic-bezier(0.16, 1, 0.3, 1)';
                jumper.style.transform = `translate(${targetLeft}px, ${targetTop}px) scale(1) rotate(0deg)`;
                jumper.style.width = targetWidth + 'px';
                jumper.style.height = targetHeight + 'px';
                jumper.style.borderRadius = '6px';
                jumper.style.backgroundColor = 'rgba(255, 255, 255, 0.05)';
                jumper.style.borderColor = 'rgba(255, 255, 255, 0.1)';
                jumper.style.boxShadow = 'none';

                if (isSidebarCollapsed) {
                    jumper.style.justifyContent = 'center';
                    jumper.style.padding = '8px';
                    jumper.querySelectorAll('.jumper-label').forEach(el => {
                        el.style.display = 'none';
                    });
                } else {
                    jumper.style.padding = '8px 16px';
                    const stopBtn = jumper.querySelector('.jumper-stop-btn');
                    if (stopBtn) {
                        stopBtn.style.opacity = '0.7';
                    }
                }

                setTimeout(() => {
                    // Docking handoff: reveal real interactive dock, remove jumper
                    window.isMusicFlying = false;
                    try { sessionStorage.setItem('sanctuary_docked', '1'); } catch(e){}
                    window.dispatchEvent(new CustomEvent('music-docked'));
                    if (onComplete) onComplete();

                    jumper.remove();
                }, 230);
            } else {
                window.isMusicFlying = false;
                try { sessionStorage.setItem('sanctuary_docked', '1'); } catch(e){}
                window.dispatchEvent(new CustomEvent('music-docked'));
                if (onComplete) onComplete();
                jumper.remove();
            }
        }
    }

    requestAnimationFrame(frame);
};

