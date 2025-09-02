@props(['active' => false, 'section' => null])

<a {{ $attributes->merge([
    'class' => 'relative group pb-1 ' . ($active ? 'text-white' : 'text-gray-400 hover:text-white'),
    'data-section' => $section
]) }}>
    {{ $slot }}
    <span class="absolute bottom-0 left-0 w-full h-px bg-white 
        @if(!$active)
            transform scale-x-0 group-hover:scale-x-100 
        @endif
        transition-transform duration-300 ease-in-out"></span>
</a>