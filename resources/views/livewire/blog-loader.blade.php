<div>
    <div class="mt-10 min-h-screen bg-gradient-dark bg-dot-pattern">
        <div class="container mx-auto px-4 py-8">
            @if($showPreview && $isLoading)
                <div class="text-center mb-12 py-16">
                    <!-- <div class="mb-8">
                                <div class="relative inline-block">
                                    <div
                                        class="w-24 h-24 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mb-6 mx-auto animate-pulse">
                                        <x-icon name="sparkles" class="h-12 w-12 text-white" />
                                    </div>
                                    <div class="absolute -top-2 -right-2 w-8 h-8 bg-yellow-400 rounded-full animate-bounce"></div>
                                </div>
                            </div> -->

                    <h2 class="text-3xl font-bold text-white mb-4 animate-fade-in">
                        Don't miss what's new at
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500">Lumina</span>
                    </h2>

                    <p class="text-muted text-lg mb-8 max-w-xl mx-auto">
                        We're curating the latest insights on mental wellness and journaling just for you...
                    </p>
                </div>
            @else
                <div class="text-center mb-12">
                    <h1 class="text-4xl font-bold text-white mb-4 flex items-center justify-center gap-3">
                        <x-icon name="book-outline" class="h-8 w-8 text-blue-400" />
                        Mental Health & Journaling Insights
                    </h1>
                    <p class="text-gray-300 text-lg max-w-2xl mx-auto">
                        Curated articles on mental wellness, journaling techniques, and personal growth from trusted sources
                    </p>
                </div>
            @endif

            @if(!$showPreview || count($blogs) > 0)
                <div class="flex flex-wrap items-center justify-between mb-8 gap-4">
                    <div class="flex flex-wrap gap-3">
                        <select wire:change="filterByCategory($event.target.value)"
                            class="bg-gradient-dark border border-white/15 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-white">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ $selectedCategory == $category ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>

                        <select wire:change="filterBySource($event.target.value)"
                            class="bg-gradient-dark border border-white/15 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-white">
                            <option value="">All Sources</option>
                            @foreach($sources as $source)
                                <option value="{{ $source }}" {{ $selectedSource == $source ? 'selected' : '' }}>
                                    {{ $source }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button wire:click="refreshBlogs"
                        class="inline-flex border border-white/15 items-center gap-2 bg-gradient-dark hover:border-white/20 text-white px-4 py-2 rounded-lg transition-colors"
                        wire:loading.attr="disabled">
                        <x-icon name="rotate-ccw" class="h-4 w-4" wire:loading.class="animate-spin" />
                        <span wire:loading.remove>Refresh Content</span>
                        <span wire:loading>Refreshing...</span>
                    </button>
                </div>
            @endif

            @if($isLoading && count($blogs) == 0)
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @for($i = 0; $i < 6; $i++)
                        <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden animate-pulse">
                            <div class="aspect-video bg-gray-700"></div>
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="h-4 bg-gray-700 rounded w-20"></div>
                                    <div class="h-3 bg-gray-700 rounded w-16"></div>
                                </div>
                                <div class="space-y-2 mb-3">
                                    <div class="h-5 bg-gray-700 rounded w-full"></div>
                                    <div class="h-5 bg-gray-700 rounded w-3/4"></div>
                                </div>
                                <div class="space-y-2 mb-4">
                                    <div class="h-4 bg-gray-700 rounded w-full"></div>
                                    <div class="h-4 bg-gray-700 rounded w-5/6"></div>
                                    <div class="h-4 bg-gray-700 rounded w-2/3"></div>
                                </div>
                                <div class="h-6 bg-gray-700 rounded w-24 mb-3"></div>
                                <div class="flex gap-2 mb-4">
                                    <div class="h-5 bg-gray-700 rounded w-12"></div>
                                    <div class="h-5 bg-gray-700 rounded w-16"></div>
                                    <div class="h-5 bg-gray-700 rounded w-14"></div>
                                </div>
                                <div class="h-8 bg-gray-700 rounded w-32"></div>
                            </div>
                        </div>
                    @endfor
                </div>
            @elseif(count($blogs) > 0)
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($blogs as $blog)
                        <article
                            class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden hover:border-blue-500/50 transition-all duration-300 hover:scale-[1.02] group animate-fade-in">
                            <div class="aspect-video overflow-hidden bg-gradient-to-br from-blue-600/20 to-purple-600/20">
                                @if($blog['image_url'])
                                    <img src="{{ $blog['image_url'] }}" alt="{{ $blog['title'] }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="w-full h-full hidden items-center justify-center text-gray-400">
                                        <div class="text-center">
                                            <x-icon name="scroll-text" class="h-12 w-12 mx-auto mb-2 opacity-50" />
                                            <p class="text-sm opacity-75">{{ $blog['source_name'] }}</p>
                                        </div>
                                    </div>
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <div class="text-center">
                                            <x-icon name="scroll-text" class="h-12 w-12 mx-auto mb-2 opacity-50" />
                                            <p class="text-sm opacity-75">{{ $blog['source_name'] }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs font-medium text-blue-400 bg-blue-500/20 px-2 py-1 rounded-full">
                                        {{ $blog['source_name'] }}
                                    </span>
                                    <span class="text-xs text-gray-400">
                                        {{ \Carbon\Carbon::parse($blog['published_at'])->diffForHumans() }}
                                    </span>
                                </div>
                                <h3
                                    class="text-lg font-semibold text-white mb-3 line-clamp-2 group-hover:text-blue-300 transition-colors">
                                    {{ $blog['title'] }}
                                </h3>
                                <p class="text-gray-300 text-sm mb-4 line-clamp-3">
                                    {{ strlen($blog['description']) > 150 ? substr($blog['description'], 0, 150) . '...' : $blog['description'] }}
                                </p>
                                @if($blog['category'])
                                    <div class="mb-3">
                                        <span class="inline-block bg-purple-500/20 text-purple-300 text-xs px-2 py-1 rounded-full">
                                            {{ $blog['category'] }}
                                        </span>
                                    </div>
                                @endif
                                @if($blog['tags'] && count($blog['tags']) > 0)
                                    <div class="flex flex-wrap gap-1 mb-4">
                                        @foreach(array_slice($blog['tags'], 0, 3) as $tag)
                                            <span class="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded">
                                                #{{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                                <a href="{{ $blog['external_url'] }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2 text-blue-400 hover:text-blue-300 text-sm font-medium group-hover:gap-3 transition-all">
                                    Read Full Article
                                    <x-icon name="scroll-text" class="h-4 w-4" />
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="text-center py-16">
                    <div class="mb-6">
                        <x-icon name="scroll-text" class="h-16 w-16 text-gray-600 mx-auto" />
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">No Articles Yet</h3>
                    <p class="text-gray-400 mb-6">We're fetching the latest mental health and journaling content for you.
                    </p>
                    <button wire:click="refreshBlogs"
                        class="inline-flex items-center gap-2 bg-gradient-dark hover:border-white/20 text-white px-6 py-3 rounded-lg transition-colors">
                        <x-icon name="rotate-ccw" class="h-5 w-5" />
                        Load Articles
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>