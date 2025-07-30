<!-- dont forget to use this card when you refactor your code -->
<div class="rounded-lg border border-white/15 text-card-foreground shadow-sm card-highlight bg-gradient-dark ">
    <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
        <h3 class="tracking-tight text-sm font-medium">{{ $title }}</h3>
        @if(isset($icon))
            <x-icon :name="$icon" class="w-4 h-4" />
        @endif
    </div>
    <div class="p-6 pt-0">
        <div class="text-2xl font-bold">
            {{ $value }}
        </div>
        <p class="text-xs text-muted font-inter">
            {{ $description }}
        </p>
    </div>
</div>