<x-layouts.auth :title="__('admin.auth.title')" eyebrow="ADMIN CONSOLE" :heading="__('admin.auth.heading')" :intro="__('admin.auth.intro')">
    <x-slot:aside>
        <h2 class="text-[30px] lg:text-[34px] font-extrabold leading-tight">{{ __('admin.auth.aside_title') }}</h2>
        <p class="mt-3 text-[15px] text-white/75 leading-relaxed">{{ __('admin.auth.aside_text') }}</p>
    </x-slot:aside>

    <form method="POST" action="{{ route('admin.login.store') }}" class="mt-5 space-y-4" novalidate>
        @csrf
        @include('partials.login-fields')

        <button type="submit" class="w-full py-3.5 rounded-xl bg-ink text-white hover:bg-ink-elev font-extrabold text-[15px] transition">
            {{ __('common.sign_in') }}
        </button>
    </form>
</x-layouts.auth>
