<div>


    <div
        class=" bg-gradient-dark rounded-lg p-8 mb-8 shadow-2xs card-highlight flex flex-col gap-6 border border-white/5 py-6">
        <div class="flex flex-col gap-1.5 px-6">
            <div class="text-2xl font-bold mb-2 text-white">Current plan</div>
        </div>
        <div class="ml-4 px-6 flex items-center justify-between">
            <div>
                <p class="text-lg font-medium">Free</p>
                <p class="text-sm text-muted">$0/monthly</p>
            </div>
        </div>
    </div>

    <div class="bg-gradient-dark rounded-lg p-8 mb-8 shadow-2xs card-highlight">
        <h2 class="text-2xl font-bold mb-2 text-white"> Premium Features</h2>
        <p class="mb-6 text-muted"> Upgrade to access premium features and capabilities</p>
        <form wire:submit.prevent="save" class="space-y-4">
            <div class="ml-4 flex items-center justify-between mb-6">
                <div>
                    <div class="text-lg text-white font-semibold">
                        <x-icon name="lock" class="w-4 h-4 inline" />

                        AI-Powered Insights
                    </div>

                    <div class="text-muted text-sm">Get personalized insights based on your journal entries
                    </div>
                </div>
                <button wire:click="Upgrade('Test')"
                    class="bg-transparent border border-white/10 hover:bg-white/5 cursor-pointer bg-gradient-black px-4 py-2 rounded-lg">
                    Upgrade
                </button>
            </div>
            <hr class="ml-4 border border-white/5 mb-4" />
            <div class="ml-4 flex items-center justify-between mb-6">
                <div>
                    <div class="text-lg text-white font-semibold">
                        <x-icon name="lock" class="w-4 h-4 inline" />

                        AI-Powered Insights
                    </div>

                    <div class="text-muted text-sm">Get personalized insights based on your journal entries
                    </div>
                </div>
                <button wire:click="Upgrade('Test')"
                    class="bg-transparent border border-white/10 hover:bg-white/5 cursor-pointer bg-gradient-black px-4 py-2 rounded-lg">
                    Upgrade
                </button>
            </div>
            <hr class="ml-4 border border-white/5 mb-4" />
            <div class="ml-4 flex items-center justify-between mb-6">
                <div>
                    <div class="text-lg text-white font-semibold">
                        <x-icon name="lock" class="w-4 h-4 inline" />

                        AI-Powered Insights
                    </div>

                    <div class="text-muted text-sm">Get personalized insights based on your journal entries
                    </div>
                </div>
                <button wire:click="Upgrade('Test')"
                    class="bg-transparent border border-white/10 hover:bg-white/5 cursor-pointer bg-gradient-black px-4 py-2 rounded-lg">
                    Upgrade
                </button>
            </div>
            <hr class="ml-4 border border-white/5 mb-4" />


            <div wire:loading.remove wire:target="save">
                @if (session()->has('message'))
                    <div class="mt-4 text-green-400">{{ session('message') }}</div>
                @endif
            </div>

        </form>


    </div>
</div>