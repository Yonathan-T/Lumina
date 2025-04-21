<!--HERO PART -->
<x-layout>
<section class="py-8">
    <div class="flex justify-between items-stretch bg-white/5 rounded-xl"> 

        <!-- Text Content Column -->
        <div class="flex-1 py-12 px-12 flex flex-col"> 
            <div class="mb-7">  
                <h1 class="font-bold text-4xl mb-5">Journal Smarter, Reflect Deeper</h1>
                <h3 class="text-lg max-w-md mt-2">  
                    Unlock personal growth, mental clarity, and emotional wellness with our intuitive journaling platform.
                </h3>
            </div>
            
          
            <div class="mt-auto">  
                <div class="space-y-5 space-x-2 mb-6"> 
                    <x-buttons href="/login">Get Started</x-buttons>            
                    <x-buttons href="/guide" >Send me a guide</x-buttons>  
                </div>
                <p class="text-sm text-gray-400">We'll Never Share Your Info With Anyone.</p>
            </div>
        </div>

        
        <div class="flex-1 flex items-center justify-end">
            <img src="{{ Vite::asset('resources/images/hero-img.png') }}" 
                 class="max-h-96 w-auto object-contain"  
                 alt="Journaling illustration">
        </div>
    </div>
</section>
<!--BENEFITS -->
<section class="max-w-4xl mx-auto py-12 px-4"> <!-- Main container -->
  <h2 class="text-3xl font-bold text-center mb-10">Why You’ll <x-icons/> Memo Mate</h2>
  
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
            <x-benefit-card title="Effortless Daily Journaling" description="Quick entries with minimal friction." />
            <x-benefit-card title="Smart AI Reflections" description="Discover hidden patterns in your thoughts." />
            <x-benefit-card title="Mood Tracking" description="Visualize your emotional journey over time." />
            <x-benefit-card title="Private & Secure" description="Your thoughts stay yours alone." />
            <x-benefit-card title="Minimalist Design" description="Focus on writing, not distractions." />
            <x-benefit-card title="Daily Reminders" description="Build a consistent habit." />
            <x-benefit-card title="Community Wisdom" description="Optional sharing of anonymized insights." />
            <x-benefit-card title="Always Improving" description="We evolve based on your needs." />
      </div>

</section>

<x-testimonials/>



<footer class="ml-[calc(-4.5rem)] mr-[calc(-4.5rem)] bg-white/5 border-t border-white/10 mt-20 py-12 px-16">
  <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">
    
    <!-- Brand Column -->
    <div class="space-y-4">
      <div class="flex items-center gap-2">
        <x-icons type="heart"/>
        <a href="/">
          <span class="text-xl font-semibold text-[#7c6a54]">Memo Mate</span>
        </a>
      </div>
      <p class="text-white/60 text-sm"><x-icons type="leftQ"/> Your private space for mindful journaling <x-icons type="rightQ"/></p>
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
      <a href="/register" class="flex items-center space-x-2 hover:text-[#c6b78e] transition">
         <span>Get Started</span>
         <x-icons type="login" />
      </a>

    </div>
    <div>
      <h3 class="font-medium mb-4 text-[#7c6a54]">Contact us</h3>
      <a href="https://t.me/+lEIft9tfqhwxNjU8">
        <x-icons type="telegram" :logo="true"/>
      </a>
    </div>
  </div>
  
  
  <!-- Copyright -->
  <div class="max-w-7xl mx-auto pt-8 mt-8 border-t border-white/10 text-center text-white/40 text-sm">
    © 2023 Memo Mate. All rights reserved.
  </div>
</footer>
</x-layout>
