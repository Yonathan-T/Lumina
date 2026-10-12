<x-layout :showNav="false" :showSidebar="true" :isChatPage="true">
    <section class="h-full max-h-full w-full p-0 md:p-6 overflow-hidden overscroll-none" id="mainContent">
        @livewire('chat-interface')
    </section>

</x-layout>