<div class="bg-gradient-dark rounded-lg p-8 mb-8 shadow-2xs card-highlight">
    <h2 class="text-2xl font-bold mb-2 text-white">Appearance</h2>
    <p class="mb-6 text-gray-400">Customize how Memo Mate looks and feels</p>
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="text-lg text-white font-semibold">Dark Mode</div>
            <div class="text-gray-400 text-sm">Switch between light and dark themes</div>
        </div>
        <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" wire:model="darkMode" class="sr-only peer">
            <div class="w-11 h-6 bg-gray-700 rounded-full peer peer-checked:bg-blue-600 transition"></div>
            <div
                class="absolute ml-1 mt-1 w-4 h-4 bg-white rounded-full shadow transform peer-checked:translate-x-5 transition">
            </div>
        </label>
    </div>
    <div class="mb-4">
        <label class="block text-gray-300 mb-1" for="fontSize">Font Size</label>
        <select id="fontSize" wire:model="fontSize"
            class="w-full px-4 py-2 rounded bg-gradient-dark text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-white-500">
            <option value="Small">Small</option>
            <option value="Medium">Medium</option>
            <option value="Large">Large</option>
        </select>
    </div>
</div>