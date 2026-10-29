<x-layouts.portal :title="__('portal.team.title')">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="font-mono text-[11px] text-teal-deep font-semibold tracking-widest">TEAM & ROLES</p>
            <h1 class="text-2xl font-extrabold mt-1">{{ __('portal.team.heading') }}</h1>
            <p class="text-sm text-ink/65 mt-1 max-w-2xl">{{ __('portal.team.intro') }}</p>
            <p class="mt-2 pill bg-teal-soft text-teal-deep">
                <i class="fa-solid fa-layer-group text-[10px]"></i>
                <bdi>{{ $plan?->name ?? '—' }}</bdi> · {{ trans_choice('portal.team.available_permissions', $permissionCount, ['count' => $permissionCount]) }}
            </p>
        </div>
        <div class="flex gap-2">
            @can('roles.manage')
                <a href="{{ route('portal.team.roles.create') }}" class="btn-ghost"><i class="fa-solid fa-shield-halved"></i> {{ __('portal.team.new_role') }}</a>
            @endcan
            @can('users.manage')
                <a href="{{ route('portal.team.members.create') }}" class="btn-primary"><i class="fa-solid fa-user-plus"></i> {{ __('portal.team.new_member') }}</a>
            @endcan
        </div>
    </div>

    @can('users.manage')
        <section class="card">
            <header class="px-5 py-4 border-b border-black/5"><h2 class="font-extrabold">{{ __('portal.team.members') }} <span class="font-mono text-xs text-ink/50">{{ $members->count() }}</span></h2></header>
            <div class="overflow-x-auto">
                <table class="w-full text-[13px] min-w-[720px]">
                    <thead><tr class="text-[11px] font-extrabold text-ink/60 bg-canvas/60">
                        <th class="px-4 py-2 text-start">{{ __('common.fields.name') }}</th>
                        <th class="px-4 py-2 text-start">{{ __('portal.team.columns.role') }}</th>
                        <th class="px-4 py-2 text-start">{{ __('portal.branches.label') }}</th>
                        <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.last_login') }}</th>
                        <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.status') }}</th>
                        <th class="px-4 py-2 text-end"></th>
                    </tr></thead>
                    <tbody>
                        @foreach ($members as $member)
                            <tr class="border-t border-black/5">
                                <td class="px-4 py-2.5">
                                    <span class="font-bold">{{ $member->name }}</span>
                                    @if ($member->is($portal->user()))<span class="pill bg-teal-soft text-teal-deep ms-1">{{ __('common.you') }}</span>@endif
                                    <span class="block font-mono text-[11px] text-ink/55">{{ $member->email }}</span>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    @if ($member->isOwner())
                                        <span class="pill bg-brand-soft text-brand-deep"><i class="fa-solid fa-crown text-[9px]"></i> {{ $member->roleLabel() }}</span>
                                    @else
                                        {{ $member->roleLabel() }}
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">{{ $member->branch?->name ?? __('portal.branches.all') }}</td>
                                <td class="px-4 py-2.5 text-[12px] text-ink/65 whitespace-nowrap">{{ $member->last_login_at?->diffForHumans() ?? '—' }}</td>
                                <td class="px-4 py-2.5">
                                    <span @class(['pill', 'bg-emerald-soft text-emerald-deep' => $member->active, 'bg-canvas text-ink/55' => ! $member->active])>{{ $member->active ? __('common.active') : __('common.inactive') }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-end">
                                    @can('update', $member)
                                        <a href="{{ route('portal.team.members.edit', $member) }}" class="btn-ghost">{{ __('common.edit') }}</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endcan

    @can('roles.manage')
        <section class="card">
            <header class="px-5 py-4 border-b border-black/5">
                <h2 class="font-extrabold">{{ __('portal.team.roles') }} <span class="font-mono text-xs text-ink/50">{{ $roles->count() }}</span></h2>
                <p class="text-[12px] text-ink/60 mt-0.5">{{ __('portal.team.roles_hint') }}</p>
            </header>
            <ul class="divide-y divide-black/5">
                @foreach ($roles as $role)
                    @php($effective = $role->permissions->filter(fn ($permission) => $permission->isAvailableOn($plan))->count())
                    <li class="px-5 py-3 flex flex-wrap items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-teal-soft text-teal-deep flex items-center justify-center"><i class="fa-solid fa-shield-halved"></i></span>
                        <span class="flex-1 min-w-0">
                            <span class="block font-extrabold">{{ $role->displayName() }}
                                @if ($role->key)<span class="pill bg-canvas text-ink/55 ms-1">{{ __('portal.team.default_role') }}</span>@endif
                            </span>
                            <span class="block text-[11.5px] text-ink/60">
                                {{ trans_choice('portal.team.permissions_count', $effective, ['count' => $effective]) }}
                                · {{ trans_choice('portal.team.members_count', $role->users_count, ['count' => $role->users_count]) }}
                                @if ($role->description) · {{ $role->description }} @endif
                            </span>
                        </span>
                        @can('update', $role)
                            <a href="{{ route('portal.team.roles.edit', $role) }}" class="btn-ghost">{{ __('common.edit') }}</a>
                        @endcan
                        @can('delete', $role)
                            <form method="POST" action="{{ route('portal.team.roles.destroy', $role) }}" onsubmit="return confirm(@js(__('common.confirm_delete')))">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-ghost text-rose-deep!" aria-label="{{ __('common.delete') }}"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        @endcan
                    </li>
                @endforeach
            </ul>
        </section>
    @endcan
</x-layouts.portal>
