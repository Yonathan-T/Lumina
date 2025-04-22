<x-layout>
   <x-form-parent>
   <h2 class="text-2xl font-bold text-white">Register</h2>
   <p class="text-sm text-gray-500">Fill in the form below to create your account.</p>
     <form method="POST" action="#">
       @csrf
       <div class="flex flex-col gap-6">

                    <x-form-wrapper>
                        <x-form-label for="firstName">First Name</x-form-label>    
                        <x-form-input type="text" id="firstName" name="name" :value="old('name')"/>
                        <x-form-error name="name"/>
                      </x-form-wrapper>
                      <x-form-wrapper>
                        <x-form-label for="email">Email</x-form-label>    
                        <x-form-input id="email" name="email" type="email" :value="old('email')" />
                        <x-form-error name="email"/>
                      </x-form-wrapper>
                      <x-form-wrapper>
                        <x-form-label for="password">Password</x-form-label>    
                        <x-form-input id="password" name="password" type="password" />
                        <x-form-error name="password"/>
                      </x-form-wrapper>
                      <x-form-wrapper>
                        <x-form-label for="password_confirmation">Confirm Password</x-form-label>    
                        <x-form-input id="password_confirmation" name="password_confirmation" type="password"/>
                        <x-form-error name="password_confirmation"/>
                      </x-form-wrapper>
                      
                      
                    </div>
                    
                <div class="mt-10 flex justify-end gap-x-4 items-center">
                  <a href="/" class="text-sm font-semibold text-gray-300 ">Cancel</a>
                  <x-form-button>Save</x-form-button> 
                </div>
              </form>
        
        </x-form-parent>
      </x-layout>