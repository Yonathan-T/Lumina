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
