<x-auth-layout title="Verify Your Email" subtitle="One quick step to activate your sanctuary."
  mode="login"
  :center="true"
  footer='Already verified? <a href="/auth/login" wire:navigate.hover class="text-white font-medium hover:underline hover:text-[#c6b78e]">Sign in</a>'>

  <div class="space-y-4"
    x-data="{
        cooldown: {{ session('status') == 'verification-link-sent' ? 30 : 0 }},
        timer: null,
        init() {
            if (this.cooldown > 0) {
                this.timer = setInterval(() => {
                    this.cooldown--;
                    if (this.cooldown <= 0) {
                        clearInterval(this.timer);
                    }
                }, 1000);
            }
        }
    }">

      @if (session('status') == 'verification-link-sent')
          <div class="flex items-start gap-2.5 text-xs font-medium px-3.5 py-2.5 rounded-xl shadow-sm text-emerald-200 border"
               style="background-color: rgba(16, 185, 129, 0.16); border-color: rgba(16, 185, 129, 0.35);">
              <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>A new verification link has been sent to your email.</span>
          </div>
      @endif

      <div class="flex items-start gap-2.5 text-xs sm:text-sm font-medium px-3.5 py-3 rounded-xl shadow-sm text-blue-200 border"
           style="background-color: rgba(59, 130, 246, 0.12); border-color: rgba(59, 130, 246, 0.28);">
          <svg class="w-4 h-4 text-blue-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
          <div class="leading-relaxed text-gray-200">
              <span>Verification email sent to</span>
              @if ($email)
                  <strong class="text-white">{{ $email }}</strong>.
              @else
                  <span>your inbox.</span>
              @endif
              <span>Click the link in the email (check spam) to verify your account and activate full access.</span>
          </div>
      </div>

      <form method="POST" action="{{ route('verification.send') }}" class="pt-2">
          @csrf
          @if ($email)
              <input type="hidden" name="email" value="{{ $email }}">
          @endif
          <button type="submit"
              :disabled="cooldown > 0"
              :class="cooldown > 0 ? 'opacity-50 cursor-not-allowed bg-gray-200 text-gray-600' : 'bg-white hover:bg-gray-100 text-gray-900 cursor-pointer active:scale-[0.99]'"
              class="w-full py-2.5 px-4 rounded-xl font-semibold transition flex items-center justify-center gap-2 shadow-sm text-sm">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              <span x-show="cooldown <= 0">Resend Verification Email</span>
              <span x-show="cooldown > 0" x-text="'Resend available in ' + cooldown + 's'"></span>
          </button>
      </form>
  </div>
</x-auth-layout>
