@props([
    'name',
    'subtitle',
    'price',
    'yearlyPrice',
    'description',
    'features' => [],
    'buttonText' => 'Get Started',
    'checkoutUrl' => '#',
    'buttonClass' => '',
    'icon' => 'zap',
    'popular' => false
])

<div class="relative h-full">
    <!-- Card -->
    <div class="relative h-full flex flex-col bg-white/5 backdrop-blur-lg border border-white/10 rounded-xl p-6 transition-all duration-300 hover:bg-white/10">
        <!-- Popular Badge -->
        @if($popular)
        <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-blue-400 to-teal-700 text-white text-xs font-medium px-3 py-1 rounded-full">
            Most Popular
        </div>
        @endif

        <!-- Plan Name & Price -->
        <div class="text-center mb-6">
            <h3 class="text-2xl font-bold text-white mb-1">{{ $name }}</h3>
            <div class="text-blue-400 text-sm mb-2">{{ $subtitle }}</div>
            
            <div class="my-4">
                @if($price === 'Free')
                    <span class="text-4xl font-bold text-white">Free</span>
                @else
                    <div class="flex items-end justify-center gap-1">
                        <span data-monthly-price="{{ $price }}" data-yearly-price="{{ $yearlyPrice }}" class="text-4xl font-bold text-white">{{ $price }}</span>
                        <span class="text-blue-300 mb-1">/mo</span>
                    </div>
                    @if($price !== 'Free')
                        <p class="text-xs text-blue-300 mt-1">
                            Billed monthly • Save 20% yearly
                        </p>
                    @endif
                @endif
            </div>
            
        </div>

        <!-- Divider -->
        <div class="h-px bg-white/10 my-4"></div>

        <!-- Features -->
        <div class="space-y-3 mb-6">
            @foreach(array_slice($features, 0, 5) as $feature)
                <div class="flex items-start">
                    <svg class="w-4 h-4 text-blue-400 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-sm text-white/80">{{ $feature }}</span>
                </div>
            @endforeach
            
            @if(count($features) > 5)
                <div class="text-center mt-4">
                    <span class="text-xs text-blue-300">+{{ count($features) - 5 }} more features</span>
                </div>
            @endif
        </div>

        <!-- Button -->
        <div class="mt-auto">
            <a href="{{ $checkoutUrl }}" class="w-full">
                <button class="w-full py-2.5 px-4 rounded-lg font-medium transition-all duration-200 
                    {{ $popular 
                        ? 'bg-gradient-to-r from-blue-400 to-teal-700 text-white hover:from-blue-500 hover:to-teal-600 shadow-lg hover:shadow-blue-500/30' 
                        : 'bg-white/5 text-white border border-white/10 hover:bg-white/10' }}">
                    {{ $buttonText }}
                </button>
            </a>
        </div>
        
        @if($price !== 'Free')
            <p class="text-xs text-center text-white/50 mt-3">No credit card required</p>
        @endif
    </div>
</div>