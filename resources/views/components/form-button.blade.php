@props(['buttonsDelete' => false ])
@if ($buttonsDelete === true )
<button {{ $attributes->merge(["type"=>"submit", "class"=>"rounded-md bg-red-500 hover:bg-red-400 px-4 py-2 text-sm font-semibold hover:bg-gray-200 transition"]) }}>{{$slot}}</button>
@else
<button {{ $attributes->merge(["type"=>"submit", "class"=>"rounded-md bg-white text-black px-4 py-2 text-sm font-semibold hover:bg-gray-200 transition"]) }}>{{$slot}}</button>


@endif