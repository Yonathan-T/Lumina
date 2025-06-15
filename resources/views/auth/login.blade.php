<x-layout :showNav="false">
  <x-form-parent>
    <h2 class="text-2xl font-bold text-white">Login</h2>
    <p class="text-sm text-gray-500">Enter your email and password below to login to your account.</p>
    <form method="POST" action="/login">
      @csrf
      <div class="flex flex-col gap-6">
        <x-form-wrapper>
          <x-form-label for="email">Email</x-form-label>
          <x-form-input id="email" name="email" type="email" />
          <x-form-error name="email" />
        </x-form-wrapper>
        <x-form-wrapper>
          <x-form-label for="password">Password</x-form-label>
          <x-form-input id="password" name="password" type="password" />
          <x-form-error name="password" />
        </x-form-wrapper>

      </div>

      <div class="mt-10 flex justify-end gap-x-4 items-center">
        <a href="/" class="text-sm font-semibold text-gray-300 ">Cancel</a>
        <x-form-button>Login</x-form-button>
      </div>
    </form>

    <p class="mt-6 text-center text-sm text-gray-400 space-x-2">
      Don’t have an account?
      <a href="/register" class="text-white underline hover:text-[#c6b78e]">Sign up</a>
    </p>
  </x-form-parent>
</x-layout>