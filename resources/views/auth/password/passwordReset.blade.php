@if (session('status'))
    <div class="mb-4 font-medium text-sm text-green-600">
        {{ session('status') }}
    </div>
@endif
<x-auth-layout title="Reset Password" subtitle="Enter your new password below."
    footer='Remembered your password? <a href="/auth/login" class="text-white underline hover:text-[#c6b78e]">Sign in</a>'>
    <x-auth-form action="{{ route('password.update') }}" button="Reset Password">
        {{-- Usually Laravel’s password reset form needs a hidden token --}}
        <input type="hidden" name="token" value="{{ request()->route('token') }}">
        <x-auth-field name="email" type="email" label="Email" value="{{ old('email') ?? request('email') }}" />
        <x-auth-field name="password" type="password" label="New Password" placeholder="********" />
        <x-auth-field name="password_confirmation" type="password" label="Confirm New Password"
            placeholder="********" />
    </x-auth-form>
</x-auth-layout>