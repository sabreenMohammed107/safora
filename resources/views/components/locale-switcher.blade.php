@props(['dark' => false])

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-0.5 rounded-lg p-0.5 '.($dark ? 'bg-white/10' : 'bg-white border border-black/10')]) }}
     role="group" aria-label="{{ __('common.language') }}">
    @foreach (config('safora.locales') as $code => $locale)
        <form method="POST" action="{{ route('locale.update', $code) }}">
            @csrf
            <button type="submit" lang="{{ $code }}" aria-pressed="{{ app()->getLocale() === $code ? 'true' : 'false' }}"
                    @class([
                        'px-2.5 py-1 rounded-md text-[11px] font-extrabold uppercase transition',
                        'bg-brand text-ink' => app()->getLocale() === $code,
                        'text-white/70 hover:text-white' => $dark && app()->getLocale() !== $code,
                        'text-ink/60 hover:text-ink' => ! $dark && app()->getLocale() !== $code,
                    ])>
                {{ $code }}
            </button>
        </form>
    @endforeach
</div>
