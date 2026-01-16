<div class="mt-10 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        {{-- Header Centered --}}
        <div class="mb-12 text-center">
            <h1 class="text-3xl font-bold text-white mb-2">Mental Health & Journaling Insights</h1>
            <p class="text-gray-400 max-w-2xl mx-auto">Curated articles to support your wellness journey</p>
        </div>

        {{-- Filters Only --}}
        <div class="flex flex-wrap items-center justify-center mb-8 gap-4">
            <select wire:change="filterByCategory($event.target.value)"
                class="border border-white/15 text-white rounded-lg shadow-sm bg-background px-3 py-2 text-sm">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </select>

            <select wire:change="filterBySource($event.target.value)"
                class="border border-white/15 text-white rounded-lg shadow-sm bg-background px-3 py-2 text-sm">
                <option value="">All Sources</option>
                @foreach($sources as $source)
                    <option value="{{ $source }}">{{ $source }}</option>
                @endforeach
            </select>
        </div>

        {{-- Content --}}
        <div class="relative">
            @if($isLoading && count($blogs) == 0)
                <div class="grid gap-6 md:grid-cols-3 lg:grid-cols-4">
                    @for($i = 0; $i < 4; $i++)
                        <div class="animate-pulse bg-white/5 rounded-xl h-64 border border-white/10"></div>
                    @endfor
                </div>
            @elseif(count($blogs) > 0)
                <div class="grid gap-6 md:grid-cols-3 lg:grid-cols-4">
                    @foreach($blogs as $blog)
                        <article wire:key="blog-{{ $blog['id'] }}"
                            class="bg-white/5 border border-white/10 rounded-xl overflow-hidden hover:border-white/20 transition-all group">
                            <div class="aspect-video overflow-hidden bg-gray-900 relative">
                                @if($blog['image_url'])
                                    <img src="{{ $blog['image_url'] }}" alt="{{ $blog['title'] }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                                        onerror="this.parentElement.innerHTML='<div class=&quot;flex items-center justify-center h-full bg-gray-800&quot;><svg class=&quot;w-8 h-8 text-gray-600&quot; fill=&quot;currentColor&quot; viewBox=&quot;0 0 24 24&quot;><path d=&quot;M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z&quot;/></svg></div>'">
                                @else
                                    <div class="flex items-center justify-center h-full bg-gray-800">
                                        <svg class="w-8 h-8 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] uppercase tracking-wider text-blue-400 font-semibold">{{ $blog['source_name'] }}</span>
                                    <span class="text-[10px] text-gray-500">{{ \Carbon\Carbon::parse($blog['published_at'])->diffForHumans() }}</span>
                                </div>
                                <h3 class="text-sm font-bold text-white mb-2 line-clamp-2 leading-tight">
                                    {{ $blog['title'] }}
                                </h3>
                                <p class="text-gray-400 text-xs mb-4 line-clamp-2">
                                    {{ $blog['description'] }}
                                </p>
                                <a href="{{ $blog['external_url'] }}" target="_blank"
                                    class="inline-block text-blue-400 hover:text-blue-300 text-xs font-semibold">
                                    Read Article →
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="text-center py-20 bg-white/5 border border-white/10 rounded-2xl">
                    <x-icon name="newspaper-outline" class="h-12 w-12 text-gray-600 mx-auto mb-4" />
                    <h3 class="text-xl font-semibold text-white mb-2">No Articles Yet</h3>
                    <p class="text-gray-400">New articles are fetched automatically. Check back soon!</p>
                </div>
            @endif
        </div>
    </div>
</div>