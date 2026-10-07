<form method="POST" action="{{ $action }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
    @csrf
    <div class="space-y-3.5">
        {{ $slot }}
    </div>

    <button type="submit"
        :disabled="submitting"
        class="w-full py-2.5 px-4 bg-white text-gray-900 rounded-xl font-semibold hover:bg-gray-100 active:scale-[0.99] transition disabled:opacity-75 disabled:cursor-wait flex items-center justify-center gap-2 min-h-[42px] shadow-sm cursor-pointer mt-4">
        <svg x-show="submitting" x-cloak class="animate-spin h-5 w-5 text-gray-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span x-text="submitting ? 'Please wait...' : '{{ $button }}'">{{ $button }}</span>
    </button>

    @isset($social)
        <div class="relative my-4">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-white/10"></div>
            </div>
            <div class="relative flex justify-center text-xs">
                <span class="bg-[#0d1117] px-3 text-gray-400">Or</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <a href="{{ url('auth/google/redirect') }}"
                class="cursor-pointer py-2.5 px-4 bg-[#171a21]/80 hover:bg-[#1f2430] border border-white/10 hover:border-white/20 rounded-xl text-white text-sm font-medium transition flex items-center justify-center gap-2.5 group">
                <x-icon name="google" class="w-4 h-4 group-hover:scale-105 transition-transform" />
                <span>Google</span>
            </a>
            <a href="{{ url('auth/github/redirect') }}"
                class="cursor-pointer py-2.5 px-4 bg-[#171a21]/80 hover:bg-[#1f2430] border border-white/10 hover:border-white/20 rounded-xl text-white text-sm font-medium transition flex items-center justify-center gap-2.5 group">
                <x-icon name="github" class="w-4 h-4 text-white group-hover:scale-105 transition-transform" />
                <span>GitHub</span>
            </a>
        </div>
    @endisset
</form>