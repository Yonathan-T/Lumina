@props(['name'])

@error($name)
    <div class="flex items-center gap-2 text-xs text-red-300 font-medium mt-2.5 mb-1.5 px-3 py-2 rounded-xl bg-red-500/15 border border-red-500/25 shadow-sm">
        <svg class="w-4 h-4 shrink-0 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
        </svg>
        <span class="leading-relaxed">{{ $message }}</span>
    </div>
@enderror