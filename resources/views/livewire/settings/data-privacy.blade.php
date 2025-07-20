<div class="bg-gradient-dark rounded-lg p-8 mb-8 shadow-2xs card-highlight">
    <h2 class="text-2xl font-bold mb-2 text-white">Data & Privacy</h2>
    <p class="mb-6 text-gray-400">Manage your data and privacy options</p>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-lg text-white font-semibold">Export Data</div>
                <div class="text-gray-400 text-sm">Download a copy of your data</div>
            </div>
            <button wire:click="exportData"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Export</button>
        </div>
        @if ($exported)
            <div class="mt-2 text-green-400 text-sm">Export started! Check your email or downloads soon.</div>
        @endif
    </div>
    <div>
        <div class="flex items-center justify-between">
            <div>
                <div class="text-lg text-white font-semibold">Delete Account</div>
                <div class="text-gray-400 text-sm">Permanently delete your account and all data</div>
            </div>
            <button wire:click="confirmDelete"
                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Delete</button>
        </div>
        @if ($showDeleteConfirm)
            <div class="mt-4 bg-gray-800 p-4 rounded shadow text-white">
                <div class="mb-2 font-bold">Are you sure you want to delete your account?</div>
                <div class="mb-4 text-gray-400">This action cannot be undone.</div>
                <button wire:click="deleteAccount"
                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition mr-2">Yes, Delete</button>
                <button wire:click="$set('showDeleteConfirm', false)"
                    class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition">Cancel</button>
            </div>
        @endif
    </div>
    @if (session()->has('message'))
        <div class="mt-4 text-green-400">{{ session('message') }}</div>
    @endif
</div>