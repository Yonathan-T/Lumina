<x-layout>
    <x-form-parent>
        <h2 class="text-2xl font-bold text-white">Set New Password</h2>
        <p class="text-sm text-gray-500">Please enter and confirm your new password.</p>

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="flex flex-col gap-6">
                <x-form-wrapper>
                    <x-form-label for="email">Email</x-form-label>
                    <x-form-input id="email" name="email" type="email" value="{{ old('email', $email) }}" required
                        autofocus />
                    <x-form-error name="email" />
                </x-form-wrapper>

                <x-form-wrapper>
                    <x-form-label for="password">New Password</x-form-label>
                    <x-form-input id="password" name="password" type="password" required />
                    <x-form-error name="password" />
                </x-form-wrapper>

                <x-form-wrapper>
                    <x-form-label for="password_confirmation">Confirm New Password</x-form-label>
                    <x-form-input id="password_confirmation" name="password_confirmation" type="password" required />
                    <x-form-error name="password_confirmation" />
                </x-form-wrapper>
            </div>

            <div class="mt-10 flex justify-end gap-x-4 items-center">
                <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-300">Cancel</a>
                <x-form-button>Reset Password</x-form-button>
            </div>

        </form>
    </x-form-parent>
</x-layout>