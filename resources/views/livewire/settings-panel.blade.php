<x-layout :showNav="false" :showSidebar="true">
    <section class="p-4 sm:p-6" id="mainContent">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">Settings</h1>
            <p class="text-sm sm:text-base text-muted">Manage your account settings and preferences.</p>
        </div>
        <div x-data="{ tab: @js($activeTab) }" class="mx-auto max-w-4xl mt-6 space-y-6">

            <div class="border border-white/5 bg-white/5 rounded-xl overflow-hidden">
                <div class="overflow-x-auto scrollbar-none overscroll-x-contain py-1 px-1 sm:px-2">
                    <nav class="flex items-center min-w-max md:min-w-0 md:justify-evenly gap-1 sm:gap-2">
                        <button @click="tab = 'settings'"
                            :class="tab === 'settings' ? 'border-b-2 border-white text-white font-semibold bg-white/5 rounded-t-lg' : 'border-b-2 border-transparent text-gray-400 hover:text-white hover:border-gray-500'"
                            class="whitespace-nowrap py-2.5 px-3 sm:px-4 text-xs sm:text-sm font-medium focus:outline-none transition-colors cursor-pointer">
                            Settings
                        </button>
                        <button @click="tab = 'account'"
                            :class="tab === 'account' ? 'border-b-2 border-white text-white font-semibold bg-white/5 rounded-t-lg' : 'border-b-2 border-transparent text-gray-400 hover:text-white hover:border-gray-500'"
                            class="whitespace-nowrap py-2.5 px-3 sm:px-4 text-xs sm:text-sm font-medium focus:outline-none transition-colors cursor-pointer">
                            Account
                        </button>
                        <button @click="tab = 'subscription'"
                            :class="tab === 'subscription' ? 'border-b-2 border-white text-white font-semibold bg-white/5 rounded-t-lg' : 'border-b-2 border-transparent text-gray-400 hover:text-white hover:border-gray-500'"
                            class="whitespace-nowrap py-2.5 px-3 sm:px-4 text-xs sm:text-sm font-medium focus:outline-none transition-colors cursor-pointer">
                            Subscription
                        </button>
                        <button @click="tab = 'data'"
                            :class="tab === 'data' ? 'border-b-2 border-white text-white font-semibold bg-white/5 rounded-t-lg' : 'border-b-2 border-transparent text-gray-400 hover:text-white hover:border-gray-500'"
                            class="whitespace-nowrap py-2.5 px-3 sm:px-4 text-xs sm:text-sm font-medium focus:outline-none transition-colors cursor-pointer">
                            Data & Privacy
                        </button>
                        <button @click="tab = 'preference'"
                            :class="tab === 'preference' ? 'border-b-2 border-white text-white font-semibold bg-white/5 rounded-t-lg' : 'border-b-2 border-transparent text-gray-400 hover:text-white hover:border-gray-500'"
                            class="whitespace-nowrap py-2.5 px-3 sm:px-4 text-xs sm:text-sm font-medium focus:outline-none transition-colors cursor-pointer">
                            Preference
                        </button>
                    </nav>
                </div>
            </div>
            <div>
                <div x-show="tab === 'settings'" x-transition>
                    @livewire('settings.general')
                </div>
                <div x-show="tab === 'account'" x-transition>
                    @livewire('settings.account-information')
                </div>
                <div x-show="tab === 'data'" x-transition>
                    @livewire('settings.data-privacy')
                </div>
                <div x-show="tab === 'subscription'" x-transition>
                    @livewire('settings.subscription')
                </div>
                <div x-show="tab === 'preference'" x-transition>
                    @livewire('settings.appearance')
                </div>
            </div>
        </div>
    </section>
</x-layout>
