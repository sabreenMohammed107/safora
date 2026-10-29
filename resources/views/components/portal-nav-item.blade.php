@props(['item', 'small' => false])

@php
    $active = $item['route'] && request()->routeIs($item['route'], str_replace('.index', '.*', $item['route']));
    $disabled = $item['locked'] || ! $item['route'];
    $classes = 'nav-link'.($small ? ' py-2 text-[13px]' : '');
@endphp

@if ($disabled)
    <span class="{{ $classes }}" aria-disabled="true" @if ($item['locked']) title="{{ __('portal.nav.locked') }}" @endif>
@else
    <a href="{{ route($item['route']) }}" class="{{ $classes }}" @if ($active) aria-current="page" @endif>
@endif
        <i @class(['fa-solid w-5 text-center', $item['icon'], 'text-brand' => $active])></i>
        <span>{{ $item['label'] }}</span>
        @if ($item['locked'])
            <i class="fa-solid fa-lock ms-auto text-[10px]"></i>
        @elseif (! $item['route'])
            <span class="ms-auto rounded bg-white/10 px-1.5 text-[9.5px] text-white/45">{{ __('common.soon') }}</span>
        @endif
@if ($disabled)
    </span>
@else
    </a>
@endif
