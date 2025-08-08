@if (session('status'))
    <div class="mb-4 font-medium text-sm text-green-600">
        {{ session('status') }}
    </div>
@endif
<x-auth-layout title="Forgot Password" subtitle="Enter your email to receive a password reset link."
    footer='Remembered your password? <a href="/auth/login" class="text-white underline hover:text-[#c6b78e]">Sign in</a>'>
    <x-auth-form action="{{ route('password.email') }}" button="Send Reset Link">
        <x-auth-field name="email" type="email" label="Email" placeholder="Memo@example.com" />
    </x-auth-form>

</x-auth-layout>