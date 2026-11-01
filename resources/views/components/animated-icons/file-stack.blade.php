<svg {{ $attributes->merge([
    'xmlns' => 'http://www.w3.org/2000/svg',
    'viewBox' => '0 0 24 24',
    'fill' => 'none',
    'stroke' => 'currentColor',
    'stroke-width' => '2',
    'stroke-linecap' => 'round',
    'stroke-linejoin' => 'round',
    'class' => 'inline-block select-none overflow-visible'
]) }}>
    <g class="lucide-file-stack-top">
        <path d="M21 7h-3a2 2 0 0 1-2-2V2" />
        <path d="M21 6v6.5c0 .8-.7 1.5-1.5 1.5h-7c-.8 0-1.5-.7-1.5-1.5v-9c0-.8.7-1.5 1.5-1.5H17Z" />
    </g>
    <path d="M7 8v8.8c0 .3.2.6.4.8.2.2.5.4.8.4H15" />
    <g class="lucide-file-stack-bottom">
        <path d="M3 12v8.8c0 .3.2.6.4.8.2.2.5.4.8.4H11" />
    </g>
    <style>
        .lucide-file-stack-top {
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translate(0, 0);
        }
        .lucide-file-stack-bottom {
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translate(0, 0);
        }
        .group:hover .lucide-file-stack-top,
        .lucide-file-stack-top:hover {
            transform: translate(-3.5px, 3.5px);
        }
        .group:hover .lucide-file-stack-bottom,
        .lucide-file-stack-bottom:hover {
            transform: translate(3.5px, -3.5px);
        }
    </style>
</svg>
