<x-layouts.portal :title="__('portal.nav.account')">
    <div>
        <p class="font-mono text-[11px] text-teal-deep font-semibold tracking-widest">MY ACCOUNT</p>
        <h1 class="text-2xl font-extrabold mt-1">{{ __('portal.nav.account') }}</h1>
    </div>

    <div class="max-w-3xl space-y-5">
        <section class="card">
            <header class="px-5 py-4 border-b border-black/5"><h2 class="font-extrabold">{{ __('accounts.profile.details') }}</h2></header>
            <form method="POST" action="{{ route('portal.account.update') }}" class="p-5 grid sm:grid-cols-2 gap-4">
                @csrf
                @method('PUT')
                <label class="block">
                    <span class="lbl">{{ __('common.fields.name') }} *</span>
                    <input name="name" value="{{ old('name', $user->name) }}" required class="fld">
                    @error('name')<p class="fld-error">{{ $message }}</p>@enderror
                </label>
                <label class="block">
                    <span class="lbl">{{ __('common.language') }}</span>
                    <select name="locale" class="fld">
                        @foreach (config('safora.locales') as $code => $locale)
                            <option value="{{ $code }}" @selected(old('locale', $user->locale) === $code)>{{ $locale['name'] }}</option>
                        @endforeach
                    </select>
                </label>
                <div>
                    <p class="lbl">{{ __('common.fields.email') }}</p>
                    <p class="mt-2 font-mono font-bold" dir="ltr">{{ $user->email }}</p>
                </div>
                <div>
                    <p class="lbl">{{ __('portal.team.columns.role') }}</p>
                    <p class="mt-2 font-bold">{{ $user->roleLabel() }} · {{ $user->branch?->name ?? __('portal.branches.all') }}</p>
                </div>
                <div class="sm:col-span-2 flex justify-end pt-2 border-t border-black/5">
                    <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> {{ __('common.save') }}</button>
                </div>
            </form>
        </section>

        @unless ($portal->isImpersonated())
            @include('partials.own-password-form', ['action' => route('portal.account.password'), 'mustChange' => $user->must_change_password])
        @endunless
    </div>
</x-layouts.portal>
