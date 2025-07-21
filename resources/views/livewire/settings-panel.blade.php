<x-layout :showNav="false" :showSidebar="true">
    <section class="p-6 " id="mainContent">
        <div class="mb-6">
            <h1 class="text-3xl font-bold tracking-tight">Settings</h1>
            <p class="text-muted">Manage your account settings and preferences.</p>
        </div>
        <div x-data="{ tab: 'account' }" class="mx-auto max-w-4xl mt-6 space-y-6">

            <div class="border border-white/5 bg-white/5 rounded-lg">
                <nav class="flex justify-evenly space-x-6 p-2">
                    <button @click="tab = 'account'"
                        :class="tab === 'account' ? 'border-b-2 border-white text-white' : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-4 text-sm font-medium focus:outline-none">
                        Account
                    </button>
                    <button @click="tab = 'payment'"
                        :class="tab === 'payment' ? 'border-b-2 border-white text-white' : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-4 text-sm font-medium focus:outline-none">
                        Payment
                    </button>
                    <button @click="tab = 'appearance'"
                        :class="tab === 'appearance' ? 'border-b-2 border-white text-white' : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-4 text-sm font-medium focus:outline-none">
                        Appearance
                    </button>
                </nav>
            </div>
            <div>
                <div x-show="tab === 'account'" x-transition>
                    @livewire('settings.account-information')
                </div>
                <div x-show="tab === 'payment'" x-transition>
                </div>
                <div x-show="tab === 'appearance'" x-transition>
                    @livewire('settings.appearance')
                </div>
            </div>
        </div>
    </section>
</x-layout>