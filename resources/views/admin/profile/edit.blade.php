<x-layouts.admin :title="__('accounts.profile.title')" eyebrow="MY PROFILE" :heading="__('accounts.profile.title')">
    <x-slot:breadcrumbs><span class="font-bold text-ink">{{ __('accounts.profile.title') }}</span></x-slot:breadcrumbs>

    <div class="max-w-3xl space-y-5">
        <section class="card">
            <header class="px-5 py-4 border-b border-black/5"><h2 class="font-extrabold">{{ __('accounts.profile.details') }}</h2></header>
            <form method="POST" action="{{ route('admin.profile.update') }}" class="p-5 grid sm:grid-cols-2 gap-4">
                @csrf
                @method('PUT')
                <label class="block">
                    <span class="lbl">{{ __('common.fields.name') }} *</span>
                    <input name="name" value="{{ old('name', $admin->name) }}" required class="fld">
                    @error('name')<p class="fld-error">{{ $message }}</p>@enderror
                </label>
                <label class="block">
                    <span class="lbl">{{ __('admin.staff.fields.title') }}</span>
                    <input name="title" value="{{ old('title', $admin->title) }}" class="fld">
                    @error('title')<p class="fld-error">{{ $message }}</p>@enderror
                </label>
                <div>
                    <p class="lbl">{{ __('common.fields.email') }}</p>
                    <p class="mt-2 font-mono font-bold" dir="ltr">{{ $admin->email }}</p>
                </div>
                <label class="block">
                    <span class="lbl">{{ __('common.language') }}</span>
                    <select name="locale" class="fld">
                        @foreach (config('safora.locales') as $code => $locale)
                            <option value="{{ $code }}" @selected(old('locale', $admin->locale) === $code)>{{ $locale['name'] }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="sm:col-span-2 flex items-center justify-between gap-3 pt-2 border-t border-black/5">
                    <p class="text-[12px] text-ink/60">
                        @if ($admin->is_super_admin)
                            <i class="fa-solid fa-crown text-brand-deep"></i> {{ __('admin.staff.super_admin') }}
                        @else
                            {{ __('admin.staff.columns.role') }}: <b>{{ $admin->role?->name ?? '—' }}</b>
                        @endif
                    </p>
                    <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> {{ __('common.save') }}</button>
                </div>
            </form>
        </section>

        @include('partials.own-password-form', ['action' => route('admin.profile.password'), 'mustChange' => $admin->must_change_password])
    </div>
</x-layouts.admin>
