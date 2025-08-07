<x-layout>
    <x-form-parent>
        <h2 class="text-2xl font-bold text-white">Forgot Password?</h2>
        <p class="text-sm text-gray-500">Enter your email and we'll send you a link to reset your password.</p>

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="flex flex-col gap-6">
                <!-- Email Address -->
                <x-form-wrapper>
                    <x-form-label for="email">Email</x-form-label>
                    <x-form-input id="email" name="email" type="email" :value="old('email')" required autofocus />
                    <x-form-error name="email" />
                </x-form-wrapper>
            </div>

            <div class="mt-10 flex justify-end gap-x-4 items-center">
                <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-300">Cancel</a>
                <x-form-button>Send Password Reset Link</x-form-button>
            </div>

            <p class="mt-3 text-center text-sm text-gray-400 space-x-2">
                Already have an account?
                <a href="/login" class="text-white underline hover:text-[#c6b78e]">Sign in</a>
            </p>
        </form>
    </x-form-parent>
</x-layout>