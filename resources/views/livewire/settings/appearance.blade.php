<div>

    <div class="bg-gradient-dark rounded-lg p-8 mb-8 shadow-2xl card-highlight">
        <h2 class="text-2xl font-bold mb-2 text-white">Appearance</h2>
        <p class="mb-6 text-muted">Customize how Lumina looks and feels</p>
        @livewire('settings.font-settings')
        {{-- <div class="ml-4 flex items-center justify-between mb-8">
            <div>
                <div class="text-lg text-white font-semibold">Dark Mode</div>
                <div class="text-muted text-sm">Switch between light and dark themes</div>
            </div>
            <x-toggle :model="'darkMode'" />
        </div> --}}
        <hr class="ml-4 border border-white/5 mb-4" />

        <div class="ml-4 mb-6">
            <h3 class="text-xl font-semibold text-white">Font Size</h3>
            <p class="text-muted mb-3">Customize your font size</p>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-muted">Font size:</span>
                <span
                    class="inline-flex items-center px-2 py-0.5 rounded bg-blue-600/20 text-blue-300 text-xs font-medium">{{ $fontSize }}px</span>
            </div>
            <input type="range" id="fontSize" wire:model.live="fontSize" min="12" max="24" step="1"
                class="w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer slider">
            <div class="flex justify-between text-xs text-muted mt-1">
                <span>12px</span>
                <span>18px</span>
                <span>24px</span>
            </div>
        </div>

    </div>

</div>