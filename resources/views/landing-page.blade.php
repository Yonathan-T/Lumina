<!--HERO PART -->
<x-layout :showSidebar="false" :isLandingPage="true">

  <!-- Decorative blur patterns for landing page only -->
  <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
    <div class="absolute -top-20 -right-20 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>
    <div class="absolute top-1/4 -left-32 w-80 h-80 bg-teal-500/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-1/4 -right-32 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-20 left-1/4 w-72 h-72 bg-pink-500/10 rounded-full blur-3xl"></div>
    <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-indigo-500/5 rounded-full blur-3xl"></div>
  </div>

  <section class="py-6 relative z-10">
    <div class="mx-auto flex flex-col lg:flex-row justify-between rounded-xl  max-w-7xl px-4">

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
            <x-buttons href="/auth/login" class="flex items-center card-highlight">
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
  <section id="features" class="mt-3  py-12 "> <!-- Main container -->
    <div class="text-center max-w-2xl mx-auto mb-6">
      <h2 class="font-playfair text-3xl md:text-4xl font-bold mb-6 tracking-wide">
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

  <x-pricing-section :products="$products" />

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

  <!-- Professional Footer -->
  <footer id="contact" class="bg-gradient-to-b from-ink to-[#0a0c10] border-t border-white/10 mt-20">
    <div class="max-w-7xl mx-auto px-6 lg:px-16">
      <!-- Main Footer Content -->
      <div class="py-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-12">

        <!-- Brand Column -->
        <div class="lg:col-span-2 space-y-6">
          <div class="flex items-center gap-2">
            <x-icons type="heart" />
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
              <x-icons type="telegram" :logo="true" class="group-hover:scale-110 transition-transform" />
            </a>
            <!-- Add more social links as needed -->
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
            <li>
              <a href="#pricing"
                class="text-white/60 hover:text-white text-sm transition-colors flex items-center group">
                <span class="w-0 group-hover:w-2 h-0.5 bg-[#7c6a54] mr-0 group-hover:mr-2 transition-all"></span>
                Pricing
              </a>
            </li>
            <li>
              <a href="#testimonials"
                class="text-white/60 hover:text-white text-sm transition-colors flex items-center group">
                <span class="w-0 group-hover:w-2 h-0.5 bg-[#7c6a54] mr-0 group-hover:mr-2 transition-all"></span>
                Testimonials
              </a>
            </li>
            <li>
              <a href="/auth/register"
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
              <a href="#" class="text-white/60 hover:text-white text-sm transition-colors flex items-center group">
                <span class="w-0 group-hover:w-2 h-0.5 bg-[#7c6a54] mr-0 group-hover:mr-2 transition-all"></span>
                Privacy Policy
              </a>
            </li>
            <li>
              <a href="#" class="text-white/60 hover:text-white text-sm transition-colors flex items-center group">
                <span class="w-0 group-hover:w-2 h-0.5 bg-[#7c6a54] mr-0 group-hover:mr-2 transition-all"></span>
                Terms of Service
              </a>
            </li>
            <li>
              <a href="#" class="text-white/60 hover:text-white text-sm transition-colors flex items-center group">
                <span class="w-0 group-hover:w-2 h-0.5 bg-[#7c6a54] mr-0 group-hover:mr-2 transition-all"></span>
                Cookie Policy
              </a>
            </li>
            <li>
              <a href="#" class="text-white/60 hover:text-white text-sm transition-colors flex items-center group">
                <span class="w-0 group-hover:w-2 h-0.5 bg-[#7c6a54] mr-0 group-hover:mr-2 transition-all"></span>
                Security
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="py-8 border-t border-white/10">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
          <!-- Copyright -->
          <div class="text-white/40 text-sm">
            © {{ date('Y') }} Lumina. All rights reserved.
          </div>

          <!-- Additional Links -->
          <div class="flex items-center gap-6 text-sm">
            <a href="#" class="text-white/40 hover:text-white/70 transition-colors">Sitemap</a>
            <a href="#" class="text-white/40 hover:text-white/70 transition-colors">Accessibility</a>
            <a href="#" class="text-white/40 hover:text-white/70 transition-colors">Status</a>
          </div>

          <!-- Trust Badges / Certifications (Optional) -->
          <div class="flex items-center gap-3">
            <div class="px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs text-white/60">
              🔒 Encrypted
            </div>

          </div>
        </div>
      </div>
    </div>
  </footer>
</x-layout>