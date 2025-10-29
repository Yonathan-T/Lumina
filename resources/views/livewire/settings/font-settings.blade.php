<div class="space-y-6" x-data="{ font: @js($selectedFont), fontSize: {{ \App\Helpers\FontHelper::getFontSize() }} }" @font-size-changed.window="fontSize = $event.detail.size">
    <div>
        <h3 class="text-md font-semibold text-gray-900 dark:text-white">Entry Font Style</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Choose how your journal entries look</p>
    </div>

    <!-- Live Preview Section -->
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-6 border border-blue-200 dark:border-blue-800"
        wire:ignore>
        <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-3">Live Preview</p>
        <div :class="'font-' + font" :style="`font-size: ${fontSize}px`" class="space-y-3">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Your Entry Title</h2>
            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">This is how your journal entries will look with
                the selected font. You can see the changes instantly as you select different fonts.</p>
            <div class="flex gap-2 flex-wrap">
                <span
                    class="px-3 py-1 bg-blue-200 dark:bg-blue-800 text-blue-900 dark:text-blue-100 rounded-full text-sm">tag-example</span>
                <span
                    class="px-3 py-1 bg-purple-200 dark:bg-purple-800 text-purple-900 dark:text-purple-100 rounded-full text-sm">another-tag</span>
            </div>
        </div>
    </div>

    <!-- Font Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
        @php
            $fonts = [
                ['id' => 'inter', 'name' => 'Inter', 'category' => 'Sans Serif'],
                ['id' => 'poppins', 'name' => 'Poppins', 'category' => 'Sans Serif'],
                ['id' => 'ubuntu', 'name' => 'Ubuntu', 'category' => 'Sans Serif'],
                ['id' => 'playfair', 'name' => 'Playfair', 'category' => 'Serif'],
                ['id' => 'lora', 'name' => 'Lora', 'category' => 'Serif'],
                ['id' => 'crimson', 'name' => 'Crimson', 'category' => 'Serif'],
                ['id' => 'merriweather', 'name' => 'Merriweather', 'category' => 'Serif'],
                ['id' => 'caveat', 'name' => 'Caveat', 'category' => 'Handwriting'],
                ['id' => 'dancing', 'name' => 'Dancing', 'category' => 'Handwriting'],
                ['id' => 'jetbrains', 'name' => 'JetBrains', 'category' => 'Monospace'],
            ];
        @endphp

        @foreach($fonts as $font)
            <button wire:click="updateFont('{{ $font['id'] }}')" @click="font = '{{ $font['id'] }}'"
                class="relative group p-4 rounded-lg border-2 transition-all duration-200 focus:outline-none"
                :aria-pressed="(font === '{{ $font['id'] }}').toString()" :class="font === '{{ $font['id'] }}' 
                                ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 ring-2 ring-blue-400/50' 
                                : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'">
                <!-- Checkmark -->
                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity"
                    :class="font === '{{ $font['id'] }}' ? 'opacity-100' : ''">
                    <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </div>

                <!-- Font Preview -->
                <div :class="'font-{{ $font['id'] }}'" class="mb-2">
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">Aa</p>
                </div>

                <!-- Font Name -->
                <p class="text-sm font-medium text-gray-900 dark:text-white flex items-center gap-2">{{ $font['name'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $font['category'] }}</p>
            </button>
        @endforeach
    </div>
</div>