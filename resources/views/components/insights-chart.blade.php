@props([
    'id',
    'title',
    'subtitle' => '',
    'height' => '300px',
])

<div {{ $attributes->merge(['class' => 'card-highlight rounded-lg border border-white/5 bg-gradient-dark p-6']) }}>
    <h3 class="mb-1 text-lg font-semibold text-white">{{ $title }}</h3>
    @if($subtitle)
        <p class="mb-4 text-sm text-gray-400">{{ $subtitle }}</p>
    @endif

    {{-- Canvas is JS-owned: wire:ignore keeps Livewire from morphing it on updates. --}}
    <div data-chart-wrap wire:ignore class="relative" style="height: {{ $height }};">
        <canvas id="{{ $id }}"></canvas>
        <div data-chart-empty style="display: none;"
            class="absolute inset-0 items-center justify-center text-center text-sm text-gray-500">
            Not enough data yet for this view.
        </div>
    </div>
</div>
