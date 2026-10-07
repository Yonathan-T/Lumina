@props([
    'name',
    'type' => 'text',
    'label',
    'placeholder' => '',
    'value' => old($name),
    'hint' => null,
])

<div x-data="{ show: false }">
    <div class="flex items-center justify-between">
        <label for="{{ $name }}" class="block text-xs font-medium text-gray-300">{{ $label }}</label>
        @if($hint)
            <span class="text-xs text-gray-400">{{ $hint }}</span>
        @endif
    </div>
    
    <div class="relative mt-1.5">
        <input id="{{ $name }}" name="{{ $name }}" 
            :type="'{{ $type }}' === 'password' ? (show ? 'text' : 'password') : '{{ $type }}'"
            value="{{ $value }}"
            class="w-full px-3.5 py-2.5 bg-[#171a21]/90 hover:bg-[#1c202a] border border-white/10 hover:border-white/20 focus:border-white/30 rounded-xl text-white text-sm placeholder:text-gray-500 focus:outline-none transition-all {{ $type === 'password' ? 'pr-10' : '' }}"
            placeholder="{{ $placeholder }}" />

        @if($type === 'password')
            <button type="button" @click="show = !show"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-white transition-colors focus:outline-none"
                tabindex="-1">
                <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg x-show="show" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                </svg>
            </button>
        @endif
    </div>
    <x-form-error name="{{ $name }}" />
</div>
