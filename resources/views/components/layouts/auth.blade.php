@props(['title', 'eyebrow', 'heading', 'intro' => null, 'wide' => false])

{{-- Split sign-in screen (prototype: 11_ClientCenter.html#portal login). --}}
<x-layouts.base :title="$title">
    <div class="min-h-screen grid lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">
        <section class="relative hidden lg:flex flex-col bg-ink text-white px-12 py-10 overflow-hidden">
            <div class="absolute -top-24 -end-24 w-80 h-80 rounded-full bg-brand/10 blur-3xl"></div>
            <div class="relative flex items-center gap-3">
                <x-brand-mark class="w-11 h-11" />
                <div dir="ltr" class="leading-none">
                    <p class="wordmark text-[23px] text-brand-cream">SAFORA</p>
                    <p class="text-[9.5px] font-semibold tracking-[.08em] text-ink-mist mt-1.5">by ELITE INTEGRATED BUSINESS</p>
                </div>
            </div>
            <div class="relative my-auto max-w-md">
                {{ $aside }}
            </div>
            <p class="relative text-[11px] text-white/40">© SAFORA — Elite Integrated Business</p>
        </section>

        <section class="flex flex-col px-6 py-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 lg:invisible">
                    <x-brand-mark class="w-8 h-8" />
                    <span dir="ltr" class="wordmark text-lg">SAFORA</span>
                </div>
                <x-locale-switcher />
            </div>

            <div class="flex-1 flex items-center justify-center py-10">
                <div @class(['w-full', 'max-w-[420px]' => ! $wide, 'max-w-2xl' => $wide])>
                    <p class="font-mono text-[11px] font-bold text-teal-deep tracking-widest">{{ $eyebrow }}</p>
                    <h1 class="text-[26px] font-extrabold mt-1">{{ $heading }}</h1>
                    @if ($intro)
                        <p class="text-[13.5px] text-ink/65 mt-1">{{ $intro }}</p>
                    @endif

                    <x-flash class="mt-5" />

                    {{ $slot }}
                </div>
            </div>
        </section>
    </div>
</x-layouts.base>
