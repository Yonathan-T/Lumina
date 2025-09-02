<x-layout>
    <div class="min-h-screen bg-gradient-dark">
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-white mb-4 flex items-center justify-center gap-3">
                    <x-icon name="book-outline" class="h-8 w-8 text-blue-400" />
                    Mental Health & Journaling Insights
                </h1>
                <p class="text-gray-300 text-lg max-w-2xl mx-auto">
                    Curated articles on mental wellness, journaling techniques, and personal growth from trusted sources
                </p>
            </div>

            <!-- Filters & Refresh -->
            <div class="flex flex-wrap items-center justify-between mb-8 gap-4">
                <div class="flex flex-wrap gap-3">
                    <!-- Category Filter -->
                    <select onchange="filterByCategory(this.value)"
                        class="bg-gradient-dark border border-white/15 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-white">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Source Filter -->
                    <select onchange="filterBySource(this.value)"
                        class="bg-gradient-dark border border-white/15 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-white">
                        <option value="">All Sources</option>
                        @foreach($sources as $source)
                            <option value="{{ $source }}" {{ request('source') == $source ? 'selected' : '' }}>
                                {{ $source }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Refresh Button -->
                <a href="{{ route('blogs.refresh') }}"
                    class="inline-flex border border-white/15 items-center gap-2 bg-gradient-dark hover:border-white/20 text-white px-4 py-2 rounded-lg transition-colors">
                    <x-icon name="rotate-ccw" class="h-4 w-4 " wire:loading class="animate-spin" />
                    Refresh Content
                </a>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-6 bg-green-500/20 border border-green-500/30 text-green-300 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Blog Grid -->
            @if($blogs->count() > 0)
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($blogs as $blog)
                        <article
                            class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden hover:border-blue-500/50 transition-all duration-300 hover:scale-[1.02] group">
                            <!-- Image -->
                            <div class="aspect-video overflow-hidden bg-gradient-to-br from-blue-600/20 to-purple-600/20">
                                @if($blog->image_url)
                                    <img src="{{ $blog->image_url }}" alt="{{ $blog->title }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <!-- Fallback placeholder -->
                                    <div class="w-full h-full hidden items-center justify-center text-gray-400">
                                        <div class="text-center">
                                            <x-icon name="scroll-text" class="h-12 w-12 mx-auto mb-2 opacity-50" />
                                            <p class="text-sm opacity-75">{{ $blog->source_name }}</p>
                                        </div>
                                    </div>
                                @else
                                    <!-- Default placeholder -->
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <div class="text-center">
                                            <x-icon name="scroll-text" class="h-12 w-12 mx-auto mb-2 opacity-50" />
                                            <p class="text-sm opacity-75">{{ $blog->source_name }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="p-6">
                                <!-- Source & Date -->
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs font-medium text-blue-400 bg-blue-500/20 px-2 py-1 rounded-full">
                                        {{ $blog->source_name }}
                                    </span>
                                    <span class="text-xs text-gray-400">
                                        {{ $blog->time_ago }}
                                    </span>
                                </div>

                                <!-- Title -->
                                <h3
                                    class="text-lg font-semibold text-white mb-3 line-clamp-2 group-hover:text-blue-300 transition-colors">
                                    {{ $blog->title }}
                                </h3>

                                <!-- Description -->
                                <p class="text-gray-300 text-sm mb-4 line-clamp-3">
                                    {{ $blog->excerpt }}
                                </p>

                                <!-- Category & Tags -->
                                @if($blog->category)
                                    <div class="mb-3">
                                        <span class="inline-block bg-purple-500/20 text-purple-300 text-xs px-2 py-1 rounded-full">
                                            {{ $blog->category }}
                                        </span>
                                    </div>
                                @endif

                                @if($blog->tags && count($blog->tags) > 0)
                                    <div class="flex flex-wrap gap-1 mb-4">
                                        @foreach(array_slice($blog->tags, 0, 3) as $tag)
                                            <span class="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded">
                                                #{{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Read More Button -->
                                <a href="{{ $blog->external_url }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2 text-blue-400 hover:text-blue-300 text-sm font-medium group-hover:gap-3 transition-all">
                                    Read Full Article
                                    <x-icon name="scroll-text" class="h-4 w-4" />
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-12">
                    {{ $blogs->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="mb-6">
                        <x-icon name="scroll-text" class="h-16 w-16 text-gray-600 mx-auto" />
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">No Articles Yet</h3>
                    <p class="text-gray-400 mb-6">We're fetching the latest mental health and journaling content for you.
                    </p>
                    <a href="{{ route('blogs.refresh') }}"
                        class="inline-flex items-center gap-2 bg-gradient-dark hover:border-white/20 text-white px-6 py-3 rounded-lg transition-colors">
                        <x-icon name="rotate-ccw" class="h-5 w-5" wire:loading class="animate-spin" />
                        Load Articles
                    </a>
                </div>
            @endif
        </div>
    </div>

    <script>
        function filterByCategory(category) {
            const url = new URL(window.location);
            if (category) {
                url.searchParams.set('category', category);
            } else {
                url.searchParams.delete('category');
            }
            window.location.href = url.toString();
        }

        function filterBySource(source) {
            const url = new URL(window.location);
            if (source) {
                url.searchParams.set('source', source);
            } else {
                url.searchParams.delete('source');
            }
            window.location.href = url.toString();
        }
    </script>

    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</x-layout>