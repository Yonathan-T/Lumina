<x-layout :showNav="false" :showSidebar="true">
    <section class="p-6" id="mainContent">
        @livewire('history')
    </section>
    <x-skiper95-scroll-progress />
</x-layout>