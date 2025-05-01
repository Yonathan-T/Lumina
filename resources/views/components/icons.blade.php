@props(['type' => 'heart','logo'=>false])
@php
$class="fa-solid text-[#c6b78e] text-2xl align-center ";
@$classLogo = "";
if($type === "heart" ){
$class .=" fa-heart";
}
else if($type === "check"){
$class .="fa-circle-check";
}

else if($type === "login"){
$class .="fa-right-to-bracket";
}
else if($type === "hashtag"){
$class .="fa-hashtag";
}
else if($type === "leftQ"){
$class .="text-sm fa-quote-left";
}
else if($type === "search"){
$class .="text-lg fa-magnifying-glass";
}
else if($type === "rightQ"){
$class .="text-sm fa-quote-right";
}
else if($type === "telegram" && $logo===true ){
$classLogo ="text-[#c6b78e] text-2xl align-center fa-brands fa-telegram";
}
else if($type === "chat"){
$classLogo ="fa-brands fa-rocketchat";
}
@endphp

<i {{ $attributes->merge(["class"=>$classLogo ?: $class]) }}></i>