<x-layout :showNav="false" :showSidebar="true">
    @livewire('edit-entry', ['entry' => $entry])
    <x-skiper95-scroll-progress />
</x-layout>