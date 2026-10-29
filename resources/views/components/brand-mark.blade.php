@props(['class' => 'w-8 h-8'])

@php($clipId = 'sfMark'.\Illuminate\Support\Str::random(6))

<svg {{ $attributes->merge(['class' => 'shrink-0 '.$class]) }} viewBox="0 0 48 47" aria-hidden="true">
    <defs><clipPath id="{{ $clipId }}"><rect width="35" height="35" rx="8"/></clipPath></defs>
    <rect width="35" height="35" rx="8" fill="#F02B18"/>
    <rect x="13" y="12" width="35" height="35" rx="8" fill="#E17F1A"/>
    <rect x="13" y="12" width="35" height="35" rx="8" fill="#FD8118" clip-path="url(#{{ $clipId }})"/>
</svg>
