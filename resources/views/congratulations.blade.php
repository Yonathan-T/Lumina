{{-- resources/views/components/thank-you.blade.php --}}
@props(['preview' => false, 'orderDetails' => null])
<x-layout :showSidebar="false" :showNav="false">

    <div class="min-h-screen bg-black relative overflow-hidden flex items-center justify-center">
        <div class="absolute inset-0">
            {{-- Confetti particles --}}
            @for ($i = 0; $i < 50; $i++)
                @php
                    $left = rand(0, 100);
                    $top = rand(0, 100);
                    $animationDelay = rand(0, 3000) / 1000;
                    $animationDuration = 2 + rand(0, 3000) / 1000;
                    $colors = ["#ef4444", "#f97316", "#eab308", "#22c55e", "#06b6d4", "#3b82f6", "#8b5cf6", "#ec4899"];
                    $color = $colors[array_rand($colors)];
                  @endphp
                <div class="absolute animate-pulse"
                    style="left: {{ $left }}%; top: {{ $top }}%; animation-delay: {{ $animationDelay }}s; animation-duration: {{ $animationDuration }}s;">
                    <div class="w-2 h-2 rounded-full" style="background-color: {{ $color }};"></div>
                </div>
            @endfor
        </div>

        <div class="relative z-10 max-w-sm w-full mx-4">
            <div
                class="bg-gray-800/90 backdrop-blur-lg border border-gray-700/50 rounded-3xl p-8 text-center shadow-2xl">
                {{-- Success Icon with celebration --}}
                <div class="flex items-center justify-center gap-2 mb-6">
                    {{-- Assuming you have SVG Blade components for Brain and Sparkles --}}
                    <x-icons name="brain" class="w-6 h-6 text-red-500" />
                    <x-icons name="sparkles" class="w-6 h-6 text-yellow-400" />
                </div>

                {{-- Simple Thank You Message --}}
                <h1 class="text-3xl font-bold text-white mb-4">Thank You!</h1>
                <p class="text-gray-300 mb-8">
                    You now have access to all {{ $preview ? 'Premium' : ($orderDetails->planName ?? 'Premium') }}
                    features.
                </p>

                {{-- Single Action Button --}}
                <a href="{{ url('/') }}"
                    class="block w-full bg-gray-700 hover:bg-gray-600 text-white font-medium py-4 px-6 rounded-2xl transition-all duration-200 mb-6">
                    Done
                </a>

                <p class="text-sm text-gray-400">
                    @if ($preview)
                        A confirmation email will be sent to your inbox
                    @else
                        Your invoice is available in the <span class="text-orange-400">Customer Portal</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</x-layout>