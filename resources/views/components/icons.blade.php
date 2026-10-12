@props(['type' => 'heart', 'logo' => false])

@if($type === 'leftQ')
    <span {{ $attributes->merge(['class' => 'text-[#c6b78e] font-serif text-base leading-none select-none']) }}>&ldquo;</span>
@elseif($type === 'rightQ')
    <span {{ $attributes->merge(['class' => 'text-[#c6b78e] font-serif text-base leading-none select-none']) }}>&rdquo;</span>
@elseif($type === 'telegram')
    <svg {{ $attributes->merge(['class' => 'w-5 h-5 fill-current text-[#c6b78e] inline-block']) }} viewBox="0 0 24 24">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.75-.55 2.95-1.28 4.91-2.13 5.89-2.55 2.8-1.16 3.39-1.37 3.77-1.37.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
    </svg>
@elseif($type === 'heart')
    <svg {{ $attributes->merge(['class' => 'w-5 h-5 fill-current text-[#c6b78e] inline-block']) }} viewBox="0 0 24 24">
        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
    </svg>
@elseif($type === 'check')
    <svg {{ $attributes->merge(['class' => 'w-5 h-5 fill-current text-[#c6b78e] inline-block']) }} viewBox="0 0 24 24">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
    </svg>
@elseif($type === 'login')
    <svg {{ $attributes->merge(['class' => 'w-5 h-5 fill-none stroke-current text-[#c6b78e] inline-block']) }} stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
    </svg>
@elseif($type === 'search')
    <svg {{ $attributes->merge(['class' => 'w-5 h-5 fill-none stroke-current text-[#c6b78e] inline-block']) }} stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
    </svg>
@elseif($type === 'hashtag')
    <svg {{ $attributes->merge(['class' => 'w-5 h-5 fill-none stroke-current text-[#c6b78e] inline-block']) }} stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
    </svg>
@else
    <span {{ $attributes->merge(['class' => 'inline-block text-[#c6b78e]']) }}></span>
@endif