<!-- In settings-panel.blade.php -->

<x-layout :showNav="false" :showSidebar="true">
    <section class="p-6" id="mainContent">
        <h1 class="text-3xl font-bold tracking-tight">Settings</h1>
        <p class="text-muted">Manage your account settings and preferences</p>
        <div x-data="{ tab: 'profile' }">
            <button @click="tab = 'profile'">Profile</button>
            <button @click="tab = 'payment'">Payment</button>
            <button @click="tab = 'appearance'">Appearance</button>

            <div x-show="tab === 'profile'">
                @livewire('settings.account-information')
            </div>
            <div x-show="tab === 'payment'">
                @livewire('settings.appearance')
            </div>
        </div>



    </section>
</x-layout>