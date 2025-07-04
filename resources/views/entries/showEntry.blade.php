<x-layout :showNav="false" :showSidebar="true">
    <section class="p-6" id="mainContent">
        <h1>{{ $entry->title }}</h1>
        <p>{{ $entry->content }}</p>
        <div>{{ $entry->created_at->format('M d, Y') }}</div>
    </section>
</x-layout>