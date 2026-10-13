<x-auth-layout title="Sign Up Account" subtitle="Enter your personal data to create your account."
  mode="register"
  footer='Already have an account? <a href="/auth/login" wire:navigate.hover class="text-white font-medium hover:underline hover:text-[#c6b78e]">Sign in</a>'>
  <x-auth-form action="/auth/register" button="Sign Up" social="true">
    <x-auth-field name="name" type="text" label="Full Name" placeholder="eg. John Doe" required autocomplete="name" />
    <x-auth-field name="email" type="email" label="Email" placeholder="eg. johnfrans@gmail.com" required autocomplete="email" />
    <x-auth-field name="password" type="password" label="Password" placeholder="Enter your password" hint="Must be at least 8 characters" required autocomplete="new-password" />
    <x-auth-field name="password_confirmation" type="password" label="Confirm Password" placeholder="Confirm your password" required autocomplete="new-password" />
  </x-auth-form>
</x-auth-layout>