<div class="bg-gradient-dark rounded-lg p-8 mb-8 shadow-2xs card-highlight">
    <h2 class="text-2xl font-bold mb-2 text-white">Account Information</h2>
    <p class="mb-6 text-muted">Update your account details and personal information</p>
    <form wire:submit.prevent="save" class="space-y-4">
        <div>
            <label class="block text-gray-300 mb-1" for="name">Name</label>
            <input id="name" type="text" wire:model.defer="name"
                class="w-full px-4 py-2 rounded bg-gradient-dark text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-white-500" />
            <x-form-error name="name" />
        </div>
        <div class=" flex items-center justify-between mb-6">
            <div>
                <div class="text-lg text-white font-semibold"> Set Password</div>

                <div class="text-muted text-sm">Click the button to receive an email to set up a password for
                    your account
                </div>
            </div>
            <button wire:click="logoutSession('')"
                class="transition duration-300 ease-in-out hover:bg-white hover:text-gray-900 px-4 py-2 rounded-md border border-white/5 cursor-pointer">
                Set Password </button>
        </div>
        <div>
            <label class="block text-gray-300 mb-1" for="email">Email</label>
            <input id="email" type="email" wire:model.defer="email"
                class="w-full px-4 py-2 rounded bg-gradient-dark text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-white-500" />
            <x-form-error name="email" />
        </div>
        <button type="submit"
            class="mt-4 px-6 py-2 bg-white text-gray-900 rounded font-semibold hover:bg-gray-200 transition"
            wire:loading.attr="disabled" wire:target="save">

            <span wire:loading.remove wire:target="save">Save Changes</span>
            <!-- animation would be fire here instead of putting it as a text and a dot -->
            <span wire:loading wire:target="save">Saving...</span>

        </button>
        <div wire:loading.remove wire:target="save">
            @if (session()->has('message'))
                <div class="mt-4 text-green-400">{{ session('message') }}</div>
            @endif
        </div>

    </form>
</div>