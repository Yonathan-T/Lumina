<nav {{ $attributes->merge(['class' => "mx-3 sm:mx-8 md:mx-16 lg:mx-30 xl:mx-30 max-w-9xl px-3 sm:px-4 py-2.5 sm:py-4 flex justify-between items-center border rounded-2xl sm:rounded-4xl border-white/10 backdrop-blur-2xl sticky top-3 sm:top-5 z-100 relative"]) }}>
    {{ $slot }}
</nav>