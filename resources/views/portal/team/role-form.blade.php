@php($editing = $role->exists)

<x-layouts.portal :title="$editing ? $role->displayName() : __('portal.team.new_role')">
    <div>
        <a href="{{ route('portal.team.index') }}" class="text-[12px] font-bold text-teal-deep hover:underline"><i class="fa-solid fa-arrow-right ltr:rotate-180 text-[10px]"></i> {{ __('portal.team.heading') }}</a>
        <h1 class="text-2xl font-extrabold mt-1">{{ $editing ? $role->displayName() : __('portal.team.new_role') }}</h1>
        <p class="text-sm text-ink/65 mt-1 max-w-2xl">{{ __('portal.team.role_intro') }}</p>
    </div>

    <form method="POST" action="{{ $editing ? route('portal.team.roles.update', $role) : route('portal.team.roles.store') }}" class="space-y-5">
        @csrf
        @if ($editing) @method('PUT') @endif

        <section class="card p-5 grid sm:grid-cols-2 gap-4">
            <label class="block">
                <span class="lbl">{{ __('portal.team.fields.role_name') }} *</span>
                <input name="name" value="{{ old('name', $editing ? $role->displayName() : '') }}" required maxlength="60" class="fld">
                @error('name')<p class="fld-error">{{ $message }}</p>@enderror
            </label>
            <label class="block">
                <span class="lbl">{{ __('portal.team.fields.description') }}</span>
                <input name="description" value="{{ old('description', $role->description) }}" maxlength="255" class="fld">
                @error('description')<p class="fld-error">{{ $message }}</p>@enderror
            </label>
        </section>

        <x-permission-matrix :groups="$groups" :group-labels="__('permissions.client_groups')"
                             :selected="old('permissions', $role->permissions->map->value->all())" />

        <div class="flex justify-end gap-2">
            <a href="{{ route('portal.team.index') }}" class="btn-ghost">{{ __('common.cancel') }}</a>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> {{ __('common.save') }}</button>
        </div>
    </form>
</x-layouts.portal>
