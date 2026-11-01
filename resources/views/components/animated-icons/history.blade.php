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
    <g class="lucide-history-arrow">
        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
        <path d="M3 3v5h5" />
    </g>
    <line class="lucide-history-hour" x1="12" y1="12" x2="12" y2="7" />
    <line class="lucide-history-minute" x1="12" y1="12" x2="16" y2="14" />
    <style>
        .lucide-history-arrow,
        .lucide-history-hour,
        .lucide-history-minute {
            transform-origin: 12px 12px;
            transform-box: view-box;
        }
        .lucide-history-arrow {
            transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            transform: rotate(0deg);
        }
        .lucide-history-minute {
            transition: transform 0.5s ease-in-out;
            transform: rotate(0deg);
        }
        .lucide-history-hour {
            transform: rotate(0deg);
        }
        @keyframes lucide-history-hour-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(-360deg); }
        }
        .group:hover .lucide-history-arrow,
        .lucide-history-arrow:hover {
            transform: rotate(-50deg);
        }
        .group:hover .lucide-history-minute,
        .lucide-history-minute:hover {
            transform: rotate(-45deg);
        }
        .group:hover .lucide-history-hour,
        .lucide-history-hour:hover {
            animation: lucide-history-hour-spin 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</svg>
