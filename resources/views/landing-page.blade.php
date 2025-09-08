<!--HERO PART -->
<x-layout :showSidebar="false">

  <section class="py-5 ">
    <div class="mx-auto flex flex-col lg:flex-row justify-between rounded-xl  max-w-7xl px-4">

      <!-- Text Content Column -->
      <div class="flex-1 py-12 px-8 flex flex-col">
        <div class="mb-7">
          <h1 class="font-playfair text-4xl md:text-5xl lg:text-6xl font-bold leading-none">
            Your story deserves to be written.
          </h1>

          <h2 class="mt-[24px] text-lg md:text-xl text-paper/80">
            <span class="font-playfair italic">Reflect.</span>
            <span class="mx-2">|</span>
            <span class="font-playfair italic">Grow.</span>
            <span class="mx-2">|</span>
            <span class="font-playfair italic">Heal.</span>

          </h2>
          <!--ANIMATion would be nice around here -->
          <p class="mt-[24px]  max-w-md">
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

          <p class="text-sm text-gray-300">We'll never share your Info. Your thoughts stay yours.</p>
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
  <section id="features" class="mt-5  py-12 "> <!-- Main container -->
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

  <x-pricing-section />

  <!-- 
ml-[calc(-4.5rem)] mr-[calc(-4.5rem)] -->
  <footer id="contact" class=" bg-white/5 border-t border-white/10 mt-20 py-12 px-16">
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">

      <!-- Brand Column -->
      <div class="space-y-4">
        <div class="flex items-center gap-2">
          <x-icons type="heart" />
          <a href="/">
            <span class="text-xl font-semibold text-[#7c6a54]">Lumina</span>
          </a>
        </div>
        <p class="text-white/60 text-sm"><x-icons type="leftQ" /> Your private space for mindful journaling <x-icons
            type="rightQ" /></p>
      </div>

      <!-- Quick Links -->
      <div>
        <h3 class="font-medium mb-4 text-[#7c6a54]">Explore</h3>
        <ul class="space-y-2">
          <li class="group">
            <a href="#" class="flex items-center text-white/60 hover:text-white transition">
              <span class="hidden group-hover:inline-block mr-2">
                <x-icons type="hashtag" />
              </span>
              Features
            </a>
          </li>
          <li class="group">
            <a href="#" class="flex items-center text-white/60 hover:text-white transition">
              <span class="hidden group-hover:inline-block mr-2">
                <x-icons type="hashtag" />
              </span>
              Pricing
            </a>
          </li>
          <li class="group">
            <a href="#" class="flex items-center text-white/60 hover:text-white transition">
              <span class="hidden group-hover:inline-block mr-2">
                <x-icons type="hashtag" />
              </span>
              Journal Prompts
            </a>
          </li>

        </ul>
      </div>

      <!-- Legal -->
      <div>
        <h3 class="font-medium mb-4 text-[#7c6a54]">Legal</h3>
        <ul class="space-y-2">
          <li class="group">
            <a href="#" class="flex items-center text-white/60 hover:text-white transition">
              <span class="hidden group-hover:inline-block mr-2">
                <x-icons type="hashtag" />
              </span>
              Privacy Policy
            </a>
          </li>
          <li class="group">
            <a href="#" class="flex items-center text-white/60 hover:text-white transition">
              <span class="hidden group-hover:inline-block mr-2">
                <x-icons type="hashtag" />
              </span>
              Terms
            </a>
          </li>
        </ul>
      </div>

      <!-- CTA & Social -->
      <div class="space-y-4">
        <h3 class="font-medium text-[#7c6a54]">Start Journaling</h3>
        <a href="/auth/register" class="flex items-center space-x-2 hover:text-[#c6b78e] transition">
          <span>Get Started</span>
          <x-icons type="login" />
        </a>

      </div>
      <div>
        <h3 class="font-medium mb-4 text-[#7c6a54]">Follow us</h3>
        <a href="https://t.me/+lEIft9tfqhwxNjU8">
          <x-icons type="telegram" :logo="true" />
        </a>
      </div>
    </div>


    <!-- Copyright -->
    <div class="max-w-7xl mx-auto pt-8 mt-8 border-t border-white/10 text-center text-white/40 text-sm">
      © 2025 Lumina. All rights reserved.
    </div>
  </footer>
</x-layout>