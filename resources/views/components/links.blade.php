<a {{ $attributes->merge(['class' => 'relative group pb-1']) }}>
    {{ $slot }}
    <span
        class="absolute bottom-0 left-1/2 w-0 h-px bg-white group-hover:w-full group-hover:left-0 transition-all duration-300"></span>
</a>