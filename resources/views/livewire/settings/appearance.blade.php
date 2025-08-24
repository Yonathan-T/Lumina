<div class="bg-gradient-dark rounded-lg p-8 mb-8 shadow-2xl card-highlight">
    <h2 class="text-2xl font-bold mb-2 text-white">Appearance</h2>
    <p class="mb-6 text-muted">Customize how Lumina looks and feels</p>

    <div class="ml-4 flex items-center justify-between mb-8">
        <div>
            <div class="text-lg text-white font-semibold">Dark Mode</div>
            <div class="text-muted text-sm">Switch between light and dark themes</div>
        </div>
        <x-toggle :model="'darkMode'" />
    </div>
    <hr class="ml-4 border border-white/5 mb-4" />

    <div class="ml-4 mb-6">
        <label for="fontSize" class="block text-sm font-medium text-gray-300 mb-1">Font Size</label>
        <div class="relative">
            <select id="fontSize" wire:model="fontSize" class="flex h-10 w-full rounded-md border border-white/15 shadow-sm bg-background px-3 py-2 text-sm
                text-white">
                <option value="Small">Small</option>
                <option value="Medium">Medium</option>
                <option value="Large">Large</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-muted">
                <!-- I need better drop downs here. react would  have saved me -->
            </div>
        </div>
    </div>

    <div class="ml-4">
        <label for="fontFamily" class="block text-sm font-medium text-gray-300 mb-1">Font Family</label>
        <div class="relative">
            <select id="fontFamily" wire:model="fontFamily" class="flex h-10 w-full rounded-md border border-white/15 shadow-sm bg-background px-3 py-2 text-sm
                text-white">
                <option value="sans-serif">Sans-serif</option>
                <option value="serif">Serif</option>
                <option value="monospace">Monospace</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-muted">
                <!-- and here. -->
            </div>
        </div>
    </div>
</div>