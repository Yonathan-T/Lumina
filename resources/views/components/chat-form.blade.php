@props([
    'wireSubmit' => 'sendMessage',
    'wireModel' => 'newMessage',
    'placeholder' => 'Share your thoughts...',
    'isDisabled' => false,
    'isTyping' => false,
    'submitIcon' => 'send',
    'typingIcon' => 'stop'
])

<form wire:submit="{{ $wireSubmit }}" class="flex items-center space-x-3">
    <div class="flex-1">
        <textarea wire:model="{{ $wireModel }}" placeholder="{{ $placeholder }}"
            class="w-full bg-gray-700 border bg-gradient-dark rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent disabled:opacity-50 overflow-hidden resize-none max-h-[200px]"
            {{ $isDisabled || $isTyping ? 'disabled' : '' }}
            oninput="this.style.height='auto'; this.style.height=(this.scrollHeight)+'px';"
            onkeydown="if(event.key==='Enter' && !event.shiftKey){event.preventDefault(); this.closest('form').dispatchEvent(new Event('submit', {bubbles: true}));}"></textarea>
    </div>
    <div>
        <button type="submit"
            class="cursor-pointer text-white p-3 rounded-xl bg-white/5 hover:border transition-colors flex items-center justify-center"
            {{ $isDisabled || $isTyping ? 'disabled' : '' }}>
            @if($isTyping)
                <x-icon name="{{ $typingIcon }}" class="w-5 h-5 bg-white" />
            @else
                <x-icon name="{{ $submitIcon }}" class="w-5 h-5" />
            @endif
        </button>
    </div>
</form>
