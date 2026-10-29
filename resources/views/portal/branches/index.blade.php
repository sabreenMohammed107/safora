<x-layouts.portal :title="__('accounts.branch.heading')">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="font-mono text-[11px] text-teal-deep font-semibold tracking-widest">BRANCHES</p>
            <h1 class="text-2xl font-extrabold mt-1">{{ __('accounts.branch.heading') }}</h1>
            <p class="text-sm text-ink/65 mt-1 max-w-2xl">{{ __('accounts.branch.intro') }}</p>
        </div>
        <a href="{{ route('portal.branches.create') }}" class="btn-primary"><i class="fa-solid fa-plus"></i> {{ __('accounts.branch.new') }}</a>
    </div>

    @if ($subscription && $quote)
        <section class="card p-5 grid sm:grid-cols-3 gap-4 text-[13px]">
            <div>
                <p class="lbl">{{ __('accounts.branch.active_branches') }}</p>
                <p class="mt-1 font-mono text-xl font-extrabold">{{ $activeCount }}<span class="text-ink/35 text-sm"> / {{ $subscription->plan->max_sites ?? '∞' }}</span></p>
                <p class="text-[11.5px] text-ink/55">{{ trans_choice('accounts.branch.included_count', $subscription->plan->included_branches, ['count' => $subscription->plan->included_branches]) }}</p>
            </div>
            <div>
                <p class="lbl">{{ __('admin.clients.price.total') }} ({{ __('common.cycles.'.$subscription->cycle->value) }})</p>
                <p class="mt-1 font-mono text-xl font-extrabold">{{ \App\Services\PriceQuote::toDecimal($quote->total) }} <span class="text-sm text-ink/55">{{ __('common.currency') }}</span></p>
                <p class="text-[11.5px] text-ink/55">{{ __('admin.clients.price.extra_branches', ['count' => $quote->extraBranches, 'price' => \App\Services\PriceQuote::toDecimal($quote->extraBranchPrice)]) }}</p>
            </div>
            <div>
                <p class="lbl">{{ __('accounts.branch.next_branch') }}</p>
                <p @class(['mt-1 font-mono text-xl font-extrabold', 'text-amber-deep' => $branchCost > 0, 'text-emerald-deep' => $branchCost === 0])>
                    {{ $branchCost > 0 ? '+'.\App\Services\PriceQuote::toDecimal($branchCost) : '0.00' }} <span class="text-sm text-ink/55">{{ __('common.currency') }}</span>
                </p>
                <p class="text-[11.5px] text-ink/55">{{ $branchCost > 0 ? __('accounts.branch.vat_included') : __('accounts.branch.included_note') }}</p>
            </div>
        </section>
    @endif

    <section class="card">
        <div class="overflow-x-auto">
            <table class="w-full text-[13px] min-w-[640px]">
                <thead><tr class="text-[11px] font-extrabold text-ink/60 bg-canvas/60">
                    <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.code') }}</th>
                    <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.branch') }}</th>
                    <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.manager') }}</th>
                    <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.users') }}</th>
                    <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.status') }}</th>
                    <th class="px-4 py-2"></th>
                </tr></thead>
                <tbody>
                    @foreach ($branches as $branch)
                        <tr class="border-t border-black/5">
                            <td class="px-4 py-2.5 font-mono text-[12px]">{{ $branch->code }}</td>
                            <td class="px-4 py-2.5 font-bold">{{ $branch->name }}<span class="block text-[11px] font-normal text-ink/55">{{ $branch->city }}</span></td>
                            <td class="px-4 py-2.5">{{ $branch->manager ?? '—' }}</td>
                            <td class="px-4 py-2.5 font-mono">{{ $branch->users_count }}</td>
                            <td class="px-4 py-2.5"><span @class(['pill', 'bg-emerald-soft text-emerald-deep' => $branch->active, 'bg-canvas text-ink/55' => ! $branch->active])>{{ $branch->active ? __('common.active') : __('common.inactive') }}</span></td>
                            <td class="px-4 py-2.5 text-end"><a href="{{ route('portal.branches.edit', $branch) }}" class="btn-ghost">{{ __('common.edit') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.portal>
