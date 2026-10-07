<x-auth-layout title="Sign In" subtitle="Enter your personal data to access your sanctuary."
  mode="login"
  footer='Don’t have an account? <a href="/auth/register" wire:navigate.hover class="text-white font-medium hover:underline hover:text-[#c6b78e]">Sign up</a>'>
  <x-auth-form action="/auth/login" button="Sign In" social="true">
    <x-auth-field name="email" type="email" label="Email" placeholder="eg. memo@example.com" />

    <x-auth-field name="password" type="password" label="Password" placeholder="Enter your password" />

    <div class="flex items-center justify-between text-xs pt-1">
      <label class="flex items-center gap-2 text-gray-300 cursor-pointer select-none">
        <input id="remember" name="remember" type="checkbox"
          class="h-4 w-4 rounded bg-[#171a21] border-white/20 text-indigo-500 focus:ring-0 cursor-pointer" />
        <span>Remember me</span>
      </label>
      <a href="{{ route('password.request') }}" class="text-gray-400 hover:text-white transition-colors">
        Forgot password?
      </a>
    </div>
  </x-auth-form>
</x-auth-layout>