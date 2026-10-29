@php($editing = $branch->exists)

<x-layouts.admin :title="$editing ? $branch->name : __('accounts.branch.new')" :eyebrow="$editing ? $branch->code : $client->code" :heading="$editing ? $branch->name : __('accounts.branch.new')" :intro="$client->company">
    <x-slot:breadcrumbs>
        <a href="{{ route('admin.clients.index') }}" class="hover:text-ink">{{ __('admin.nav.clients') }}</a>
        <span class="text-ink/30">/</span>
        <a href="{{ route('admin.clients.show', $client) }}" class="hover:text-ink truncate">{{ $client->company }}</a>
        <span class="text-ink/30">/</span>
        <span class="font-bold text-ink">{{ $editing ? $branch->name : __('accounts.branch.new') }}</span>
    </x-slot:breadcrumbs>

    <form method="POST" action="{{ $editing ? route('admin.clients.branches.update', [$client, $branch]) : route('admin.clients.branches.store', $client) }}"
          class="card p-5 max-w-3xl grid sm:grid-cols-2 gap-4">
        @csrf
        @if ($editing) @method('PUT') @endif

        @include('partials.branch-fields')

        <div class="sm:col-span-2 flex justify-end gap-2 pt-2 border-t border-black/5">
            <a href="{{ route('admin.clients.show', $client) }}" class="btn-ghost">{{ __('common.cancel') }}</a>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> {{ __('common.save') }}</button>
        </div>
    </form>
</x-layouts.admin>
