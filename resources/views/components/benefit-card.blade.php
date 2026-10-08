<div class="relative bg-white/5 backdrop-blur-sm border border-white/10 p-6 rounded-xl flex flex-col space-y-4 shadow-md hover:shadow-2xl hover:border-white/30 hover:bg-white/10 transition-all duration-300 group transform hover:scale-[1.02]">
    <div class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center text-white">
        {!! file_get_contents(public_path($svg)) !!}
    </div>
    <div>
        <h3 class="font-playfair text-lg font-semibold text-white mb-2">
            {{ $title }}
        </h3>
        <p class="text-white/75 leading-relaxed text-sm">
            {{ $description }}
        </p>
    </div>
</div>