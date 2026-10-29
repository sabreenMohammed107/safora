@php($editing = $branch->exists)

<x-layouts.portal :title="$editing ? $branch->name : __('accounts.branch.new')">
    <div>
        <a href="{{ route('portal.branches.index') }}" class="text-[12px] font-bold text-teal-deep hover:underline"><i class="fa-solid fa-arrow-right ltr:rotate-180 text-[10px]"></i> {{ __('accounts.branch.heading') }}</a>
        <h1 class="text-2xl font-extrabold mt-1">{{ $editing ? $branch->name : __('accounts.branch.new') }} @if ($editing)<span class="font-mono text-sm text-ink/45">{{ $branch->code }}</span>@endif</h1>
    </div>

    <form method="POST" action="{{ $editing ? route('portal.branches.update', $branch) : route('portal.branches.store') }}"
          class="card p-5 max-w-3xl grid sm:grid-cols-2 gap-4">
        @csrf
        @if ($editing) @method('PUT') @endif

        @include('partials.branch-fields')

        <div class="sm:col-span-2 flex justify-end gap-2 pt-2 border-t border-black/5">
            <a href="{{ route('portal.branches.index') }}" class="btn-ghost">{{ __('common.cancel') }}</a>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> {{ __('common.save') }}</button>
        </div>
    </form>
</x-layouts.portal>
