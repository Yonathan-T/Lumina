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
    <path class="lucide-zap-path" pathLength="100" d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z" />
    <style>
        .lucide-zap-path {
            stroke-dasharray: 100;
            stroke-dashoffset: 0;
            opacity: 1;
        }
        @keyframes lucide-zap-anim {
            0% {
                stroke-dashoffset: 100;
                opacity: 0;
            }
            15% {
                opacity: 1;
            }
            100% {
                stroke-dashoffset: 0;
                opacity: 1;
            }
        }
        .group:hover .lucide-zap-path,
        .lucide-zap-path:hover {
            animation: lucide-zap-anim 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</svg>
