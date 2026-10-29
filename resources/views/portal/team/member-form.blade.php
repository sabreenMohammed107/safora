@php($editing = $member->exists)

<x-layouts.portal :title="$editing ? $member->name : __('portal.team.new_member')">
    <div>
        <a href="{{ route('portal.team.index') }}" class="text-[12px] font-bold text-teal-deep hover:underline"><i class="fa-solid fa-arrow-right ltr:rotate-180 text-[10px]"></i> {{ __('portal.team.heading') }}</a>
        <h1 class="text-2xl font-extrabold mt-1">{{ $editing ? $member->name : __('portal.team.new_member') }}</h1>
    </div>

    <form method="POST" action="{{ $editing ? route('portal.team.members.update', $member) : route('portal.team.members.store') }}"
          class="card p-5 max-w-3xl grid sm:grid-cols-2 gap-4">
        @csrf
        @if ($editing) @method('PUT') @endif

        @include('partials.client-user-fields', [
            'passwordHint' => $editing ? __('common.password_keep_hint') : __('accounts.password.required_temporary_hint'),
        ])

        <div class="sm:col-span-2 flex justify-end gap-2 pt-2 border-t border-black/5">
            <a href="{{ route('portal.team.index') }}" class="btn-ghost">{{ __('common.cancel') }}</a>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> {{ __('common.save') }}</button>
        </div>
    </form>
</x-layouts.portal>
