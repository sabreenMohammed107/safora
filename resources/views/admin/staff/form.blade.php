@php
    $editing = $staff->exists;
    $canGrantSuperAdmin = auth('admin')->user()->can('grantSuperAdmin', \App\Models\Admin::class);
@endphp

<x-layouts.admin :title="$editing ? $staff->name : __('admin.staff.new')" eyebrow="STAFF" :heading="$editing ? $staff->name : __('admin.staff.new')">
    <x-slot:breadcrumbs>
        <a href="{{ route('admin.staff.index') }}" class="hover:text-ink">{{ __('admin.nav.staff') }}</a>
        <span class="text-ink/30">/</span>
        <span class="font-bold text-ink">{{ $editing ? $staff->name : __('admin.staff.new') }}</span>
    </x-slot:breadcrumbs>

    <form method="POST" action="{{ $editing ? route('admin.staff.update', $staff) : route('admin.staff.store') }}"
          class="card p-5 max-w-3xl grid sm:grid-cols-2 gap-4"
          x-data="{ superAdmin: @js((bool) old('is_super_admin', $staff->is_super_admin)) }">
        @csrf
        @if ($editing) @method('PUT') @endif

        <label class="block">
            <span class="lbl">{{ __('common.fields.name') }} *</span>
            <input name="name" value="{{ old('name', $staff->name) }}" required class="fld">
            @error('name')<p class="fld-error">{{ $message }}</p>@enderror
        </label>
        <label class="block">
            <span class="lbl">{{ __('admin.staff.fields.title') }}</span>
            <input name="title" value="{{ old('title', $staff->title) }}" class="fld">
            @error('title')<p class="fld-error">{{ $message }}</p>@enderror
        </label>
        <label class="block sm:col-span-2">
            <span class="lbl">{{ __('common.fields.email') }} *</span>
            <input name="email" type="email" value="{{ old('email', $staff->email) }}" required dir="ltr" class="fld font-mono">
            @error('email')<p class="fld-error">{{ $message }}</p>@enderror
        </label>
        <label class="block">
            <span class="lbl">{{ __('common.fields.password') }}</span>
            <input name="password" type="password" dir="ltr" autocomplete="new-password" class="fld font-mono">
            <p class="mt-1 text-[11px] text-ink/50">{{ $editing ? __('accounts.password.admin_edit_hint') : __('accounts.password.generate_hint') }}</p>
            @error('password')<p class="fld-error">{{ $message }}</p>@enderror
        </label>
        <label class="block">
            <span class="lbl">{{ __('common.fields.password_confirmation') }}</span>
            <input name="password_confirmation" type="password" dir="ltr" autocomplete="new-password" class="fld font-mono">
        </label>

        <div class="sm:col-span-2 rounded-xl border border-black/10 p-4 space-y-3">
            <label @class(['flex items-start gap-3', 'opacity-55' => ! $canGrantSuperAdmin])>
                <input type="hidden" name="is_super_admin" value="0">
                <input type="checkbox" name="is_super_admin" value="1" x-model="superAdmin" class="mt-1 w-4 h-4 accent-brand" @disabled(! $canGrantSuperAdmin)>
                <span>
                    <span class="block text-[13px] font-extrabold"><i class="fa-solid fa-crown text-brand-deep text-[11px]"></i> {{ __('admin.staff.super_admin') }}</span>
                    <span class="block text-[12px] text-ink/60">{{ __('admin.staff.super_admin_hint') }}</span>
                </span>
            </label>
            @error('is_super_admin')<p class="fld-error">{{ $message }}</p>@enderror

            <label class="block" x-show="!superAdmin">
                <span class="lbl">{{ __('admin.staff.columns.role') }} *</span>
                <select name="admin_role_id" class="fld" :disabled="superAdmin">
                    <option value="">{{ __('common.choose') }}</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected(old('admin_role_id', $staff->admin_role_id) == $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('admin_role_id')<p class="fld-error">{{ $message }}</p>@enderror
                @if ($roles->isEmpty())
                    <p class="mt-1 text-[12px] text-amber-deep"><a href="{{ route('admin.roles.create') }}" class="font-bold underline">{{ __('admin.roles.create_first') }}</a></p>
                @endif
            </label>
        </div>

        <label class="block">
            <span class="lbl">{{ __('common.language') }}</span>
            <select name="locale" class="fld">
                @foreach (config('safora.locales') as $code => $locale)
                    <option value="{{ $code }}" @selected(old('locale', $staff->locale) === $code)>{{ $locale['name'] }}</option>
                @endforeach
            </select>
        </label>
        <label class="flex items-center gap-2 sm:pt-6 text-[13px] font-bold cursor-pointer">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" value="1" class="w-4 h-4 accent-emerald" @checked(old('active', $staff->active))>
            {{ __('common.account_active') }}
        </label>
        @error('active')<p class="fld-error sm:col-span-2">{{ $message }}</p>@enderror

        <div class="sm:col-span-2 flex justify-end gap-2 pt-2 border-t border-black/5">
            <a href="{{ route('admin.staff.index') }}" class="btn-ghost">{{ __('common.cancel') }}</a>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> {{ __('common.save') }}</button>
        </div>
    </form>
</x-layouts.admin>
