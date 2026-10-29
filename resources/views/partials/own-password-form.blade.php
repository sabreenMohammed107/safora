{{-- Change your own password (admin profile / portal account). Needs $action and $mustChange. --}}
<section @class(['card', 'border-amber/50!' => $mustChange]) id="password">
    <header class="px-5 py-4 border-b border-black/5">
        <h2 class="font-extrabold"><i class="fa-solid fa-lock text-ink/40 text-sm"></i> {{ __('accounts.password.change_heading') }}</h2>
        @if ($mustChange)
            <p class="mt-1 text-[12.5px] font-bold text-amber-deep"><i class="fa-solid fa-triangle-exclamation"></i> {{ __('accounts.must_change_password') }}</p>
        @endif
    </header>
    <form method="POST" action="{{ $action }}" class="p-5 grid sm:grid-cols-3 gap-4">
        @csrf
        @method('PUT')
        <label class="block">
            <span class="lbl">{{ __('accounts.password.current') }} *</span>
            <input name="current_password" type="password" required dir="ltr" autocomplete="current-password" class="fld font-mono">
            @error('current_password')<p class="fld-error">{{ $message }}</p>@enderror
        </label>
        <label class="block">
            <span class="lbl">{{ __('accounts.password.new') }} *</span>
            <input name="password" type="password" required dir="ltr" autocomplete="new-password" class="fld font-mono">
            @error('password')<p class="fld-error">{{ $message }}</p>@enderror
        </label>
        <label class="block">
            <span class="lbl">{{ __('common.fields.password_confirmation') }} *</span>
            <input name="password_confirmation" type="password" required dir="ltr" autocomplete="new-password" class="fld font-mono">
        </label>
        <p class="sm:col-span-2 text-[11.5px] text-ink/55">{{ __('portal.register.password_hint') }} {{ __('accounts.password.other_devices') }}</p>
        <div class="flex justify-end">
            <button type="submit" class="btn-primary"><i class="fa-solid fa-key"></i> {{ __('accounts.password.update') }}</button>
        </div>
    </form>
</section>
