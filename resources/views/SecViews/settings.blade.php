<x-layout :showNav="false" :showSidebar="true">
    <section class="p-6" id="mainContent">
        <h1 class="text-3xl font-bold tracking-tight">Settings</h1>
        <p class="text-muted">Manage your account settings and preferences</p>
        @livewire('settings.account-information')
        @livewire('settings.appearance')
        @livewire('settings.notifications')
        @livewire('settings.data-privacy')
    </section>
</x-layout>