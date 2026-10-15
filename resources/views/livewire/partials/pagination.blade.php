@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-white/10 pt-5 mt-6">
        {{-- Results Counter --}}
        <div>
            <p class="text-xs sm:text-sm text-gray-400 font-medium">
                Showing
                <span class="font-semibold text-white">{{ $paginator->firstItem() ?? 0 }}</span>
                to
                <span class="font-semibold text-white">{{ $paginator->lastItem() ?? 0 }}</span>
                of
                <span class="font-semibold text-white">{{ $paginator->total() }}</span>
                results
            </p>
        </div>

        {{-- Pagination Links --}}
        <div class="flex items-center gap-1.5">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                    <span class="relative inline-flex items-center justify-center px-3 py-1.5 text-xs sm:text-sm font-medium text-gray-500 bg-[#080d19]/60 border border-white/5 cursor-not-allowed rounded-lg select-none opacity-50">
                        <svg class="w-4 h-4 mr-1 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span>Previous</span>
                    </span>
                </span>
            @else
                <button type="button"
                    wire:click="previousPage('{{ $paginator->getPageName() }}')"
                    wire:loading.attr="disabled"
                    class="cursor-pointer relative inline-flex items-center justify-center px-3 py-1.5 text-xs sm:text-sm font-medium text-gray-300 hover:text-white bg-[#080d19] border border-white/10 hover:border-blue-400/50 hover:bg-[#0b1328] rounded-lg transition-all duration-150 shadow-xs active:scale-95 disabled:opacity-50"
                    aria-label="{{ __('pagination.previous') }}">
                    <svg class="w-4 h-4 mr-1 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Previous</span>
                </button>
            @endif

            {{-- Numbered Page Links --}}
            <div class="hidden sm:flex items-center gap-1">
                @foreach ($elements as $element)
                    {{-- Three Dots Separator --}}
                    @if (is_string($element))
                        <span aria-disabled="true" class="px-2.5 py-1.5 text-xs sm:text-sm font-medium text-gray-500">
                            {{ $element }}
                        </span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <span wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}">
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page"
                                        class="relative inline-flex items-center justify-center min-w-8 h-8 px-2 text-xs sm:text-sm font-bold text-white bg-blue-600 border border-blue-500 rounded-lg shadow-sm shadow-blue-500/30 select-none">
                                        {{ $page }}
                                    </span>
                                @else
                                    <button type="button"
                                        wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                        wire:loading.attr="disabled"
                                        class="cursor-pointer relative inline-flex items-center justify-center min-w-8 h-8 px-2 text-xs sm:text-sm font-medium text-gray-300 hover:text-white bg-[#080d19] border border-white/10 hover:border-blue-400/50 hover:bg-[#0b1328] rounded-lg transition-all duration-150 active:scale-95 disabled:opacity-50"
                                        aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </button>
                                @endif
                            </span>
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <button type="button"
                    wire:click="nextPage('{{ $paginator->getPageName() }}')"
                    wire:loading.attr="disabled"
                    class="cursor-pointer relative inline-flex items-center justify-center px-3 py-1.5 text-xs sm:text-sm font-medium text-gray-300 hover:text-white bg-[#080d19] border border-white/10 hover:border-blue-400/50 hover:bg-[#0b1328] rounded-lg transition-all duration-150 shadow-xs active:scale-95 disabled:opacity-50"
                    aria-label="{{ __('pagination.next') }}">
                    <span>Next</span>
                    <svg class="w-4 h-4 ml-1 sm:ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            @else
                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                    <span class="relative inline-flex items-center justify-center px-3 py-1.5 text-xs sm:text-sm font-medium text-gray-500 bg-[#080d19]/60 border border-white/5 cursor-not-allowed rounded-lg select-none opacity-50">
                        <span>Next</span>
                        <svg class="w-4 h-4 ml-1 sm:ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </span>
            @endif
        </div>
    </nav>
@endif
