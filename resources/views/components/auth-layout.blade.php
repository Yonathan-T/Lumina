<x-layout>
    <section class="p-4 mt-10">
        <div class="mx-auto max-w-md bg-gradient-dark rounded-lg shadow-lg p-6 border border-gray-700">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-white">{{ $title }}</h2>
                @isset($subtitle)
                    <p class="text-sm text-gray-400">{{ $subtitle }}</p>
                @endisset
            </div>

            {{ $slot }}

            @isset($footer)
                <p class="mt-6 text-center text-sm text-gray-400">
                    {!! $footer !!}
                </p>
            @endisset
        </div>
    </section>
</x-layout>