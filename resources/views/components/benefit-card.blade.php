<div class=" bg-[#1a1d24] p-6 rounded-xl flex flex-col space-y-4 shadow-md hover:shadow-lg transition-shadow">
    <div class="w-12 h-12 bg-[#2e2e2e] rounded-full flex items-center justify-center">
        {!! file_get_contents(public_path($svg)) !!}
    </div>
    <div>
        <h3 class="font-playfair text-lg font-semibold text-[#90cdf4] font-[cursive] mb-2">
            {{ $title }}
        </h3>
        <p class="text-white/70 leading-relaxed">
            {{ $description }}
        </p>
    </div>
</div>