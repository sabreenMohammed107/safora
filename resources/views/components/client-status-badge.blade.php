@props(['status'])

@php
    $style = match ($status) {
        \App\Enums\ClientStatus::Pending => 'bg-brand-soft text-brand-deep',
        \App\Enums\ClientStatus::Active => 'bg-emerald-soft text-emerald-deep',
        \App\Enums\ClientStatus::Suspended => 'bg-rose-soft text-rose-deep',
        \App\Enums\ClientStatus::Rejected => 'bg-canvas text-ink/60',
    };
@endphp

<span {{ $attributes->merge(['class' => 'pill '.$style]) }}>
    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>{{ __('common.client_status.'.$status->value) }}
</span>
