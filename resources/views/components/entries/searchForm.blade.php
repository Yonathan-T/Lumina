

<div class="max-w-3xl mx-auto p-6">
  <!-- Search Input -->
  <div class="mb-6">
    <label for="search" class="block text-sm font-medium text-gray-300 mb-2">Search</label>
    <div class="relative">
      <input
        id="search"
        type="text"
        placeholder="Search for Entires..."
        class="w-full bg-white/5 border border-white/10 text-white text-sm rounded-xl py-3 px-4 pl-10 focus:outline-none focus:ring-2 focus:ring-[#c2b68e] focus:border-[#c2b68e] transition-all"
      />
      <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
      </svg>
    </div>
  </div>

  <!-- Search Result Card -->
  <!-- Only show if searchResults is not empty -->
   <div>

       @if (!empty($searchResults))
       @foreach ($searchResults as $result)
       <div class="bg-white/5 border border-white/10 rounded-2xl p-6 shadow-md hover:shadow-lg transition-shadow duration-300 mb-4">
           <div class="flex items-start justify-between mb-4">
               <div class="text-sm text-gray-400">Search Result</div>
               <div class="text-xs text-gray-500">{{ $result['time'] ?? 'Just now' }}</div>
            </div>
            
            <h3 class="text-xl font-semibold text-white mb-2">
                {{ $result['title'] }}
            </h3>
            
            <p class="text-sm text-gray-300 leading-relaxed">
                {{ $result['description'] }}
            </p>
            
            <div class="mt-4 flex justify-end">
                <button 
                wire:click="copyToClipboard('{{ $result['description'] }}')"
                class="bg-[#c2b68e] hover:bg-[#a6966b] text-white text-sm px-4 py-2 rounded-lg transition-all"
                >
                Copy
            </button>
        </div>
    </div>
    @endforeach
    @endif
</div>
    
</div>
