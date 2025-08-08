@props(['model'])
<label class="relative inline-flex items-center cursor-pointer">
    <input type="checkbox" wire:model="{{ $model }}" class="sr-only peer" />
    <div class="w-11 h-6 bg-gray-600 rounded-full peer peer-checked:bg-white transition-colors duration-300">
    </div>
    <div
        class="absolute left-1 top-1 w-4 h-4 bg-gradient-dark rounded-full shadow transform peer-checked:translate-x-5 transition-transform duration-300">
    </div>
</label>