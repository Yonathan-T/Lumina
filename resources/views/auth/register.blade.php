<x-auth-layout title="Sign Up" subtitle="Fill in the details below to create your account."
  footer='Already have an account? <a href="/auth/login" class="text-white underline hover:text-[#c6b78e]">Sign in</a>'>
  <x-auth-form action="/auth/register" button="Register" social="true">
    <x-auth-field name="firstName" type="text" label="Full Name" placeholder="John Doe" />
    <x-auth-field name="email" type="email" label="Email" placeholder="Memo@example.com" />
    <x-auth-field name="password" type="password" label="Password" placeholder="********" />
    <x-auth-field name="password_confirmation" type="password" label="Confirm Password" placeholder="********" />
  </x-auth-form>
</x-auth-layout>