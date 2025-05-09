<a {{$attributes->merge(
    ["class" => " border border-white/15 rounded bg-gray-700 font-semibold py-2 px-4  w-full md:w-auto hover:bg-gray-800"]
)}}>
    {{$slot}}
</a>