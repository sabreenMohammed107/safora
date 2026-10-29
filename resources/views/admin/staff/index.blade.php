<x-layouts.admin :title="__('admin.staff.title')" eyebrow="STAFF & ROLES" :heading="__('admin.staff.heading')" :intro="__('admin.staff.intro')">
    <x-slot:breadcrumbs>
        <a href="{{ route('admin.dashboard') }}" class="hover:text-ink">{{ __('admin.nav.dashboard') }}</a>
        <span class="text-ink/30">/</span>
        <span class="font-bold text-ink">{{ __('admin.nav.staff') }}</span>
    </x-slot:breadcrumbs>

    <x-slot:actions>
        <div class="flex gap-2">
            @can('create', \App\Models\AdminRole::class)
                <a href="{{ route('admin.roles.create') }}" class="btn-ghost"><i class="fa-solid fa-shield-halved"></i> {{ __('admin.roles.new') }}</a>
            @endcan
            @can('create', \App\Models\Admin::class)
                <a href="{{ route('admin.staff.create') }}" class="btn-primary"><i class="fa-solid fa-user-plus"></i> {{ __('admin.staff.new') }}</a>
            @endcan
        </div>
    </x-slot:actions>

    <section class="card">
        <header class="px-5 py-4 border-b border-black/5"><h2 class="font-extrabold">{{ __('admin.staff.members') }} <span class="font-mono text-xs text-ink/50">{{ $staff->count() }}</span></h2></header>
        <div class="overflow-x-auto">
            <table class="w-full text-[13px] min-w-[720px]">
                <thead><tr class="text-[11px] font-extrabold text-ink/60 bg-canvas/60">
                    <th class="px-4 py-2 text-start">{{ __('common.fields.name') }}</th>
                    <th class="px-4 py-2 text-start">{{ __('admin.staff.columns.role') }}</th>
                    <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.last_login') }}</th>
                    <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.status') }}</th>
                    <th class="px-4 py-2 text-end"></th>
                </tr></thead>
                <tbody>
                    @foreach ($staff as $member)
                        <tr class="border-t border-black/5">
                            <td class="px-4 py-2.5">
                                <span class="font-bold">{{ $member->name }}</span>
                                @if ($member->is(auth('admin')->user()))<span class="pill bg-teal-soft text-teal-deep ms-1">{{ __('common.you') }}</span>@endif
                                <span class="block font-mono text-[11px] text-ink/55">{{ $member->email }}</span>
                            </td>
                            <td class="px-4 py-2.5 whitespace-nowrap">
                                @if ($member->is_super_admin)
                                    <span class="pill bg-brand-soft text-brand-deep"><i class="fa-solid fa-crown text-[9px]"></i> {{ __('admin.staff.super_admin') }}</span>
                                @else
                                    {{ $member->role?->name ?? '—' }}
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-[12px] text-ink/65 whitespace-nowrap">{{ $member->last_login_at?->diffForHumans() ?? '—' }}</td>
                            <td class="px-4 py-2.5">
                                <span @class(['pill', 'bg-emerald-soft text-emerald-deep' => $member->active, 'bg-canvas text-ink/55' => ! $member->active])>{{ $member->active ? __('common.active') : __('common.inactive') }}</span>
                                @if ($member->must_change_password)
                                    <span class="pill bg-amber-soft text-amber-deep" title="{{ __('accounts.password.pending_change') }}"><i class="fa-solid fa-key text-[9px]"></i> {{ __('accounts.password.temporary') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-end whitespace-nowrap">
                                @can('update', $member)
                                    <a href="{{ route('admin.staff.edit', $member) }}" class="btn-ghost">{{ __('common.edit') }}</a>
                                    @unless ($member->is(auth('admin')->user()))
                                        <form method="POST" action="{{ route('admin.staff.password', $member) }}" class="inline"
                                              onsubmit="return confirm(@js(__('accounts.password.confirm_reset', ['name' => $member->name])))">
                                            @csrf
                                            <button type="submit" class="btn-ghost" title="{{ __('accounts.password.reset') }}" aria-label="{{ __('accounts.password.reset') }}"><i class="fa-solid fa-key"></i></button>
                                        </form>
                                    @endunless
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    @can('viewAny', \App\Models\AdminRole::class)
        <section class="card">
            <header class="px-5 py-4 border-b border-black/5"><h2 class="font-extrabold">{{ __('admin.roles.heading') }} <span class="font-mono text-xs text-ink/50">{{ $roles->count() }}</span></h2></header>
            <ul class="divide-y divide-black/5">
                @forelse ($roles as $role)
                    <li class="px-5 py-3 flex flex-wrap items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-teal-soft text-teal-deep flex items-center justify-center"><i class="fa-solid fa-shield-halved"></i></span>
                        <span class="flex-1 min-w-0">
                            <span class="block font-extrabold">{{ $role->name }}</span>
                            <span class="block text-[11.5px] text-ink/60">
                                {{ trans_choice('admin.roles.permissions_count', $role->permissions->count(), ['count' => $role->permissions->count()]) }}
                                · {{ trans_choice('admin.roles.staff_count', $role->admins_count, ['count' => $role->admins_count]) }}
                                @if ($role->description) · {{ $role->description }} @endif
                            </span>
                        </span>
                        @can('update', $role)
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn-ghost">{{ __('common.edit') }}</a>
                        @endcan
                        @can('delete', $role)
                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm(@js(__('common.confirm_delete')))">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-ghost text-rose-deep!"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        @endcan
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-ink/55">{{ __('admin.roles.empty') }}</li>
                @endforelse
            </ul>
        </section>
    @endcan
</x-layouts.admin>
