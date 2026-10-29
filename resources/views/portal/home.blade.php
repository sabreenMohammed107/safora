@php($user = $portal->user())

<x-layouts.portal :title="__('portal.nav.home')">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="font-mono text-[11px] text-teal-deep font-semibold tracking-widest">CLIENT PORTAL</p>
            <h1 class="text-2xl font-extrabold mt-1">{{ __('portal.home.welcome', ['name' => $user->name]) }}</h1>
            <p class="text-sm text-ink/65 mt-1">{{ $client->company }} · {{ $user->roleLabel() }}</p>
        </div>
    </div>

    <div class="grid sm:grid-cols-3 gap-3">
        <div class="card px-4 py-3">
            <p class="text-[11px] font-bold text-ink/65">{{ __('portal.home.plan') }}</p>
            <p class="font-extrabold mt-1">{{ $plan->name }}</p>
            <p class="text-[11.5px] text-ink/55">{{ __('common.cycles.'.$subscription->cycle->value) }}</p>
        </div>
        <div class="card px-4 py-3">
            <p class="text-[11px] font-bold text-ink/65">{{ __('portal.home.valid_until') }}</p>
            <p class="font-mono font-extrabold mt-1">{{ $subscription->end_date->toDateString() }}</p>
            <p @class(['text-[11.5px]', 'text-amber-deep font-bold' => $daysLeft <= 30, 'text-ink/55' => $daysLeft > 30])>{{ trans_choice('portal.home.days_left', $daysLeft, ['count' => $daysLeft]) }}</p>
        </div>
        <div class="card px-4 py-3">
            <p class="text-[11px] font-bold text-ink/65">{{ __('portal.branches.label') }}</p>
            <p class="font-extrabold mt-1">{{ $portal->currentBranch()?->name ?? __('portal.branches.all') }}</p>
            <p class="text-[11.5px] text-ink/55">{{ trans_choice('portal.home.branches_count', $branches->count(), ['count' => $branches->count()]) }}</p>
        </div>
    </div>

    @if ($portal->isAllBranches())
        <section class="card">
            <header class="px-5 py-4 border-b border-black/5">
                <h2 class="font-extrabold">{{ __('portal.home.compare_heading') }}</h2>
                <p class="text-[12px] text-ink/60 mt-0.5">{{ __('portal.home.compare_hint') }}</p>
            </header>
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead><tr class="text-[11px] font-extrabold text-ink/60 bg-canvas/60">
                        <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.branch') }}</th>
                        <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.manager') }}</th>
                        <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.users') }}</th>
                        <th class="px-4 py-2 text-end"></th>
                    </tr></thead>
                    <tbody>
                        @foreach ($branches as $branch)
                            <tr class="border-t border-black/5">
                                <td class="px-4 py-2.5"><span class="font-bold">{{ $branch->name }}</span> <span class="font-mono text-[11px] text-ink/50">{{ $branch->code }}</span></td>
                                <td class="px-4 py-2.5">{{ $branch->manager ?? '—' }}</td>
                                <td class="px-4 py-2.5 font-mono">{{ $branch->users_count }}</td>
                                <td class="px-4 py-2.5 text-end">
                                    <form method="POST" action="{{ route('portal.branch.update') }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="branch" value="{{ $branch->id }}">
                                        <button type="submit" class="btn-ghost">{{ __('portal.home.open_branch') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <section class="card">
        <header class="px-5 py-4 border-b border-black/5"><h2 class="font-extrabold">{{ __('portal.home.modules_heading') }}</h2></header>
        <div class="p-5 grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach (\App\Enums\PlanModule::cases() as $module)
                @php($included = $plan->hasModule($module))
                <div @class(['rounded-xl border px-4 py-3 flex items-center gap-3', 'border-emerald/30 bg-emerald-soft/40' => $included, 'border-black/5 bg-canvas/60 opacity-70' => ! $included])>
                    <i @class(['fa-solid w-5 text-center', 'fa-circle-check text-emerald' => $included, 'fa-lock text-ink/40' => ! $included])></i>
                    <span class="text-[13px] font-bold">{{ __('common.modules.'.$module->value) }}</span>
                </div>
            @endforeach
        </div>
    </section>
</x-layouts.portal>
