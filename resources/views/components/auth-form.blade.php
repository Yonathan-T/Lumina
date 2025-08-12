<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    {{ $slot }}

    <button type="submit"
        class="w-full py-2 px-4 bg-white text-gray-900 rounded-md font-semibold hover:bg-gray-200 transition">
        {{ $button }}
    </button>

    @isset($social)
        <div class="text-center">
            <p class="text-sm text-gray-400">Or continue with</p>
            <a href="{{ url('auth/google/redirect') }}"
                class="cursor-pointer mt-2 w-full py-2 px-4 bg-white/5 border border-gray-600 rounded-md text-white hover:bg-gray-600 transition flex items-center justify-center">
                <span class="text-xl">
                    <x-icon name="google1" class="w-8 h-8" />
                </span>
            </a>
        </div>
    @endisset
</form>