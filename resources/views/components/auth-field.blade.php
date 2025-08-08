@props([
    'name',
    'type' => 'text',
    'label',
    'placeholder' => '',
    'value' => old($name),
])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-300">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
        value="{{ $value }}"
        class="mt-1 w-full px-4 py-2 bg-white/5 border border-gray-600 rounded-md text-white text-sm placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-white-500"
        placeholder="{{ $placeholder }}" />
    <x-form-error name="{{ $name }}" />
</div>
