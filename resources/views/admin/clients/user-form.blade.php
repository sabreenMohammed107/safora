@php($editing = $member->exists)

<x-layouts.admin :title="$editing ? $member->name : __('portal.team.new_member')" :eyebrow="$client->code" :heading="$editing ? $member->name : __('portal.team.new_member')" :intro="$client->company">
    <x-slot:breadcrumbs>
        <a href="{{ route('admin.clients.index') }}" class="hover:text-ink">{{ __('admin.nav.clients') }}</a>
        <span class="text-ink/30">/</span>
        <a href="{{ route('admin.clients.show', $client) }}" class="hover:text-ink truncate">{{ $client->company }}</a>
        <span class="text-ink/30">/</span>
        <span class="font-bold text-ink">{{ $editing ? $member->name : __('portal.team.new_member') }}</span>
    </x-slot:breadcrumbs>

    <form method="POST" action="{{ $editing ? route('admin.clients.users.update', [$client, $member]) : route('admin.clients.users.store', $client) }}"
          class="card p-5 max-w-3xl grid sm:grid-cols-2 gap-4">
        @csrf
        @if ($editing) @method('PUT') @endif

        @include('partials.client-user-fields', [
            'canAppointOwner' => true,
            'passwordHint' => $editing ? __('accounts.password.admin_edit_hint') : __('accounts.password.generate_hint'),
        ])

        <div class="sm:col-span-2 flex justify-end gap-2 pt-2 border-t border-black/5">
            <a href="{{ route('admin.clients.show', $client) }}" class="btn-ghost">{{ __('common.cancel') }}</a>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> {{ __('common.save') }}</button>
        </div>
    </form>
</x-layouts.admin>
