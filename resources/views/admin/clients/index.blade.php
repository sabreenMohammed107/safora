<x-layouts.admin :title="__('admin.clients.title')" eyebrow="CLIENT MANAGEMENT" :heading="__('admin.clients.heading')" :intro="__('admin.clients.intro')">
    <x-slot:breadcrumbs>
        <a href="{{ route('admin.dashboard') }}" class="hover:text-ink">{{ __('admin.nav.dashboard') }}</a>
        <span class="text-ink/30">/</span>
        <span class="font-bold text-ink">{{ __('admin.nav.clients') }}</span>
    </x-slot:breadcrumbs>

    <section class="card">
        <form method="GET" action="{{ route('admin.clients.index') }}" class="p-3 border-b border-black/5 flex flex-wrap items-center gap-2">
            <div class="inline-flex flex-wrap p-1 rounded-xl bg-canvas" role="tablist" aria-label="{{ __('admin.clients.status_filter') }}">
                @foreach ([null, ...\App\Enums\ClientStatus::cases()] as $tab)
                    @php
                        $selected = $status === $tab;
                        $count = $tab ? $statusCounts->get($tab->value, 0) : $statusCounts->sum();
                    @endphp
                    <a href="{{ route('admin.clients.index', array_filter(['status' => $tab?->value, 'q' => $search ?: null, 'category' => $categoryId])) }}"
                       role="tab" aria-selected="{{ $selected ? 'true' : 'false' }}"
                       @class(['flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] font-bold transition', 'bg-white shadow-sm text-ink' => $selected, 'text-ink/65 hover:text-ink' => ! $selected])>
                        {{ $tab ? __('common.client_status.'.$tab->value) : __('common.all') }}
                        <span class="font-mono text-[10px] text-ink/50">{{ $count }}</span>
                    </a>
                @endforeach
            </div>

            @if ($status)
                <input type="hidden" name="status" value="{{ $status->value }}">
            @endif

            <label class="flex items-center gap-2 rounded-lg bg-canvas border border-black/5 px-3 py-2 w-full sm:w-64 sm:ms-auto">
                <i class="fa-solid fa-magnifying-glass text-ink/50 text-xs"></i>
                <input name="q" value="{{ $search }}" placeholder="{{ __('admin.clients.search') }}" class="bg-transparent outline-none text-sm w-full placeholder:text-ink/40">
            </label>
            <select name="category" onchange="this.form.submit()" class="rounded-lg bg-canvas border border-black/5 px-3 py-2 text-sm font-semibold text-ink/80 outline-none" aria-label="{{ __('common.fields.category') }}">
                <option value="">{{ __('admin.clients.all_categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-dark">{{ __('common.search') }}</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[860px]">
                <thead>
                    <tr class="text-[11px] font-extrabold text-ink/65 bg-canvas/60 text-start">
                        <th class="px-4 py-2.5 text-start">{{ __('admin.clients.columns.company') }}</th>
                        <th class="px-4 py-2.5 text-start">{{ __('admin.clients.columns.contact') }}</th>
                        <th class="px-4 py-2.5 text-start">{{ __('common.fields.category') }}</th>
                        <th class="px-4 py-2.5 text-start">{{ __('admin.clients.columns.plan') }}</th>
                        <th class="px-4 py-2.5 text-start">{{ __('admin.clients.columns.status') }}</th>
                        <th class="px-4 py-2.5 text-start">{{ __('admin.clients.columns.expiry') }}</th>
                        <th class="px-4 py-2.5 text-end">{{ __('admin.clients.columns.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                        @php($subscription = $client->subscription)
                        <tr class="border-t border-black/5 hover:bg-canvas/50">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.clients.show', $client) }}" class="font-extrabold hover:text-teal-deep">{{ $client->company }}</a>
                                <p class="font-mono text-[11px] text-ink/60 whitespace-nowrap">{{ $client->code }} · {{ $client->cr_number }}</p>
                                @if ($client->documents_count)
                                    <p class="text-[11px] font-bold text-teal-deep mt-0.5"><i class="fa-solid fa-paperclip text-[10px]"></i> {{ trans_choice('admin.clients.documents_count', $client->documents_count, ['count' => $client->documents_count]) }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ $client->contact_name }}</p>
                                <p class="text-[11px] text-ink/65 font-mono" dir="ltr">{{ $client->phone }}</p>
                                <p class="text-[11px] text-ink/65 font-mono break-all">{{ $client->email }}</p>
                            </td>
                            <td class="px-4 py-3 text-[12.5px]">
                                <i class="fa-solid {{ $client->category?->icon }} text-ink/40 text-[11px]"></i> {{ $client->category?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($subscription)
                                    <p class="font-bold">{{ $subscription->plan->name }}</p>
                                    <p class="text-[11.5px] text-ink/65">
                                        {{ __('common.cycles.'.$subscription->cycle->value) }} ·
                                        {{ trans_choice('admin.clients.branches_count', $client->active_branches_count, ['count' => $client->active_branches_count]) }} ·
                                        <span class="font-mono font-bold text-ink">{{ \App\Services\PriceQuote::toDecimal($pricing->quoteSubscription($subscription, $client->active_branches_count)->total) }}</span> {{ __('common.currency') }}
                                    </p>
                                @else
                                    <span class="text-ink/40">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <x-client-status-badge :status="$client->status" />
                            </td>
                            <td class="px-4 py-3 text-[12.5px] whitespace-nowrap">
                                @if ($subscription)
                                    <span @class(['font-mono', 'text-rose font-bold' => $subscription->isExpired(), 'text-amber-deep font-bold' => ! $subscription->isExpired() && $subscription->end_date->lte(today()->addDays(30))])>{{ $subscription->end_date->toDateString() }}</span>
                                @else
                                    <span class="text-ink/40">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-end whitespace-nowrap">
                                <a href="{{ route('admin.clients.show', $client) }}" @class(['btn-primary' => $client->status === \App\Enums\ClientStatus::Pending, 'btn-ghost' => $client->status !== \App\Enums\ClientStatus::Pending])>
                                    {{ $client->status === \App\Enums\ClientStatus::Pending ? __('admin.clients.review') : __('admin.clients.manage') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-ink/55">
                                <i class="fa-solid fa-building-circle-xmark text-2xl text-ink/25 block mb-2"></i>{{ __('admin.clients.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($clients->hasPages())
            <div class="px-4 py-3 border-t border-black/5">{{ $clients->links() }}</div>
        @endif
    </section>
</x-layouts.admin>
