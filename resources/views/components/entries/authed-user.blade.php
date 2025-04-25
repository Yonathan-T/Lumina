  <div class=" bg-white/5 rounded-xl p-5 shadow">
      <div class="text-center">

          <h1 class="mb-10 text-4xl font-bold text-white">
              Welcome to
              <span class="relative inline-block ml-2">
                  <span class="absolute inset-0 bg-gradient-to-r from-[#7c6a54] to-transparent rounded-sm"></span>
                  <span class="relative text-white  px-2 ">Memo Mate</span>
                </span>
            </h1>
        </div>
            
<x-form-parent >
<form method="POST" >
       @csrf
       <div class="flex flex-col gap-6">
                    <x-form-wrapper>
                        <x-form-label for="title">Title</x-form-label>    
                        <x-form-input id="title" name="title" type="text"/>
                        <x-form-error name="title"/>
                      </x-form-wrapper>
                      <x-form-wrapper>
                        <x-form-label for="content">Content</x-form-label>    
                        <x-form-input id="content" name="content" type="text" placeholder="What’s one thing you’re grateful for today?" />
                        <x-form-error name="content"/>
                      </x-form-wrapper>
                      
        </div>
        <div class="mt-10 flex justify-end gap-x-4 items-center">
                  <a href="/" class="text-sm font-semibold text-gray-300 ">Cancel</a>
                  <x-form-button>Save</x-form-button>
                </div>
              </form>
              
</x-form-parent>
    
 
  <!-- Inspiration -->
  <!-- <div class="text-zinc-400 text-sm italic text-center mt-4">
    Need inspiration? <span class="text-amber-500 cursor-pointer hover:underline">Write about a childhood memory.</span>
  </div> -->
</div>
   
 