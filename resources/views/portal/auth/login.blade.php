<x-layouts.auth :title="__('portal.auth.title')" eyebrow="CLIENT PORTAL" :heading="__('portal.auth.heading')" :intro="__('portal.auth.intro')">
    <x-slot:aside>
        <h2 class="text-[30px] lg:text-[36px] font-extrabold leading-tight">{{ __('portal.auth.aside_title') }}</h2>
        <p class="mt-3 text-[15px] text-white/75 leading-relaxed">{{ __('portal.auth.aside_text') }}</p>
        <ul class="mt-8 space-y-4 text-[14px]">
            @foreach (['limits' => 'fa-temperature-half', 'records' => 'fa-table-cells', 'plans' => 'fa-route'] as $key => $icon)
                <li class="flex gap-3">
                    <span class="w-9 h-9 shrink-0 rounded-xl bg-white/10 flex items-center justify-center"><i class="fa-solid {{ $icon }} text-brand"></i></span>
                    <span><b>{{ __('portal.auth.features.'.$key.'.title') }}</b><br><span class="text-white/60 text-[12.5px]">{{ __('portal.auth.features.'.$key.'.text') }}</span></span>
                </li>
            @endforeach
        </ul>
    </x-slot:aside>

    <form method="POST" action="{{ route('portal.login.store') }}" class="mt-5 space-y-4" novalidate>
        @csrf
        @include('partials.login-fields')

        <p class="rounded-lg bg-teal-soft px-3 py-2 text-[12.5px] text-teal-deep">{{ __('portal.auth.forgot') }}</p>

        <button type="submit" class="w-full py-3.5 rounded-xl bg-brand text-ink hover:bg-brand-deep hover:text-white font-extrabold text-[15px] transition">
            {{ __('common.sign_in') }}
        </button>
    </form>

    <p class="mt-6 text-center text-[13px] text-ink/65">
        {{ __('portal.auth.new_company') }}
        <a href="{{ route('portal.register') }}" class="font-extrabold text-brand-deep hover:underline">{{ __('portal.auth.register_now') }}</a>
    </p>
</x-layouts.auth>
