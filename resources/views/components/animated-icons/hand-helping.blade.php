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
    <g class="lucide-hand-helping-motion">
        <path d="M11 12h2a2 2 0 1 0 0-4h-3c-.6 0-1.1.2-1.4.6L3 14" />
        <path d="m7 18 1.6-1.4c.3-.4.8-.6 1.4-.6h4c1.1 0 2.1-.4 2.8-1.2l4.6-4.4a2 2 0 0 0-2.75-2.91l-4.2 3.9" />
        <path d="m2 13 6 6" />
    </g>
    <style>
        @keyframes lucide-hand-helping-anim {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(2px, -2px); }
        }
        .group:hover .lucide-hand-helping-motion,
        .lucide-hand-helping-motion:hover {
            animation: lucide-hand-helping-anim 0.5s ease-in-out;
        }
    </style>
</svg>
