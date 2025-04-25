
       <div class="flex flex-col gap-6 p-6 w-full max-w-4xl mx-auto text-white">
  <!-- Header -->
  <div>
    <h1 class="text-2xl font-semibold">Welcome back, {{ auth()->user()->name }}!</h1>
    <p class="text-sm text-zinc-400 mt-1">
      "Journaling is the art of listening to yourself."
    </p>
  </div>

  <!-- Quick Access Bar -->
  <div class="flex gap-4">
    <button class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-2xl shadow">✏️ New Entry</button>
    <button class="bg-zinc-800 hover:bg-zinc-700 text-white px-4 py-2 rounded-2xl shadow">📅 Calendar</button>
    <button class="bg-zinc-800 hover:bg-zinc-700 text-white px-4 py-2 rounded-2xl shadow">📊 Stats</button>
  </div>

  <!-- Today's Prompt -->
  <div class="bg-white/5 border border-[#c6b78e]/10 hover:border-[#c6b78e] rounded-xl p-5 shadow">
      
      <h2 class="text-lg font-medium mb-2">Today's Prompt</h2>
      <p class="text-zinc-300 italic mb-3">“What’s one thing you’re grateful for today?”</p>
      <x-form-input />
      <textarea
      placeholder="Start writing..."
      class="mt-5 w-full h-32 bg-zinc-900 rounded-xl p-4 text-sm text-white border border-zinc-700 focus:outline-none focus:ring-2 focus:ring-amber-500 resize-none"
      ></textarea>
    </div>
    
    <!-- Recent Entries -->
    <div class="bg-white/5 border border-[#c6b78e]/10 hover:border-[#c6b78e] rounded-xl p-5 shadow">
    <h2 class="text-lg font-medium mb-2">Recent Entries</h2>
    <div class="text-zinc-400 italic text-sm py-8 text-center">
      <p>No entries yet!</p>
      <button class="mt-4 px-4 py-2 bg-amber-600 hover:bg-amber-700 rounded-2xl text-white shadow">
        Explore Templates
      </button>
    </div>
  </div>

  <!-- Features to Try -->
  <div class="bg-zinc-800 rounded-xl p-5 shadow">
    <h2 class="text-lg font-medium mb-3">Features to Try</h2>
    <div class="grid grid-cols-2 gap-3 text-sm text-zinc-300">
      <div>🔒 Private & Secure</div>
      <div>⏰ Daily Reminders</div>
      <div>🏷️ Tag & Search</div>
      <div>🎤 Voice Notes</div>
    </div>
  </div>

  <!-- Inspiration -->
  <div class="text-zinc-400 text-sm italic text-center mt-4">
    Need inspiration? <span class="text-amber-500 cursor-pointer hover:underline">Write about a childhood memory.</span>
  </div>
</div>
   
 