@props(['name'])

@error($name)
    <div x-show="!$data.errors || !$data.errors['{{ $name }}']"
         class="flex items-center gap-2 text-xs font-medium mt-3 mb-2 px-3.5 py-2.5 rounded-xl shadow-sm text-red-200 border"
         style="background-color: rgba(239, 68, 68, 0.16); border-color: rgba(239, 68, 68, 0.35);">
        <svg class="w-4 h-4 shrink-0 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
        </svg>
        <span class="leading-relaxed text-red-200">{{ $message }}</span>
    </div>
@enderror

<template x-if="$data.errors && $data.errors['{{ $name }}']">
    <div class="flex items-center gap-2 text-xs font-medium mt-3 mb-2 px-3.5 py-2.5 rounded-xl shadow-sm text-red-200 border"
         style="background-color: rgba(239, 68, 68, 0.16); border-color: rgba(239, 68, 68, 0.35);">
        <svg class="w-4 h-4 shrink-0 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
        </svg>
        <span class="leading-relaxed text-red-200" x-text="Array.isArray($data.errors['{{ $name }}']) ? $data.errors['{{ $name }}'][0] : $data.errors['{{ $name }}']"></span>
    </div>
</template>