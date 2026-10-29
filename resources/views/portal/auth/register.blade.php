<x-layouts.auth :title="__('portal.register.title')" eyebrow="NEW FACILITY" :heading="__('portal.register.heading')" :intro="__('portal.register.intro')" wide>
    <x-slot:aside>
        <h2 class="text-[30px] font-extrabold leading-tight">{{ __('portal.register.aside_title') }}</h2>
        <ol class="mt-8 space-y-4 text-[14px]">
            @foreach (['submit', 'review', 'activate'] as $index => $step)
                <li class="flex gap-3">
                    <span class="w-8 h-8 shrink-0 rounded-full bg-brand text-ink font-extrabold flex items-center justify-center">{{ $index + 1 }}</span>
                    <span class="pt-1">{{ __('portal.register.steps.'.$step) }}</span>
                </li>
            @endforeach
        </ol>
    </x-slot:aside>

    <form method="POST" action="{{ route('portal.register.store') }}" enctype="multipart/form-data" class="mt-5 space-y-5" novalidate>
        @csrf

        <fieldset class="grid sm:grid-cols-2 gap-4">
            <legend class="sr-only">{{ __('portal.register.company_section') }}</legend>
            <label class="block sm:col-span-2">
                <span class="lbl">{{ __('common.fields.company') }} *</span>
                <input name="company" value="{{ old('company') }}" required class="fld">
                @error('company')<p class="fld-error">{{ $message }}</p>@enderror
            </label>
            <label class="block">
                <span class="lbl">{{ __('common.fields.category') }} *</span>
                <select name="sector_category_id" required class="fld">
                    <option value="">{{ __('common.choose') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('sector_category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('sector_category_id')<p class="fld-error">{{ $message }}</p>@enderror
            </label>
            <label class="block">
                <span class="lbl">{{ __('common.fields.cr_number') }} *</span>
                <input name="cr_number" value="{{ old('cr_number') }}" required dir="ltr" inputmode="numeric" placeholder="1010XXXXXX" class="fld font-mono">
                @error('cr_number')<p class="fld-error">{{ $message }}</p>@enderror
            </label>
            <label class="block">
                <span class="lbl">{{ __('common.fields.contact_name') }} *</span>
                <input name="contact_name" value="{{ old('contact_name') }}" required class="fld">
                @error('contact_name')<p class="fld-error">{{ $message }}</p>@enderror
            </label>
            <label class="block">
                <span class="lbl">{{ __('common.fields.phone') }} *</span>
                <input name="phone" value="{{ old('phone') }}" required dir="ltr" placeholder="+966 5X XXX XXXX" class="fld font-mono">
                @error('phone')<p class="fld-error">{{ $message }}</p>@enderror
            </label>
        </fieldset>

        <fieldset class="rounded-xl border border-black/10 p-4 grid sm:grid-cols-2 gap-4">
            <legend class="px-1 text-[12px] font-extrabold text-ink/70">{{ __('portal.register.login_section') }}</legend>
            <label class="block sm:col-span-2">
                <span class="lbl">{{ __('common.fields.email') }} *</span>
                <input name="email" type="email" value="{{ old('email') }}" required dir="ltr" autocomplete="username" class="fld font-mono">
                @error('email')<p class="fld-error">{{ $message }}</p>@enderror
            </label>
            <label class="block">
                <span class="lbl">{{ __('common.fields.password') }} *</span>
                <input name="password" type="password" required dir="ltr" autocomplete="new-password" class="fld font-mono">
                @error('password')<p class="fld-error">{{ $message }}</p>@enderror
            </label>
            <label class="block">
                <span class="lbl">{{ __('common.fields.password_confirmation') }} *</span>
                <input name="password_confirmation" type="password" required dir="ltr" autocomplete="new-password" class="fld font-mono">
            </label>
            <p class="sm:col-span-2 text-[11.5px] text-ink/55">{{ __('portal.register.password_hint') }}</p>
        </fieldset>

        <label class="block">
            <span class="lbl">{{ __('portal.register.documents') }} *</span>
            <input name="documents[]" type="file" multiple required accept=".pdf,.jpg,.jpeg,.png"
                   class="fld file:me-3 file:rounded-md file:border-0 file:bg-canvas file:px-3 file:py-1.5 file:text-xs file:font-bold">
            <p class="mt-1 text-[11.5px] text-ink/55">{{ __('portal.register.documents_hint', ['max' => \App\Http\Requests\RegisterClientRequest::MAX_DOCUMENTS, 'size' => \App\Http\Requests\RegisterClientRequest::MAX_DOCUMENT_KB / 1024]) }}</p>
            @error('documents')<p class="fld-error">{{ $message }}</p>@enderror
            @foreach ($errors->get('documents.*') as $messages)
                <p class="fld-error">{{ $messages[0] }}</p>
            @endforeach
        </label>

        <button type="submit" class="w-full py-3.5 rounded-xl bg-brand text-ink hover:bg-brand-deep hover:text-white font-extrabold text-[15px] transition">
            {{ __('portal.register.submit') }}
        </button>

        <p class="text-center text-[13px] text-ink/65">
            {{ __('portal.register.have_account') }}
            <a href="{{ route('portal.login') }}" class="font-extrabold text-brand-deep hover:underline">{{ __('common.sign_in') }}</a>
        </p>
    </form>
</x-layouts.auth>
