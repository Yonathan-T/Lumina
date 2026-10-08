<!--HERO PART -->
<x-layout :showSidebar="false" :isLandingPage="true">

  {{-- Skiper 8 Typography Words Preloader (Dennis Snellenberg Style) --}}
  <div id="words-preloader"
       class="fixed inset-0 z-[99999] bg-[#07090e] flex flex-col items-center justify-center pointer-events-auto select-none overflow-hidden transition-transform duration-700 ease-[cubic-bezier(0.76,0,0.24,1)]">
    <div class="relative z-10 flex items-center text-white font-playfair text-3xl sm:text-5xl md:text-6xl font-medium tracking-wide">
      <span class="inline-block w-3 h-3 rounded-full bg-yellow-400 mr-4 shadow-[0_0_14px_#facc15] animate-pulse"></span>
      <span id="preloader-word-text" class="transition-all duration-150 inline-block">Reflect</span>
    </div>
    
    {{-- Dennis Snellenberg curved curtain SVG bottom edge --}}
    <svg class="absolute -bottom-24 left-0 w-full h-24 fill-[#07090e] pointer-events-none" viewBox="0 0 100 100" preserveAspectRatio="none">
      <path id="preloader-curve" d="M0 0 L100 0 Q50 60 0 0 Z"></path>
    </svg>
  </div>

  <script>
    (function () {
      const preloader = document.getElementById('words-preloader');
      if (!preloader) return;

      const words = ['Reflect', 'Mindfulness', 'Clarity', 'Growth', 'Sanctuary', 'Lumina'];
      const wordEl = document.getElementById('preloader-word-text');
      let index = 0;

      const interval = setInterval(() => {
        index++;
        if (index < words.length) {
          if (wordEl) {
            wordEl.style.opacity = '0';
            wordEl.style.transform = 'translateY(6px)';
            setTimeout(() => {
              wordEl.textContent = words[index];
              wordEl.style.opacity = '1';
              wordEl.style.transform = 'translateY(0)';
            }, 60);
          }
        } else {
          clearInterval(interval);
          setTimeout(() => {
            preloader.style.transform = 'translateY(-100%)';
            preloader.style.transition = 'transform 0.85s cubic-bezier(0.76, 0, 0.24, 1)';
            setTimeout(() => {
              preloader.remove();
            }, 900);
          }, 300);
        }
      }, 230);

      // Failsafe timeout
      setTimeout(() => {
        if (document.getElementById('words-preloader')) {
          preloader.style.transform = 'translateY(-100%)';
          setTimeout(() => preloader.remove(), 900);
        }
      }, 3500);
    })();
  </script>

  @php
    $currentHour = (int) now()->format('H');
    $defaultScene = ($currentHour >= 5 && $currentHour < 12) ? 'alpine-dawn' : (($currentHour >= 12 && $currentHour < 19) ? 'ocean-sunset' : 'night-coast');
  @endphp

  {{-- Full-Page Dynamic Time-Aware ASCII Art Background --}}
  <div class="page-time-container fixed inset-0 pointer-events-none select-none overflow-hidden z-0">
    <ascii-art id="page-time-piece" piece="{{ $defaultScene }}" class="page-time-art absolute inset-0 w-full h-full block">
    </ascii-art>

    {{-- Subtle scanline texture layer --}}
    <div class="absolute inset-0 bg-scanlines opacity-10 pointer-events-none"></div>

    {{-- Atmospheric Dark Tint & Vignette Overlays for Crisp Content Contrast --}}
    <div class="absolute inset-0 bg-[#07090e]/50 pointer-events-none"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-[#0a0c10]/80 via-transparent to-[#0a0c10]/85 pointer-events-none"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-[#0a0c10]/40 via-transparent to-[#0a0c10]/40 pointer-events-none"></div>
  </div>

  {{-- Main Content Layer with guaranteed z-10 stacking context --}}
  <div class="relative z-10 flex flex-col min-h-screen">
    <!-- Dynamic Automatic Time-Aware Hero Section -->
    <section class="relative mt-6 pt-12 md:pt-16 pb-16">
    <div class="relative z-10 mx-auto flex flex-col lg:flex-row justify-between rounded-xl max-w-7xl px-4">

      <!-- Text Content Column -->
      <div class="flex-1 py-12 px-8 flex flex-col">
        <div class="mb-7 relative">
          <!-- BETA BADGE (floating just above the heading) -->
          <div class="absolute -top-3 -left-10
           flex items-center gap-1.5
           bg-[#111]/95 backdrop-blur-sm text-yellow-400 text-[10px] font-bold uppercase tracking-wider
           px-3 py-1 rounded-full
           border border-yellow-600/40
           shadow-xl
           animate-bounce
           whitespace-nowrap
           cursor-pointer
           hover:scale-110 transition-transform">
            <div class="w-1.5 h-1.5 rounded-full bg-yellow-400
             shadow-[0_0_8px_#facc15] animate-ping"></div>
            <span>Beta Release</span>
          </div>

          <h1 class="font-playfair text-4xl md:text-5xl lg:text-6xl font-bold leading-none">
            Your story deserves to be written.
          </h1>

          <h2 class="mt-[24px] text-lg md:text-xl text-paper/80 flex items-center  gap-2">
            <!-- Reflect -->
            <span class="font-playfair italic relative overflow-hidden cursor-pointer group">
              <span class="inline-block transition-transform duration-300 group-hover:scale-105 group-hover:text-white
             before:absolute before:inset-0 before:bg-gradient-to-r before:from-transparent before:via-white/60 before:to-transparent
             before:translate-x-[-100%] before:skew-x-12 before:opacity-0
             group-hover:before:animate-reflect-shine">
                Reflect.
              </span>
            </span>

            <span>|</span>

            <!-- Grow -->
            <span
              class="font-playfair italic inline-block cursor-pointer transition-all duration-300 hover:scale-125 hover:text-green-400">
              Grow.
            </span>

            <span>|</span>

            <!-- Heal -->
            <span
              class="font-playfair italic inline-block cursor-pointer transition-all duration-500 hover:text-pink-400 hover:rotate-1 hover:scale-110 hover:drop-shadow-[0_0_8px_rgba(244,114,182,0.6)]">
              Heal.
            </span>
          </h2>

          <!--ANIMATion would be nice around here -->
          <p
            class="mt-[24px] max-w-md text-paper/80 transition-all duration-1000 ease-out opacity-80 hover:opacity-100 hover:text-white hover:drop-shadow-[0_0_10px_rgba(255,255,200,0.3)]">
            A beautiful, private space for your thoughts, dreams, and reflections.
            Lumina helps you cultivate mindfulness and emotional clarity through journaling.
          </p>


        </div>


        <div class="mt-auto">
          <div class="flex space-x-3 mb-6">
            <x-buttons href="/auth/login" wire:navigate.hover class="flex items-center card-highlight">
              Start Writing Today
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                class="w-4 h-4 ml-2">
                <path d="M5 12h14M12 5l7 7-7 7" />
              </svg>
            </x-buttons>

            <!-- <x-buttons href="/guide">Send me a guide</x-buttons> -->
          </div>

          <p class="mt-10 text-sm text-gray-500">We'll never share your Info. Your thoughts stay yours.</p>
        </div>
      </div>


      <div class="flex-1 flex items-center justify-end">
        <div class="relative animate-float w-full h-96">
          <!-- Main journal card -->
          <div
            class="w-full h-full bg-paper/10 backdrop-blur-sm rounded-xl shadow-2xl border border-paper/20 p-5 transform rotate-4">
            <div class="h-full rounded-lg bg-paper/10 p-6 flex flex-col">
              <div class="flex justify-between mb-6">
                <div class="text-sm ">{{ now()->format('F j')}}</div>
                <div class="text-accent-peach text-sm">Personal</div>
              </div>
              <h3 class="font-playfair text-lg mb-4">Today's Reflection</h3>
              <div class="space-y-2 animate-pulse">
                <div class="h-3 bg-white/15 rounded-full w-full"></div>
                <div class="h-3 bg-white/15 rounded-full w-5/6"></div>
                <div class="h-3 bg-white/15 rounded-full w-full"></div>
                <div class="h-3 bg-white/15 rounded-full w-4/6"></div>
              </div>

              <div class="mt-auto pt-4">
                <div class="flex items-center space-x-2">
                  <div class="w-8 h-8 rounded-full bg-muted-green flex items-center justify-center text-twilight">
                    {{-- Optional: Replace with Smile icon or image --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M14 10h4v4h-4v4h-4v-4H6v-4h4V6h4v4z" />
                    </svg>
                  </div>
                  <span class="text-sm ">Feeling calm today</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Floating journal card -->
          <div
            class="absolute -bottom-10 -right-10 w-64 h-40 bg-paper/10 backdrop-blur-sm rounded-lg shadow-xl border border-paper/20 p-3 transform -rotate-6">
            <div class="h-full rounded bg-paper/10 p-3">
              <div class="flex justify-between mb-3">
                <div class="text-xs ">Jan 6</div>
              </div>
              <div class="space-y-1 animate-pulse">
                <div class="h-2 bg-white/20 rounded-full w-full"></div>
                <div class="h-2 bg-white/20 rounded-full w-3/4"></div>
                <div class="h-2 bg-white/20 rounded-full w-5/6"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
  <!--BENEFITS -->
  <section id="features" class="relative z-10 mt-6 py-12"> <!-- Main container -->
    <div class="text-center max-w-2xl mx-auto mb-6">
      <h2 class="font-playfair text-3xl md:text-4xl font-bold mb-6 tracking-wide text-white">
        Why Lumina?
      </h2>
      <p class="text-white/70">
        Our thoughtfully crafted features make journaling a delightful part of your daily routine.
      </p>
    </div>


    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto px-4">
      <!-- card items go here -->
      <x-benefit-card svg="svg/lock.svg" title="Private & Secure"
        description="Your journal is for your eyes only. Entries are encrypted and your privacy is our priority." />
      <x-benefit-card svg="svg/calendar-days.svg" title="Daily Prompts"
        description="Get inspired with thoughtful prompts tailored to foster self-discovery and reflection." />
      <x-benefit-card svg="svg/smile.svg" title="Mood Tracker"
        description="Visualize your emotional journey and identify patterns for greater self-awareness." />

    </div>

  </section>
  <div class="mt-12 text-center max-w-2xl mx-auto mb-10">
    <h2 class="font-playfair text-3xl md:text-4xl font-bold mb-8 tracking-wide">
      From Our Community
    </h2>
    <p class="text-white/70">
      Hear from people who have transformed their journaling practice with Lumina. </p>
  </div>
  <x-testimonials />

  {{-- <x-pricing-section :products="$products" /> --}}

  <!-- FAQ Section -->
  <section class="py-20 px-4">
    <div class="max-w-4xl mx-auto">
      <div class="text-center mb-12">
        <h2 class="font-playfair text-3xl md:text-4xl font-bold mb-4 tracking-wide">
          Frequently Asked Questions
        </h2>
        <p class="text-white/70 max-w-2xl mx-auto">
          Everything you need to know about Lumina and how it can transform your journaling practice
        </p>
      </div>

      <div class="space-y-4" x-data="{ openFaq: null }">
        <!-- FAQ Item 1 -->
        <div
          class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 overflow-hidden transition-all duration-300"
          :class="openFaq === 1 ? 'shadow-lg' : ''">
          <button @click="openFaq = openFaq === 1 ? null : 1"
            class="w-full px-6 py-5 flex items-center justify-between text-left hover:bg-white/5 transition-colors">
            <span class="font-medium text-lg text-white">Is my data private?</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform duration-300"
              :class="openFaq === 1 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-show="openFaq === 1" x-collapse x-cloak>
            <div class="px-6 pb-5 text-white/70">
              Absolutely. Your entries are encrypted and never shared. We don't read your journals. Your privacy is our
              top priority, and all data is stored securely with industry-standard encryption.
            </div>
          </div>
        </div>

        <!-- FAQ Item 2 -->
        <div
          class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 overflow-hidden transition-all duration-300"
          :class="openFaq === 2 ? 'shadow-lg' : ''">
          <button @click="openFaq = openFaq === 2 ? null : 2"
            class="w-full px-6 py-5 flex items-center justify-between text-left hover:bg-white/5 transition-colors">
            <span class="font-medium text-lg text-white">Can I cancel anytime?</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform duration-300"
              :class="openFaq === 2 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-show="openFaq === 2" x-collapse x-cloak>
            <div class="px-6 pb-5 text-white/70">
              Yes! Cancel with one click from your settings. No questions asked. You'll keep your free account and all
              your entries, you just won't have access to premium features anymore.
            </div>
          </div>
        </div>

        <!-- FAQ Item 3 -->
        <div
          class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 overflow-hidden transition-all duration-300"
          :class="openFaq === 3 ? 'shadow-lg' : ''">
          <button @click="openFaq = openFaq === 3 ? null : 3"
            class="w-full px-6 py-5 flex items-center justify-between text-left hover:bg-white/5 transition-colors">
            <span class="font-medium text-lg text-white">What's the difference between Standard and Pro?</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform duration-300"
              :class="openFaq === 3 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-show="openFaq === 3" x-collapse x-cloak>
            <div class="px-6 pb-5 text-white/70">
              Pro adds unlimited entries, text-to-speech for listening to your journals, unlimited data exports, and
              premium customization options. Standard is perfect for most daily journalers with 100 entries per month
              and AI chat support.
            </div>
          </div>
        </div>

        <!-- FAQ Item 4 -->
        <div
          class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 overflow-hidden transition-all duration-300"
          :class="openFaq === 4 ? 'shadow-lg' : ''">
          <button @click="openFaq = openFaq === 4 ? null : 4"
            class="w-full px-6 py-5 flex items-center justify-between text-left hover:bg-white/5 transition-colors">
            <span class="font-medium text-lg text-white">How does the AI work?</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform duration-300"
              :class="openFaq === 4 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-show="openFaq === 4" x-collapse x-cloak>
            <div class="px-6 pb-5 text-white/70">
              Lumi, our AI assistant, reads your entries (with your permission) and provides thoughtful, context-aware
              responses to help you reflect deeper. It remembers your previous conversations and journal themes to offer
              personalized insights and prompts.
            </div>
          </div>
        </div>

        <!-- FAQ Item 5 -->
        <div
          class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 overflow-hidden transition-all duration-300"
          :class="openFaq === 5 ? 'shadow-lg' : ''">
          <button @click="openFaq = openFaq === 5 ? null : 5"
            class="w-full px-6 py-5 flex items-center justify-between text-left hover:bg-white/5 transition-colors">
            <span class="font-medium text-lg text-white">Can I export my data?</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform duration-300"
              :class="openFaq === 5 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-show="openFaq === 5" x-collapse x-cloak>
            <div class="px-6 pb-5 text-white/70">
              Free users can manually copy their entries. Standard plan users get 4 full JSON exports per year, and Pro
              users can export their data anytime with unlimited exports. Your data is always yours.
            </div>
          </div>
        </div>

        <!-- FAQ Item 6 -->
        <div
          class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 overflow-hidden transition-all duration-300"
          :class="openFaq === 6 ? 'shadow-lg' : ''">
          <button @click="openFaq = openFaq === 6 ? null : 6"
            class="w-full px-6 py-5 flex items-center justify-between text-left hover:bg-white/5 transition-colors">
            <span class="font-medium text-lg text-white">What if I exceed my monthly entry limit?</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform duration-300"
              :class="openFaq === 6 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-show="openFaq === 6" x-collapse x-cloak>
            <div class="px-6 pb-5 text-white/70">
              You'll receive a friendly notification encouraging you to upgrade. Your existing entries are never locked
              or deleted—you just won't be able to create new ones until the next month or you upgrade your plan.
            </div>
          </div>
        </div>

        <!-- FAQ Item 7 -->
        <div
          class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 overflow-hidden transition-all duration-300"
          :class="openFaq === 7 ? 'shadow-lg' : ''">
          <button @click="openFaq = openFaq === 7 ? null : 7"
            class="w-full px-6 py-5 flex items-center justify-between text-left hover:bg-white/5 transition-colors">
            <span class="font-medium text-lg text-white">Is there a mobile app?</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform duration-300"
              :class="openFaq === 7 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-show="openFaq === 7" x-collapse x-cloak>
            <div class="px-6 pb-5 text-white/70">
              Not yet, but our web app works beautifully on mobile browsers and is fully responsive. You can add it to
              your home screen for a native app-like experience. A dedicated native app is coming in 2025!
            </div>
          </div>
        </div>

        <!-- FAQ Item 8 -->
        <div
          class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 overflow-hidden transition-all duration-300"
          :class="openFaq === 8 ? 'shadow-lg' : ''">
          <button @click="openFaq = openFaq === 8 ? null : 8"
            class="w-full px-6 py-5 flex items-center justify-between text-left hover:bg-white/5 transition-colors">
            <span class="font-medium text-lg text-white">Do you offer refunds?</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform duration-300"
              :class="openFaq === 8 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-show="openFaq === 8" x-collapse x-cloak>
            <div class="px-6 pb-5 text-white/70">
              Yes, we offer a 30-day money-back guarantee on all paid plans. If Lumina isn't right for you, just contact
              support within 30 days of your purchase for a full refund.
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 
ml-[calc(-4.5rem)] mr-[calc(-4.5rem)] -->

  <!-- Atmospheric Footer (Shares main page time art with frosted glass blur) -->
  <footer id="contact" class="relative overflow-hidden border-t border-white/15 mt-24 footer-atmospheric-blur">
    <!-- Extra Ambient Blur Backdrop Layer over the Page Art -->
    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-[#06080e]/25 to-[#06080e]/60 pointer-events-none"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-16">
      <!-- Main Footer Content -->
      <div class="py-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-12">

        <!-- Brand Column -->
        <div class="lg:col-span-2 space-y-6">
          <div class="flex items-center gap-2">
           <svg class="w-10 h-10 rotate-[-45deg] hover:rotate-[720deg] transition-all duration-500" fill="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <path d="M13,2l9,13.6L13,22ZM11,2,2,15.6,11,22Z"></path>
                    </g>
                </svg>
            <a href="/">
              <span class="text-2xl font-playfair font-bold text-[#7c6a54]">Lumina</span>
            </a>
          </div>
          <p class="text-white/70 text-sm leading-relaxed max-w-sm">
            <x-icons type="leftQ" class="inline" />
            Your private sanctuary for mindful journaling. Reflect, grow, and heal through the power of written words.
            <x-icons type="rightQ" class="inline" />
          </p>

          <!-- Social Links -->
          <div id="#contact" class="flex items-center gap-4">
            <a href="https://t.me/+lEIft9tfqhwxNjU8"
              class="w-10 h-10 rounded-full bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 flex items-center justify-center transition-all group">
              <x-icons type="telegram" :logo="true" class="group-hover:scale-110 transition-transform hover:rotate-[360deg]" />
            </a>
                    <!-- GitHub Star Button (Compact & Sleek) -->
            <a href="https://github.com/Yonathan-T/Lumina" target="_blank"
               class="group relative inline-flex items-center gap-3 px-3.5 py-2 bg-gradient-to-br from-gray-900/90 via-[#0d1117]/90 to-gray-900/90 
                      rounded-xl border border-white/10 backdrop-blur-xl shadow-lg 
                      hover:shadow-yellow-500/20 hover:border-yellow-500/30 
                      transition-all duration-300 hover:-translate-y-0.5 
                      overflow-hidden cursor-pointer">

              <!-- Background glow -->
              <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                <div class="absolute inset-0 bg-yellow-500/10 blur-xl"></div>
              </div>

              <!-- Shine sweep -->
              <div class="absolute inset-0 -translate-x-full group-hover:translate-x-full 
                          transition-transform duration-1000 ease-linear 
                          bg-gradient-to-r from-transparent via-white/15 to-transparent skew-x-12"></div>

              <!-- GitHub Icon -->
              <div class="z-10 shrink-0">
                <x-icon name="github" class="w-5 h-5 text-gray-300 group-hover:text-white transition-all duration-300 group-hover:scale-110" />
              </div>

              <!-- Text + Star Count -->
              <div class="z-10 flex flex-col text-left justify-center">
                <p class="text-gray-400 text-[10px] font-medium tracking-wider uppercase leading-tight group-hover:text-gray-200 transition-colors">
                  Star on GitHub
                </p>

                <div class="flex items-center gap-1.5 pt-0.5">
                  <p class="text-xs sm:text-sm font-bold tracking-tight text-white group-hover:text-yellow-400 transition-colors leading-none">
                    {{ $stars }}
                  </p>

                  <div class="relative inline-flex items-center">
                    <svg class="w-3.5 h-3.5 text-yellow-400 fill-current transition-all duration-300 group-hover:scale-115 drop-shadow-[0_0_4px_rgba(250,204,21,0.5)]"
                         viewBox="0 0 24 24">
                      <path d="M12 .587l3.668 7.431 8.332 1.209-6 5.854 1.416 8.262L12 19.897l-7.416 3.897 1.416-8.262-6-5.854 8.332-1.209z"/>
                    </svg>
                  </div>
                </div>
              </div>
            </a>
          </div>
        </div>

        <!-- Product Column -->
        <div>
          <h3 class="font-semibold mb-5 text-[#7c6a54] tracking-wide">Product</h3>
          <ul class="space-y-3">
            <li>
              <a href="#features"
                class="text-white/60 hover:text-white text-sm transition-colors flex items-center group">
                <span class="w-0 group-hover:w-2 h-0.5 bg-[#7c6a54] mr-0 group-hover:mr-2 transition-all"></span>
                Features
              </a>
            </li>
            {{-- <li>
              <a href="#pricing"
                class="text-white/60 hover:text-white text-sm transition-colors flex items-center group">
                <span class="w-0 group-hover:w-2 h-0.5 bg-[#7c6a54] mr-0 group-hover:mr-2 transition-all"></span>
                Pricing
              </a>
            </li> --}}
            <li>
              <a href="#testimonials"
                class="text-white/60 hover:text-white text-sm transition-colors flex items-center group">
                <span class="w-0 group-hover:w-2 h-0.5 bg-[#7c6a54] mr-0 group-hover:mr-2 transition-all"></span>
                Testimonials
              </a>
            </li>
            <li>
              <a href="/auth/register" wire:navigate.hover
                class="text-white/60 hover:text-white text-sm transition-colors flex items-center group">
                <span class="w-0 group-hover:w-2 h-0.5 bg-[#7c6a54] mr-0 group-hover:mr-2 transition-all"></span>
                Get Started
              </a>
            </li>
          </ul>
        </div>

        <!-- Resources Column -->
        <div>
          <h3 class="font-semibold mb-5 text-[#7c6a54] tracking-wide">Resources</h3>
          <ul class="space-y-3">
            <li>
              <a href="#" class="text-white/60 hover:text-white text-sm transition-colors flex items-center group">
                <span class="w-0 group-hover:w-2 h-0.5 bg-[#7c6a54] mr-0 group-hover:mr-2 transition-all"></span>
                Journal Prompts
              </a>
            </li>
            <li>
              <a href="{{ route('blogs.index') }}"
                class="text-white/60 hover:text-white text-sm transition-colors flex items-center group">
                <span class="w-0 group-hover:w-2 h-0.5 bg-[#7c6a54] mr-0 group-hover:mr-2 transition-all"></span>
                Blog
              </a>
            </li>

          </ul>
        </div>

        <!-- Legal Column -->
        <div>
          <h3 class="font-semibold mb-5 text-[#7c6a54] tracking-wide">Legal</h3>
          <ul class="space-y-3">
            <li>
              <a href="{{ route('privacy') }}" wire:navigate.hover
                class="text-white/60 hover:text-white text-sm transition-colors flex items-center group">
                <span class="w-0 group-hover:w-2 h-0.5 bg-[#7c6a54] mr-0 group-hover:mr-2 transition-all"></span>
                Privacy Policy
              </a>
            </li>
            <li>
              <a href="{{ route('terms') }}" wire:navigate.hover
                class="text-white/60 hover:text-white text-sm transition-colors flex items-center group">
                <span class="w-0 group-hover:w-2 h-0.5 bg-[#7c6a54] mr-0 group-hover:mr-2 transition-all"></span>
                Terms of Service
              </a>
            </li>
            <li>
              <a href="{{ route('security') }}" wire:navigate.hover
                class="text-white/60 hover:text-white text-sm transition-colors flex items-center group">
                <span class="w-0 group-hover:w-2 h-0.5 bg-[#7c6a54] mr-0 group-hover:mr-2 transition-all"></span>
                Security
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="py-8 border-t border-white/10">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 text-xs">
          <!-- Copyright -->
          <div class="text-white/40">
            © {{ date('Y') }} Lumina. All rights reserved.
          </div>
        </div>
      </div>
    </div>
  </footer>
  </div>

  <style>
    /* Full-Page Time-Aware Atmosphere Backdrop */
    .page-time-container,
    .hero-time-container {
      background-color: #07090e;
    }
    .page-time-art,
    .hero-time-art {
      width: 100% !important;
      height: 100% !important;
      opacity: 0.65;
      filter: blur(1.8px);
      transform: scale(1.03);
      transform-origin: center;
    }
    .page-time-art canvas,
    .hero-time-art canvas {
      width: 100% !important;
      height: 100% !important;
      aspect-ratio: auto !important;
      object-fit: cover !important;
      object-position: center top !important;
      display: block !important;
      filter: blur(1.8px);
    }
    .footer-atmospheric-blur {
      backdrop-filter: blur(24px) saturate(180%);
      -webkit-backdrop-filter: blur(24px) saturate(180%);
      background-color: rgba(6, 8, 14, 0.45);
    }

    /* Lenis Smooth Momentum Scroller Styles */
    html.lenis, html.lenis body {
      height: auto;
    }
    .lenis.lenis-smooth {
      scroll-behavior: auto !important;
    }
    .lenis.lenis-smooth [data-lenis-prevent] {
      overscroll-behavior: contain;
    }
    .lenis.lenis-stopped {
      overflow: hidden;
    }
    .lenis.lenis-scrolling iframe {
      pointer-events: none;
    }

    /* Completely hide native browser scrollbars (custom Skiper 95 indicator active) */
    html, body {
      scrollbar-width: none !important; /* Firefox */
      -ms-overflow-style: none !important; /* IE & Edge */
    }
    html::-webkit-scrollbar,
    body::-webkit-scrollbar,
    *::-webkit-scrollbar {
      display: none !important; /* Chrome, Safari, Opera, Edge */
      width: 0 !important;
      height: 0 !important;
      background: transparent !important;
    }
  </style>

  <!-- ASCII.rest Web Component Loader & Automatic Time-Aware Sync -->
  <script type="module" src="https://ascii.rest/ascii.js"></script>
  <script>
    (function () {
      function syncHeroTimeScene() {
        const hour = new Date().getHours();
        const piece = (hour >= 5 && hour < 12) ? 'alpine-dawn' : ((hour >= 12 && hour < 19) ? 'ocean-sunset' : 'night-coast');
        const el = document.getElementById('page-time-piece') || document.getElementById('hero-time-piece');
        if (el && el.getAttribute('piece') !== piece) {
          el.setAttribute('piece', piece);
        }
      }

      syncHeroTimeScene();
      document.addEventListener('livewire:navigated', syncHeroTimeScene);
    })();
  </script>

  {{-- Skiper 95 Vertical Scroll Progress Indicator --}}
  <x-skiper95-scroll-progress />

  <!-- Lenis Silky Smooth Momentum Scroller -->
  <script src="/lenis.min.js"></script>
  <script>
    (function () {
      function initLenis() {
        if (typeof Lenis === 'undefined') return;
        if (window.__luminaLenis) {
          try { window.__luminaLenis.destroy(); } catch (e) {}
        }

        const lenis = new Lenis({
          lerp: 0.07,              // Buttery-smooth inertial deceleration
          wheelMultiplier: 0.72,   // Gentle, controlled scroll speed (never frantic or too fast)
          touchMultiplier: 1.1,
          smoothWheel: true,
          syncTouch: false,
          orientation: 'vertical',
          gestureOrientation: 'vertical',
          autoResize: true,
        });

        window.__luminaLenis = lenis;

        // Smooth anchor link scrolling for #features, #contact, etc.
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
          anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId && targetId !== '#') {
              const targetEl = document.querySelector(targetId);
              if (targetEl) {
                e.preventDefault();
                lenis.scrollTo(targetEl, { offset: -40, duration: 1.2 });
              }
            }
          });
        });

        function raf(time) {
          if (window.__luminaLenis === lenis) {
            lenis.raf(time);
            requestAnimationFrame(raf);
          }
        }
        requestAnimationFrame(raf);
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLenis);
      } else {
        initLenis();
      }
      document.addEventListener('livewire:navigated', initLenis);
    })();
  </script>
</x-layout>