@props([
    'wireModel' => 'content',
    'name' => 'content',
    'placeholder' => "Type '/' for commands or start writing your reflection...",
    'initialContent' => '',
])

<div wire:ignore
    data-memo-canvas
    x-data="initMemoEditor({
        initialContent: @js($initialContent ?: ''),
        placeholder: @js($placeholder),
        wireModel: @js($wireModel)
    })"
    @submit.window="flushSync()"
    class="space-y-2 relative group/editor">

    <!-- Top Formatting Ribbon / Toolbar (Styled to match Lumina Sidebar) -->
    <div class="flex items-center justify-between gap-1 p-1 sm:p-1.5 sidebar-gradient bg-gradient-dark border border-white/10 rounded-xl shadow-md overflow-x-auto scrollbar-none">
        
        <!-- Headings Group -->
        <div class="flex items-center gap-0.5 shrink-0">
            <button type="button"
                @mousedown.prevent="toggleHeading(1)"
                :class="activeStates.heading1 ? 'bg-blue-300/15 text-white border border-blue-400/30' : 'text-gray-400 hover:text-white hover:bg-blue-300/15 border border-transparent'"
                class="px-2 py-1 rounded-md text-xs font-mono font-bold transition-colors cursor-pointer"
                title="Heading 1 (Shortcut: #)">
                H1
            </button>
            <button type="button"
                @mousedown.prevent="toggleHeading(2)"
                :class="activeStates.heading2 ? 'bg-blue-300/15 text-white border border-blue-400/30' : 'text-gray-400 hover:text-white hover:bg-blue-300/15 border border-transparent'"
                class="px-2 py-1 rounded-md text-xs font-mono font-bold transition-colors cursor-pointer"
                title="Heading 2 (Shortcut: ##)">
                H2
            </button>
            <button type="button"
                @mousedown.prevent="toggleHeading(3)"
                :class="activeStates.heading3 ? 'bg-blue-300/15 text-white border border-blue-400/30' : 'text-gray-400 hover:text-white hover:bg-blue-300/15 border border-transparent'"
                class="px-2 py-1 rounded-md text-xs font-mono font-bold transition-colors cursor-pointer"
                title="Heading 3 (Shortcut: ###)">
                H3
            </button>
        </div>

        <div class="h-4 w-px bg-white/10 mx-0.5 shrink-0"></div>

        <!-- Lists Group -->
        <div class="flex items-center gap-0.5 shrink-0">
            <button type="button"
                @mousedown.prevent="toggleTaskList()"
                :class="activeStates.taskList ? 'bg-blue-300/15 text-white border border-blue-400/30' : 'text-gray-400 hover:text-white hover:bg-blue-300/15 border border-transparent'"
                class="flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-medium transition-colors cursor-pointer"
                title="To-do Checklist (Shortcut: [] or [ ])">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="5" width="6" height="6" rx="1.5" />
                    <path d="m5 8 1 1 2-2" />
                    <path d="M13 6h8" />
                    <path d="M13 12h8" />
                    <path d="M13 18h8" />
                    <rect x="3" y="14" width="6" height="6" rx="1.5" />
                </svg>
                <span class="hidden sm:inline">Checklist</span>
            </button>

            <button type="button"
                @mousedown.prevent="toggleBulletList()"
                :class="activeStates.bulletList ? 'bg-blue-300/15 text-white border border-blue-400/30' : 'text-gray-400 hover:text-white hover:bg-blue-300/15 border border-transparent'"
                class="p-1.5 rounded-md text-xs transition-colors cursor-pointer"
                title="Bullet List (Shortcut: - )">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="9" y1="6" x2="20" y2="6"/>
                    <line x1="9" y1="12" x2="20" y2="12"/>
                    <line x1="9" y1="18" x2="20" y2="18"/>
                    <circle cx="4" cy="6" r="1.5" fill="currentColor"/>
                    <circle cx="4" cy="12" r="1.5" fill="currentColor"/>
                    <circle cx="4" cy="18" r="1.5" fill="currentColor"/>
                </svg>
            </button>

            <button type="button"
                @mousedown.prevent="toggleOrderedList()"
                :class="activeStates.orderedList ? 'bg-blue-300/15 text-white border border-blue-400/30' : 'text-gray-400 hover:text-white hover:bg-blue-300/15 border border-transparent'"
                class="p-1.5 rounded-md text-xs transition-colors cursor-pointer"
                title="Numbered List (Shortcut: 1. )">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="10" y1="6" x2="21" y2="6"/>
                    <line x1="10" y1="12" x2="21" y2="12"/>
                    <line x1="10" y1="18" x2="21" y2="18"/>
                    <path d="M4 6h1v4"/>
                    <path d="M4 10h2"/>
                    <path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/>
                </svg>
            </button>
        </div>

        <div class="h-4 w-px bg-white/10 mx-0.5 shrink-0"></div>

        <!-- Blocks Group -->
        <div class="flex items-center gap-0.5 shrink-0">
            <button type="button"
                @mousedown.prevent="toggleBlockquote()"
                :class="activeStates.blockquote ? 'bg-blue-300/15 text-white border border-blue-400/30' : 'text-gray-400 hover:text-white hover:bg-blue-300/15 border border-transparent'"
                class="flex items-center gap-1 px-2 py-1 rounded-md text-xs transition-colors cursor-pointer"
                title="Quote (Shortcut: >)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span class="hidden md:inline">Quote</span>
            </button>

            <button type="button"
                @mousedown.prevent="toggleCodeBlock()"
                :class="activeStates.codeBlock ? 'bg-blue-300/15 text-white border border-blue-400/30' : 'text-gray-400 hover:text-white hover:bg-blue-300/15 border border-transparent'"
                class="p-1.5 rounded-md text-xs transition-colors cursor-pointer"
                title="Code Block (Shortcut: ```)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                </svg>
            </button>

            <button type="button"
                @mousedown.prevent="insertDivider()"
                class="p-1.5 rounded-md text-xs text-gray-400 hover:text-white hover:bg-blue-300/15 border border-transparent transition-colors cursor-pointer"
                title="Divider (Shortcut: ---)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                </svg>
            </button>
        </div>

        <div class="h-4 w-px bg-white/10 mx-0.5 shrink-0"></div>

        <!-- Inline Styling Group -->
        <div class="flex items-center gap-0.5 shrink-0">
            <button type="button"
                @mousedown.prevent="toggleBold()"
                :class="activeStates.bold ? 'bg-blue-300/15 text-white border border-blue-400/30' : 'text-gray-400 hover:text-white hover:bg-blue-300/15 border border-transparent'"
                class="px-2 py-1 rounded-md text-xs font-bold transition-colors cursor-pointer"
                title="Bold (Ctrl+B)">
                B
            </button>

            <button type="button"
                @mousedown.prevent="toggleItalic()"
                :class="activeStates.italic ? 'bg-blue-300/15 text-white border border-blue-400/30' : 'text-gray-400 hover:text-white hover:bg-blue-300/15 border border-transparent'"
                class="px-2 py-1 rounded-md text-xs italic font-serif transition-colors cursor-pointer"
                title="Italic (Ctrl+I)">
                I
            </button>

            <button type="button"
                @mousedown.prevent="toggleStrike()"
                :class="activeStates.strike ? 'bg-blue-300/15 text-white border border-blue-400/30' : 'text-gray-400 hover:text-white hover:bg-blue-300/15 border border-transparent'"
                class="px-2 py-1 rounded-md text-xs line-through font-serif transition-colors cursor-pointer"
                title="Strikethrough (Shortcut: ~~text~~)">
                S
            </button>

            <button type="button"
                @mousedown.prevent="toggleHighlight()"
                :class="activeStates.highlight ? 'bg-blue-300/15 text-white border border-blue-400/30' : 'text-gray-400 hover:text-white hover:bg-blue-300/15 border border-transparent'"
                class="p-1.5 rounded-md text-xs transition-colors cursor-pointer"
                title="Highlight">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
            </button>
        </div>

    </div>

    <!-- Hidden native textarea for form submit serialization -->
    <textarea
        x-ref="hiddenInput"
        name="{{ $name }}"
        class="hidden"></textarea>

    <!-- Canvas Editing Surface (Uses Lumina's standard card & pattern) -->
    <div class="relative">
        <div
            x-ref="editorContainer"
            class="min-h-[340px] sm:min-h-[400px] px-3.5 py-3 sm:px-5 sm:py-4 card-highlight bg-gradient-dark border border-white/10 focus-within:border-white/20 focus-within:ring-1 focus-within:ring-blue-400/20 rounded-2xl transition-all shadow-inner relative">
        </div>
    </div>

    <!-- Bottom Counter & Shortcuts Hint Bar -->
    <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-gray-400 font-mono px-1">
        <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 text-gray-400">
            <span class="text-gray-300">
                <kbd class="px-1.5 py-0.5 rounded bg-white/5 border border-white/10 text-blue-300 font-mono text-[10px]">/</kbd> commands
            </span>
            <span class="text-gray-600 hidden sm:inline">•</span>
            <span class="hidden sm:inline text-gray-400/80">
                <span class="text-blue-300 font-semibold">[]</span> to-do
            </span>
            <span class="text-gray-600 hidden sm:inline">•</span>
            <span class="hidden sm:inline text-gray-400/80">
                <span class="text-blue-300 font-semibold">#</span> H1
            </span>
            <span class="text-gray-600 hidden sm:inline">•</span>
            <span class="hidden sm:inline text-gray-400/80">
                <span class="text-blue-300 font-semibold">##</span> H2
            </span>
            <span class="text-gray-600 hidden md:inline">•</span>
            <span class="hidden md:inline text-gray-400/80">
                <span class="text-blue-300 font-semibold">&gt;</span> quote
            </span>
            <span class="text-gray-600 hidden md:inline">•</span>
            <span class="hidden md:inline text-gray-400/80">
                <span class="text-blue-300 font-semibold">-</span> list
            </span>
            <span class="text-gray-600 hidden lg:inline">•</span>
            <span class="hidden lg:inline text-gray-400/80">
                <span class="text-blue-300 font-semibold">```</span> code
            </span>
        </div>
        <div class="ml-auto text-gray-400 shrink-0">
            <span x-text="wordCount" class="text-gray-200 font-medium">0</span> words
            <span class="text-gray-600 mx-1">•</span>
            <span x-text="charCount" class="text-gray-200 font-medium">0</span> characters
        </div>
    </div>

</div>
