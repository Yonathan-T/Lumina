@props([
    'size' => 28,
    'class' => '',
    'animated' => true,
])

<div {{ $attributes->merge(['class' => 'inline-flex items-center justify-center select-none ' . $class]) }}
     x-data="{
        isAnimating: false,
        timer: null,
        triggerAnimation() {
            if (this.isAnimating) return;
            this.isAnimating = true;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => {
                this.isAnimating = false;
            }, 1000);
        }
     }"
     @mouseenter="triggerAnimation()"
     @click="triggerAnimation()">
    <svg xmlns="http://www.w3.org/2000/svg"
         viewBox="0 0 200 220"
         width="{{ $size }}"
         height="{{ $size }}"
         class="lumi-bot-svg overflow-visible transition-transform duration-300"
         :class="{ 'lumi-is-animating': isAnimating }">
        <defs>
            <style>
                .lumi-bot-svg.lumi-is-animating .lumi-body-group,
                .group:hover .lumi-bot-svg .lumi-body-group {
                    animation: lumiWiggle 1s ease-in-out forwards;
                }
                .lumi-bot-svg.lumi-is-animating .lumi-eye-left,
                .group:hover .lumi-bot-svg .lumi-eye-left {
                    animation: lumiEyeBlinkLeft 0.45s ease-in-out 0.1s forwards;
                }
                .lumi-bot-svg.lumi-is-animating .lumi-eye-right,
                .group:hover .lumi-bot-svg .lumi-eye-right {
                    animation: lumiEyeBlinkRight 0.45s ease-in-out 0.2s forwards;
                }
                .lumi-bot-svg.lumi-is-animating .lumi-antenna-pulse,
                .group:hover .lumi-bot-svg .lumi-antenna-pulse {
                    animation: lumiAntennaGlow 0.9s ease-in-out forwards;
                }

                /* Subtle periodic idle breathing and blink every 5.5s */
                @keyframes lumiIdleBlink {
                    0%, 94%, 98%, 100% { transform: scaleY(1); }
                    96% { transform: scaleY(0.15); }
                }

                .lumi-eye-idle-left {
                    transform-origin: 89px 47px;
                    animation: lumiIdleBlink 5.5s infinite ease-in-out;
                }
                .lumi-eye-idle-right {
                    transform-origin: 111px 47px;
                    animation: lumiIdleBlink 5.5s infinite ease-in-out 0.12s;
                }

                @keyframes lumiWiggle {
                    0% {
                        transform: rotate(0deg) translateY(0) scale(1);
                    }
                    25% {
                        transform: rotate(-4deg) translateY(1.5px) scale(1.04);
                    }
                    55% {
                        transform: rotate(3.5deg) translateY(-1.5px) scale(1.04);
                    }
                    80% {
                        transform: rotate(-1.5deg) translateY(0.5px) scale(1.01);
                    }
                    100% {
                        transform: rotate(0deg) translateY(0) scale(1);
                    }
                }

                @keyframes lumiEyeBlinkLeft {
                    0% { transform: scaleY(1); }
                    40% { transform: scaleY(0.1); }
                    80%, 100% { transform: scaleY(1); }
                }

                @keyframes lumiEyeBlinkRight {
                    0% { transform: scaleY(1); }
                    40% { transform: scaleY(0.1); }
                    80%, 100% { transform: scaleY(1); }
                }

                @keyframes lumiAntennaGlow {
                    0%, 100% {
                        filter: drop-shadow(0 0 0px rgba(96, 165, 250, 0));
                    }
                    50% {
                        filter: drop-shadow(0 0 6px rgba(96, 165, 250, 0.9));
                    }
                }
            </style>
        </defs>

        <!-- Entire Lumi Body (rotates & bobs on trigger) -->
        <g class="lumi-body-group" style="transform-origin: 100px 110px;">
            <!-- Geometric Prism Body -->
            <g transform="translate(100, 125) scale(5.2) rotate(-180) translate(-12, -12)">
                <path d="M13,2l9,13.6L13,22ZM11,2,2,15.6,11,22Z" fill="currentColor"/>
            </g>

            <!-- Head -->
            <circle cx="100" cy="48" r="24" fill="currentColor"/>

            <!-- Left Eye -->
            <circle cx="89" cy="47" r="6" fill="#0d1117"
                    class="lumi-eye-left lumi-eye-idle-left"
                    style="transform-origin: 89px 47px;"/>

            <!-- Right Eye -->
            <circle cx="111" cy="47" r="6" fill="#0d1117"
                    class="lumi-eye-right lumi-eye-idle-right"
                    style="transform-origin: 111px 47px;"/>

            <!-- Antenna -->
            <line x1="114" y1="28" x2="122" y2="14" stroke="currentColor" stroke-width="3.2" stroke-linecap="round"/>
            <circle cx="123" cy="12" r="3.8" fill="currentColor" class="lumi-antenna-pulse"/>

            <!-- Left Arm -->
            <path d="M80 95 L58 112 L48 138" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="58" cy="112" r="3.8" fill="currentColor"/>
            <circle cx="48" cy="141" r="7.5" fill="currentColor"/>

            <!-- Right Arm -->
            <path d="M120 95 L142 112 L152 138" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="142" cy="112" r="3.8" fill="currentColor"/>
            <circle cx="152" cy="141" r="7.5" fill="currentColor"/>

            <!-- Left Leg + Foot -->
            <g stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" fill="none">
                <path d="M88 160 L80 188 L80 198"/>
                <rect x="63" y="198" width="34" height="9" rx="4.5" fill="currentColor" stroke="none"/>
                <circle cx="71" cy="202.5" r="1.6" fill="currentColor" stroke="none"/>
            </g>

            <!-- Right Leg + Foot -->
            <g stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" fill="none">
                <path d="M112 160 L124 188 L124 198"/>
                <rect x="107" y="198" width="34" height="9" rx="4.5" fill="currentColor" stroke="none"/>
                <circle cx="133" cy="202.5" r="1.6" fill="currentColor" stroke="none"/>
            </g>
        </g>
    </svg>
</div>
