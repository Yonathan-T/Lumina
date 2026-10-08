<div
    class="mt-10 min-h-screen"
    x-data="{
        allBlogs: @js($formattedBlogs),
        hoveredId: null,
        selectedCategory: '',
        selectedSource: '',
        get filteredBlogs() {
            return (this.allBlogs || []).filter(b => {
                const bCat = (b.category || '').trim().toLowerCase();
                const selCat = (this.selectedCategory || '').trim().toLowerCase();
                const catMatch = !selCat || bCat === selCat;

                const bSrc = (b.source_name || '').trim().toLowerCase();
                const selSrc = (this.selectedSource || '').trim().toLowerCase();
                const srcMatch = !selSrc || bSrc === selSrc;

                return catMatch && srcMatch;
            });
        },
        get shelves() {
            const list = this.filteredBlogs;
            const chunks = [];
            for (let i = 0; i < list.length; i += 8) {
                chunks.push(list.slice(i, i + 8));
            }
            return chunks;
        },
        resetFilters() {
            this.selectedCategory = '';
            this.selectedSource = '';
            this.hoveredId = null;
        }
    }"
    x-on:blogs-refreshed.window="allBlogs = $event.detail.blogs"
>
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        {{-- Header Centered --}}
        <div class="mb-8 text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-xs font-medium text-emerald-400 mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Mindfulness & Reflection</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2 font-inter tracking-tight">Mental Health & Journaling Insights</h1>
            <p class="text-gray-400 max-w-2xl mx-auto text-sm sm:text-base">Curated articles presented on an interactive Skiper 52 showcase — hover over any card to reveal the reflection</p>
        </div>

        {{-- Custom Dashboard-Styled Filters --}}
        <div class="flex flex-wrap items-center justify-center mb-10 gap-4 px-2">
            {{-- Category Custom Dropdown --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                <button 
                    type="button" 
                    @click="open = !open"
                    class="flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl border border-white/10 bg-gradient-dark hover:border-white/25 text-white text-xs sm:text-sm font-medium shadow-sm transition-all focus:outline-none min-w-[200px] cursor-pointer group"
                    :class="{ 'border-emerald-500/40 ring-1 ring-emerald-500/30': open || selectedCategory }"
                >
                    <div class="flex items-center gap-2 truncate">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        <span class="truncate" x-text="selectedCategory ? selectedCategory : 'All Categories'">All Categories</span>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0 group-hover:text-white" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {{-- Custom Menu Popover --}}
                <div 
                    x-show="open"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                    class="absolute left-0 mt-2 w-64 rounded-xl border border-white/15 bg-[#0b1220]/95 backdrop-blur-xl shadow-2xl z-50 py-1.5 max-h-64 overflow-y-auto"
                    style="display: none;"
                >
                    <button 
                        type="button"
                        @click="selectedCategory = ''; open = false; hoveredId = null"
                        class="w-full flex items-center justify-between px-3.5 py-2 text-xs sm:text-sm text-left transition-colors cursor-pointer"
                        :class="!selectedCategory ? 'bg-emerald-500/15 text-emerald-300 font-semibold' : 'text-gray-300 hover:bg-white/10 hover:text-white'"
                    >
                        <span>All Categories</span>
                        <template x-if="!selectedCategory">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </template>
                    </button>

                    @foreach($categories as $category)
                        <button 
                            type="button"
                            @click="selectedCategory = @js($category); open = false; hoveredId = null"
                            class="w-full flex items-center justify-between px-3.5 py-2 text-xs sm:text-sm text-left transition-colors cursor-pointer"
                            :class="selectedCategory === @js($category) ? 'bg-emerald-500/15 text-emerald-300 font-semibold' : 'text-gray-300 hover:bg-white/10 hover:text-white'"
                        >
                            <span class="truncate">{{ $category }}</span>
                            <template x-if="selectedCategory === @js($category)">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </template>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Source Custom Dropdown --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                <button 
                    type="button" 
                    @click="open = !open"
                    class="flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl border border-white/10 bg-gradient-dark hover:border-white/25 text-white text-xs sm:text-sm font-medium shadow-sm transition-all focus:outline-none min-w-[200px] cursor-pointer group"
                    :class="{ 'border-emerald-500/40 ring-1 ring-emerald-500/30': open || selectedSource }"
                >
                    <div class="flex items-center gap-2 truncate">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                        <span class="truncate" x-text="selectedSource ? selectedSource : 'All Sources'">All Sources</span>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0 group-hover:text-white" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {{-- Custom Menu Popover --}}
                <div 
                    x-show="open"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                    class="absolute left-0 mt-2 w-64 rounded-xl border border-white/15 bg-[#0b1220]/95 backdrop-blur-xl shadow-2xl z-50 py-1.5 max-h-64 overflow-y-auto"
                    style="display: none;"
                >
                    <button 
                        type="button"
                        @click="selectedSource = ''; open = false; hoveredId = null"
                        class="w-full flex items-center justify-between px-3.5 py-2 text-xs sm:text-sm text-left transition-colors cursor-pointer"
                        :class="!selectedSource ? 'bg-emerald-500/15 text-emerald-300 font-semibold' : 'text-gray-300 hover:bg-white/10 hover:text-white'"
                    >
                        <span>All Sources</span>
                        <template x-if="!selectedSource">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </template>
                    </button>

                    @foreach($sources as $source)
                        <button 
                            type="button"
                            @click="selectedSource = @js($source); open = false; hoveredId = null"
                            class="w-full flex items-center justify-between px-3.5 py-2 text-xs sm:text-sm text-left transition-colors cursor-pointer"
                            :class="selectedSource === @js($source) ? 'bg-emerald-500/15 text-emerald-300 font-semibold' : 'text-gray-300 hover:bg-white/10 hover:text-white'"
                        >
                            <span class="truncate">{{ $source }}</span>
                            <template x-if="selectedSource === @js($source)">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </template>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Matching count badge when filtered --}}
            <span 
                x-show="selectedCategory || selectedSource"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100"
                class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-mono font-medium text-emerald-400 bg-emerald-500/10 border border-emerald-500/20"
                x-text="filteredBlogs.length + ' ' + (filteredBlogs.length === 1 ? 'article' : 'articles')"
            ></span>

            {{-- Reset Active Filters Pill --}}
            <button 
                type="button" 
                x-show="selectedCategory || selectedSource"
                @click="resetFilters()"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium text-gray-400 hover:text-white bg-white/5 hover:bg-white/10 border border-white/10 transition-colors cursor-pointer"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>Clear filters</span>
            </button>
        </div>

        {{-- Dynamic Skiper 52 ExpandOnHover Shelves (Only expands on hover, all equal at rest) --}}
        <div class="relative w-full max-w-6xl mx-auto px-2 sm:px-5 space-y-12">
            @if($isLoading && count($blogs) == 0)
                {{-- Skeleton Loader matching Skiper 52 dimensions --}}
                <div class="flex w-full items-center justify-center gap-2 py-4">
                    @for($i = 0; $i < 8; $i++)
                        <div class="rounded-3xl bg-white/5 border border-white/10 animate-pulse" style="width: 4.5rem; height: 24rem;"></div>
                    @endfor
                </div>
            @else
                {{-- No Results State --}}
                <div x-show="filteredBlogs.length === 0" class="text-center py-20 bg-white/5 border border-white/10 rounded-2xl max-w-lg mx-auto">
                    <div class="w-12 h-12 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mx-auto mb-4 text-gray-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">No Articles Found</h3>
                    <p class="text-gray-400 text-sm mb-6">No articles matched your active filters. Try resetting the filters.</p>
                    <button 
                        type="button" 
                        @click="resetFilters()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-white/10 hover:bg-white/20 border border-white/15 transition-colors cursor-pointer"
                    >
                        <span>Reset Filters</span>
                    </button>
                </div>

                {{-- Continuous Shelves via Alpine x-for --}}
                <template x-for="(shelf, shelfIdx) in shelves" :key="'shelf-' + (selectedCategory || 'all') + '-' + (selectedSource || 'all') + '-' + shelfIdx">
                    <div class="w-full">
                        {{-- Shelf Strip --}}
                        <div 
                            class="w-full overflow-x-auto sm:overflow-visible py-2 no-scrollbar"
                            @mouseleave="hoveredId = null"
                        >
                            <div 
                                class="flex w-full items-center justify-center gap-1.5 sm:gap-2.5 min-w-max sm:min-w-0"
                                @mouseleave="hoveredId = null"
                            >
                                <template x-for="blog in shelf" :key="'blog-' + blog.id">
                                    <div 
                                        class="relative cursor-pointer overflow-hidden rounded-3xl shrink-0 transition-all duration-300 ease-in-out select-none border border-white/10 group bg-black"
                                        style="height: 24rem;"
                                        :style="hoveredId === blog.id 
                                            ? 'width: 24rem; height: 24rem;' 
                                            : 'width: 4.5rem; height: 24rem;'"
                                        :class="hoveredId === blog.id 
                                            ? 'shadow-[0_20px_50px_rgba(0,0,0,0.95)] ring-1 ring-emerald-400/40 border-white/30' 
                                            : 'hover:opacity-100 opacity-90 shadow-[0_8px_20px_rgba(0,0,0,0.5)] border-white/10'"
                                        @mouseenter="hoveredId = blog.id"
                                        @click="hoveredId = (hoveredId === blog.id ? null : blog.id)"
                                    >
                                        {{-- Background RSS Image: Clearly visible on hover with soft 8px focus blur --}}
                                        <img 
                                            :src="blog.image" 
                                            :alt="blog.title" 
                                            referrerpolicy="no-referrer"
                                            loading="lazy"
                                            x-on:error="$el.src = blog.fallback"
                                            class="w-full h-full object-cover pointer-events-none transition-all duration-500 ease-out" 
                                            :style="hoveredId === blog.id 
                                                ? 'opacity: 0.88; filter: blur(8px) brightness(0.7); transform: scale(1.08);' 
                                                : 'opacity: 1; filter: blur(0px) brightness(0.9); transform: scale(1);'"
                                        />

                                        {{-- Inactive Card: Ambient Dark Tint & Code/Source Spine --}}
                                        <div 
                                            x-show="hoveredId !== blog.id"
                                            class="absolute inset-0 flex flex-col justify-between items-center py-4 z-10 pointer-events-none bg-black/35 group-hover:bg-black/15 transition-colors"
                                        >
                                            <span 
                                                class="text-[10px] font-mono font-bold text-white/80 bg-black/70 px-1.5 py-0.5 rounded-full border border-white/15 backdrop-blur-sm"
                                                x-text="blog.code"
                                            ></span>
                                            <span 
                                                class="text-[9px] font-mono uppercase tracking-wider text-white/85 bg-black/80 px-2 py-0.5 rounded-full backdrop-blur-md border border-white/20 truncate max-w-[85%] text-center shadow-xs"
                                                x-text="blog.source_name"
                                            ></span>
                                        </div>

                                        {{-- Active Card: Soft Translucent Dark Scrim Overlay for Readability --}}
                                        <div 
                                            x-show="hoveredId === blog.id"
                                            x-transition:enter="transition-opacity duration-300 ease-out"
                                            x-transition:enter-start="opacity-0"
                                            x-transition:enter-end="opacity-100"
                                            x-transition:leave="transition-opacity duration-150 ease-in"
                                            x-transition:leave-start="opacity-100"
                                            x-transition:leave-end="opacity-0"
                                            style="background: linear-gradient(180deg, rgba(0, 0, 0, 0.40) 0%, rgba(0, 0, 0, 0.60) 50%, rgba(0, 0, 0, 0.85) 100%);"
                                            class="absolute inset-0 z-20 pointer-events-none"
                                        ></div>

                                        {{-- Active Card: Full Crisp Content Reveal --}}
                                        <div 
                                            x-show="hoveredId === blog.id"
                                            x-transition:enter="transition-all duration-300 ease-out"
                                            x-transition:enter-start="opacity-0 translate-y-2"
                                            x-transition:enter-end="opacity-100 translate-y-0"
                                            x-transition:leave="transition-all duration-150 ease-in"
                                            x-transition:leave-start="opacity-100 translate-y-0"
                                            x-transition:leave-end="opacity-0 translate-y-2"
                                            class="absolute inset-0 flex flex-col justify-between p-5 sm:p-6 text-white z-30 pointer-events-auto text-left"
                                        >
                                            {{-- Top Header Pill Row --}}
                                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span 
                                                        class="text-[10px] font-mono uppercase tracking-wider text-emerald-400 font-bold bg-white/10 px-2.5 py-0.5 rounded-full border border-white/20 backdrop-blur-md shadow-xs"
                                                        x-text="blog.code + ' • ' + blog.source_name"
                                                    ></span>
                                                    <template x-if="blog.category">
                                                        <span 
                                                            class="text-[10px] font-medium text-emerald-300 bg-emerald-500/25 px-2.5 py-0.5 rounded-full border border-emerald-500/35"
                                                            x-text="blog.category"
                                                        ></span>
                                                    </template>
                                                </div>
                                                <div class="flex items-center gap-1.5 text-[10px] text-white/90 font-mono shrink-0 bg-black/70 px-2.5 py-0.5 rounded-full border border-white/20 backdrop-blur-sm">
                                                    <svg class="w-3 h-3 text-emerald-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    <span :title="blog.date_title" x-text="blog.date_formatted"></span>
                                                    <span class="text-white/40">&bull;</span>
                                                    <span x-text="blog.read_time"></span>
                                                </div>
                                            </div>

                                            {{-- Center Title & Description --}}
                                            <div class="space-y-2.5 my-auto py-2">
                                                <h3 
                                                    class="text-base sm:text-lg md:text-xl font-bold text-white line-clamp-3 leading-snug drop-shadow-md"
                                                    x-text="blog.title"
                                                ></h3>

                                                <template x-if="blog.description">
                                                    <p 
                                                        class="text-xs sm:text-sm text-gray-200 line-clamp-3 leading-relaxed drop-shadow-sm"
                                                        x-text="blog.description"
                                                    ></p>
                                                </template>

                                                <div class="flex flex-wrap items-center gap-1 pt-1">
                                                    <template x-for="tag in blog.tags" :key="tag">
                                                        <span 
                                                            class="inline-flex items-center text-[9px] font-mono text-emerald-300 bg-emerald-500/20 border border-emerald-500/35 px-2 py-0.5 rounded-md"
                                                            x-text="'#' + tag.replace('#', '')"
                                                        ></span>
                                                    </template>
                                                </div>
                                            </div>

                                            {{-- Bottom Action Link Button --}}
                                            <div class="pt-2">
                                                <a 
                                                    :href="blog.external_url" 
                                                    target="_blank" 
                                                    rel="noopener noreferrer"
                                                    class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-500 border border-emerald-400/40 shadow-xl backdrop-blur-md transition-all duration-200 group/btn"
                                                >
                                                    <span>Read Article</span>
                                                    <span class="group-hover/btn:translate-x-1 transition-transform">&rarr;</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Subtle Architectural Shelf Ledge Divider Between Rows --}}
                        <template x-if="shelfIdx < shelves.length - 1">
                            <div class="relative w-full max-w-5xl mx-auto h-2 my-8 rounded-full bg-gradient-to-r from-transparent via-white/10 to-transparent border-t border-white/15 flex items-center justify-center">
                                <div class="w-1/3 h-[1px] bg-gradient-to-r from-transparent via-emerald-400/40 to-transparent blur-xs"></div>
                            </div>
                        </template>
                    </div>
                </template>
            @endif
        </div>
    </div>
</div>
