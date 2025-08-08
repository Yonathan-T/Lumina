<x-auth-layout title="Sign In" subtitle="Enter your email and password below to login to your account."
  footer='Don’t have an account? <a href="/auth/register" class="text-white underline hover:text-[#c6b78e]">Sign up</a>'>
  <x-auth-form action="/auth/login" button="Log in" social="true">
    <x-auth-field name="email" type="email" label="Email" placeholder="Memo@example.com" />

    <div>
      <div class="flex justify-between items-center">
        <label for="password" class="block text-sm font-medium text-gray-300">Password</label>
        <a href="{{ route('password.request') }}" class="text-sm text-gray-400 hover:text-gray-300">Forgot your
          password?</a>
      </div>
      <input id="password" name="password" type="password"
        class="mt-1 w-full px-4 py-2 bg-white/5 border border-gray-600 rounded-md text-white text-sm placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-white-500"
        placeholder="Password" />
      <x-form-error name="password" />
    </div>

    <div class="flex items-center">
      <input id="remember" name="remember" type="checkbox"
        class="h-4 w-4 accent-black border-gray-600 rounded bg-white/5 focus:ring-white-500" />
      <label for="remember" class="ml-2 block text-sm text-gray-300">Remember me</label>
    </div>
  </x-auth-form>
</x-auth-layout>