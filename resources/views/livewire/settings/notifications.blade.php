<div class="bg-gradient-dark rounded-lg p-8 mb-8 shadow-2xs card-highlight">
    <h2 class="text-2xl font-bold mb-2 text-white">Notifications</h2>
    <p class="mb-6 text-gray-400">Manage your notification preferences</p>
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="text-lg text-white font-semibold">Email Notifications</div>
            <div class="text-gray-400 text-sm">Receive updates and news via email</div>
        </div>
        <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" wire:model="emailNotifications" class="sr-only peer">
            <div class="w-11 h-6 bg-gray-700 rounded-full peer peer-checked:bg-blue-600 transition"></div>
            <div
                class="absolute ml-1 mt-1 w-4 h-4 bg-white rounded-full shadow transform peer-checked:translate-x-5 transition">
            </div>
        </label>
    </div>
    <div class="flex items-center justify-between mb-2">
        <div>
            <div class="text-lg text-white font-semibold">Push Notifications</div>
            <div class="text-gray-400 text-sm">Get instant alerts in your browser or device</div>
        </div>
        <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" wire:model="pushNotifications" class="sr-only peer">
            <div class="w-11 h-6 bg-gray-700 rounded-full peer peer-checked:bg-blue-600 transition"></div>
            <div
                class="absolute ml-1 mt-1 w-4 h-4 bg-white rounded-full shadow transform peer-checked:translate-x-5 transition">
            </div>
        </label>
    </div>
</div>