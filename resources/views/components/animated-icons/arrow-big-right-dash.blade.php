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
    <path class="lucide-arrow-dash" d="M5 9v6" />
    <path class="lucide-arrow-body" d="M9 9h3V5l7 7-7 7v-4H9V9z" />
    <style>
        @keyframes lucide-arrow-dash-anim {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(1.5px); }
        }
        @keyframes lucide-arrow-body-anim {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(3px); }
        }
        .group:hover .lucide-arrow-dash,
        .group\/btn:hover .lucide-arrow-dash,
        button:hover .lucide-arrow-dash,
        .lucide-arrow-dash:hover {
            animation: lucide-arrow-dash-anim 0.4s ease-in-out;
        }
        .group:hover .lucide-arrow-body,
        .group\/btn:hover .lucide-arrow-body,
        button:hover .lucide-arrow-body,
        .lucide-arrow-body:hover {
            animation: lucide-arrow-body-anim 0.4s ease-in-out;
        }
    </style>
</svg>
