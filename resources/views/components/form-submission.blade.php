@props($model, $isTyping)
<form wire:submit="{{ $model }}" class="flex items-center space-x-3">
    <div class="flex-1">
        <textarea wire:model="newMessage" placeholder="Share your thoughts..."
            class="w-full bg-gray-700 border bg-gradient-dark rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent disabled:opacity-50 overflow-hidden resize-none max-h-[200px]"
            {{ $isTyping ? 'disabled' : '' }}
            oninput="this.style.height='auto'; this.style.height=(this.scrollHeight)+'px';"
            onkeydown="if(event.key==='Enter' && !event.shiftKey){event.preventDefault(); this.closest('form').dispatchEvent(new Event('submit', {bubbles: true}));}"></textarea>
    </div>
    <div>
        <button type="submit"
            class=" cursor-pointer text-white p-3 rounded-xl bg-white/5 hover:border  transition-colors flex items-center justify-center"
            {{ $isTyping ? 'disabled' : '' }}>
            @if($isTyping)
                <x-icon name="stop" class="w-5 h-5 bg-white" />
            @else
                <x-icon name="send" class="w-5 h-5" />
            @endif
        </button>
    </div>
</form>