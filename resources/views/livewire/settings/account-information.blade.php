<div>
    <div class="bg-gradient-dark rounded-lg p-8 mb-8 shadow-2xl card-highlight">
        {{-- <h2 class="text-2xl font-bold mb-2 text-white">Theme</h2>
        <p class="mb-6 text-muted">Select your preferred theme for Lumina</p> --}}
        @livewire('settings.api-integration')
    </div>
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
                    <div class="text-lg text-white font-semibold"> Change Password</div>

                    <div class="text-muted text-sm">Click the button to receive an email to set up a password for
                        your account
                    </div>
                </div>
                <button type="button" wire:click="forgotPassword"
                    class="transition duration-300 ease-in-out hover:bg-white hover:text-gray-900 px-4 py-2 rounded-md border border-white/5 cursor-pointer">
                    Set Password </button>
            </div>
            @if (session('status'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
            <div>
                <label class="block text-gray-300 mb-1" for="email">Email</label>
                <input id="email" type="email" wire:model.defer="email"
                    class="w-full px-4 py-2 rounded bg-gradient-dark text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-white-500" />
                <x-form-error name="email" />
            </div>


            @if (session('emailError'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('emailError') }}</span>
                </div>
            @endif
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

    <div class="bg-gradient-dark rounded-lg p-8 mb-8 shadow-2xs card-highlight">
        <h2 class="text-2xl font-bold mb-2 text-white">Account</h2>
        <div class="flex justify-between">


            <div class="mb-6 text-muted  space-y-2">
                <p><strong>Created on:</strong> {{ auth()->user()->created_at->format('F j, Y') }}</p>
                <p><strong>Last updated:</strong> {{ auth()->user()->updated_at->diffForHumans() }}</p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="mt-4 px-6 py-2 text-white rounded-lg font-semibold border border-white/10 bg-red-500 hover:bg-red-600 transition flex items-center gap-2">
                    <x-icon name="log-out" class="w-4 h-4" />
                    <span>Log out</span>
                </button>
            </form>
        </div>
    </div>

</div>