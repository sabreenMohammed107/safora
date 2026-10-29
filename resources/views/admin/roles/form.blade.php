@php($editing = $role->exists)

<x-layouts.admin :title="$editing ? $role->name : __('admin.roles.new')" eyebrow="STAFF ROLE" :heading="$editing ? $role->name : __('admin.roles.new')" :intro="__('admin.roles.intro')">
    <x-slot:breadcrumbs>
        <a href="{{ route('admin.staff.index') }}" class="hover:text-ink">{{ __('admin.nav.staff') }}</a>
        <span class="text-ink/30">/</span>
        <span class="font-bold text-ink">{{ $editing ? $role->name : __('admin.roles.new') }}</span>
    </x-slot:breadcrumbs>

    <form method="POST" action="{{ $editing ? route('admin.roles.update', $role) : route('admin.roles.store') }}" class="space-y-5">
        @csrf
        @if ($editing) @method('PUT') @endif

        <section class="card p-5 grid sm:grid-cols-2 gap-4">
            <label class="block">
                <span class="lbl">{{ __('admin.roles.fields.name') }} *</span>
                <input name="name" value="{{ old('name', $role->name) }}" required maxlength="60" class="fld">
                @error('name')<p class="fld-error">{{ $message }}</p>@enderror
            </label>
            <label class="block">
                <span class="lbl">{{ __('admin.roles.fields.description') }}</span>
                <input name="description" value="{{ old('description', $role->description) }}" maxlength="255" class="fld">
                @error('description')<p class="fld-error">{{ $message }}</p>@enderror
            </label>
        </section>

        <x-permission-matrix :groups="$groups" :group-labels="__('permissions.admin_groups')"
                             :selected="old('permissions', $role->permissions->map->value->all())" />

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.staff.index') }}" class="btn-ghost">{{ __('common.cancel') }}</a>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> {{ __('common.save') }}</button>
        </div>
    </form>
</x-layouts.admin>
