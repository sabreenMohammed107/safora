<label class="block">
    <span class="lbl">{{ __('common.fields.email') }}</span>
    <div class="relative mt-1">
        <i class="fa-regular fa-envelope absolute start-3.5 top-1/2 -translate-y-1/2 text-ink/40"></i>
        <input name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" dir="ltr"
               placeholder="name@company.com" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
               class="w-full rounded-xl border border-black/10 bg-white ps-10 pe-3 py-3 text-[14.5px] font-mono outline-none focus:border-teal focus:ring-4 focus:ring-teal/10">
    </div>
    @error('email')<p class="fld-error">{{ $message }}</p>@enderror
</label>

<label class="block" x-data="{ show: false }">
    <span class="lbl">{{ __('common.fields.password') }}</span>
    <div class="relative mt-1">
        <i class="fa-solid fa-lock absolute start-3.5 top-1/2 -translate-y-1/2 text-ink/40"></i>
        <input name="password" :type="show ? 'text' : 'password'" type="password" required autocomplete="current-password" dir="ltr"
               class="w-full rounded-xl border border-black/10 bg-white ps-10 pe-11 py-3 text-[14.5px] font-mono outline-none focus:border-teal focus:ring-4 focus:ring-teal/10">
        <button type="button" @click="show = !show" class="absolute end-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg text-ink/50 hover:bg-canvas"
                :aria-label="show ? @js(__('common.hide_password')) : @js(__('common.show_password'))">
            <i class="fa-regular" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
        </button>
    </div>
    @error('password')<p class="fld-error">{{ $message }}</p>@enderror
</label>

<label class="inline-flex items-center gap-2 text-[13px] font-semibold cursor-pointer">
    <input type="checkbox" name="remember" value="1" class="w-4 h-4 accent-teal" @checked(old('remember'))> {{ __('common.remember_me') }}
</label>
