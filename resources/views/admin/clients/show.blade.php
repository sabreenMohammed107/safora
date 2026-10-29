@php
    use App\Enums\ClientStatus;
    use App\Services\PriceQuote;

    $subscription = $client->subscription;
@endphp

<x-layouts.admin :title="$client->company" :eyebrow="$client->code" :heading="$client->company">
    <x-slot:breadcrumbs>
        <a href="{{ route('admin.dashboard') }}" class="hover:text-ink">{{ __('admin.nav.dashboard') }}</a>
        <span class="text-ink/30">/</span>
        <a href="{{ route('admin.clients.index') }}" class="hover:text-ink">{{ __('admin.nav.clients') }}</a>
        <span class="text-ink/30">/</span>
        <span class="font-bold text-ink truncate">{{ $client->company }}</span>
    </x-slot:breadcrumbs>

    <x-slot:actions>
        <div class="flex flex-wrap items-center gap-2">
            <x-client-status-badge :status="$client->status" class="text-[12px] px-3 py-1" />
            @if ($client->owner && auth('admin')->user()->can('clients.impersonate'))
                <form method="POST" action="{{ route('admin.clients.impersonate', $client) }}">
                    @csrf
                    <button type="submit" class="btn-ghost"><i class="fa-solid fa-user-secret"></i> {{ __('admin.clients.impersonate') }}</button>
                </form>
            @endif
        </div>
    </x-slot:actions>

    <div class="grid xl:grid-cols-3 gap-5">
        <div class="xl:col-span-2 space-y-5">

            {{-- ───── Review / status actions ───── --}}
            @if ($client->status === ClientStatus::Pending && auth('admin')->user()->can('clients.approve'))
                <section class="card border-brand/40" x-data="{ mode: @js($errors->has('reason') ? 'reject' : 'approve') }">
                    <header class="px-5 py-4 border-b border-black/5 flex flex-wrap items-center justify-between gap-2">
                        <h2 class="font-extrabold"><i class="fa-solid fa-clipboard-check text-brand-deep"></i> {{ __('admin.clients.review_heading') }}</h2>
                        <div class="inline-flex p-1 rounded-xl bg-canvas" role="tablist">
                            <button type="button" role="tab" @click="mode = 'approve'" :aria-selected="(mode === 'approve').toString()" class="px-3 py-1.5 rounded-lg text-[12px] font-bold" :class="mode === 'approve' ? 'bg-white shadow-sm' : 'text-ink/60'">{{ __('admin.clients.approve') }}</button>
                            <button type="button" role="tab" @click="mode = 'reject'" :aria-selected="(mode === 'reject').toString()" class="px-3 py-1.5 rounded-lg text-[12px] font-bold" :class="mode === 'reject' ? 'bg-white shadow-sm' : 'text-ink/60'">{{ __('admin.clients.reject') }}</button>
                        </div>
                    </header>

                    <form x-show="mode === 'approve'" method="POST" action="{{ route('admin.clients.approve', $client) }}" class="p-5 grid sm:grid-cols-2 gap-4">
                        @csrf
                        <label class="block">
                            <span class="lbl">{{ __('admin.clients.fields.plan') }} *</span>
                            <select name="plan_id" required class="fld">
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>{{ $plan->name }} — {{ number_format((float) $plan->price_monthly) }} {{ __('common.currency') }}/{{ __('common.cycles.monthly') }}</option>
                                @endforeach
                            </select>
                            @error('plan_id')<p class="fld-error">{{ $message }}</p>@enderror
                        </label>
                        <label class="block">
                            <span class="lbl">{{ __('admin.clients.fields.cycle') }} *</span>
                            <select name="cycle" required class="fld">
                                @foreach ($cycles as $cycle)
                                    <option value="{{ $cycle->value }}" @selected(old('cycle') === $cycle->value)>{{ __('common.cycles.'.$cycle->value) }}</option>
                                @endforeach
                            </select>
                            @error('cycle')<p class="fld-error">{{ $message }}</p>@enderror
                        </label>
                        <label class="block">
                            <span class="lbl">{{ __('admin.clients.fields.start_date') }} *</span>
                            <input type="date" name="start_date" value="{{ old('start_date', today()->toDateString()) }}" required class="fld font-mono">
                            @error('start_date')<p class="fld-error">{{ $message }}</p>@enderror
                        </label>
                        <label class="block">
                            <span class="lbl">{{ __('admin.clients.fields.discount') }}</span>
                            <input type="number" name="discount" min="0" max="100" step="0.01" value="{{ old('discount', 0) }}" class="fld font-mono">
                            @error('discount')<p class="fld-error">{{ $message }}</p>@enderror
                        </label>
                        <div class="sm:col-span-2 flex items-center justify-between gap-3">
                            <p class="text-[12px] text-ink/60">{{ __('admin.clients.approve_hint', ['email' => $client->owner?->email ?? $client->email]) }}</p>
                            <button type="submit" class="btn-primary"><i class="fa-solid fa-circle-check"></i> {{ __('admin.clients.approve_submit') }}</button>
                        </div>
                    </form>

                    <form x-show="mode === 'reject'" x-cloak method="POST" action="{{ route('admin.clients.reject', $client) }}" class="p-5 space-y-3">
                        @csrf
                        <label class="block">
                            <span class="lbl">{{ __('admin.clients.fields.reject_reason') }} *</span>
                            <textarea name="reason" rows="3" required class="fld">{{ old('reason') }}</textarea>
                            @error('reason')<p class="fld-error">{{ $message }}</p>@enderror
                        </label>
                        <div class="flex justify-end"><button type="submit" class="btn-danger"><i class="fa-solid fa-circle-xmark"></i> {{ __('admin.clients.reject_submit') }}</button></div>
                    </form>
                </section>
            @elseif ($client->status === ClientStatus::Active && auth('admin')->user()->can('clients.suspend'))
                <details class="card group" @if ($errors->has('reason')) open @endif>
                    <summary class="px-5 py-4 flex items-center gap-2 cursor-pointer font-extrabold text-rose-deep">
                        <i class="fa-solid fa-ban"></i> {{ __('admin.clients.suspend') }}
                        <i class="fa-solid fa-chevron-down ms-auto text-xs text-ink/40 group-open:rotate-180 transition"></i>
                    </summary>
                    <form method="POST" action="{{ route('admin.clients.suspend', $client) }}" class="px-5 pb-5 space-y-3">
                        @csrf
                        <label class="block">
                            <span class="lbl">{{ __('admin.clients.fields.suspend_reason') }} *</span>
                            <textarea name="reason" rows="2" required class="fld">{{ old('reason') }}</textarea>
                            @error('reason')<p class="fld-error">{{ $message }}</p>@enderror
                        </label>
                        <div class="flex justify-end"><button type="submit" class="btn-danger">{{ __('admin.clients.suspend_submit') }}</button></div>
                    </form>
                </details>
            @elseif (in_array($client->status, [ClientStatus::Suspended, ClientStatus::Rejected], true))
                <section @class(['rounded-2xl px-5 py-4 flex flex-wrap items-center gap-3', 'bg-rose-soft text-rose-deep' => $client->status === ClientStatus::Suspended, 'bg-canvas text-ink/70 border border-black/5' => $client->status === ClientStatus::Rejected])>
                    <i class="fa-solid {{ $client->status === ClientStatus::Suspended ? 'fa-ban' : 'fa-circle-xmark' }}"></i>
                    <p class="text-[13px] font-bold flex-1">{{ __('admin.clients.reason_label.'.$client->status->value) }}: {{ $client->status_reason ?: '—' }}</p>
                    @if ($client->status === ClientStatus::Suspended && auth('admin')->user()->can('clients.suspend'))
                        <form method="POST" action="{{ route('admin.clients.reactivate', $client) }}">
                            @csrf
                            <button type="submit" class="btn-dark"><i class="fa-solid fa-rotate-left"></i> {{ __('admin.clients.reactivate') }}</button>
                        </form>
                    @endif
                </section>
            @endif

            {{-- ───── Company ───── --}}
            <section class="card">
                <header class="px-5 py-4 border-b border-black/5"><h2 class="font-extrabold">{{ __('admin.clients.sections.company') }}</h2></header>
                <dl class="p-5 grid sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4 text-[13px]">
                    @foreach ([
                        'cr_number' => [$client->cr_number, true],
                        'category' => [$client->category?->name ?? '—', false],
                        'contact_name' => [$client->contact_name, false],
                        'phone' => [$client->phone ?? '—', true],
                        'email' => [$client->email, true],
                        'registered_at' => [$client->registered_at->toDateString(), true],
                    ] as $field => [$value, $mono])
                        <div>
                            <dt class="lbl">{{ __('common.fields.'.$field) }}</dt>
                            <dd @class(['mt-1 font-bold', 'font-mono' => $mono]) @if ($mono) dir="ltr" @endif>{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            {{-- ───── Branches ───── --}}
            <section class="card">
                <header class="px-5 py-4 border-b border-black/5 flex items-center justify-between">
                    <h2 class="font-extrabold">{{ __('admin.clients.sections.branches') }} <span class="font-mono text-xs text-ink/50">{{ $activeBranches }}/{{ $client->branches->count() }}</span></h2>
                    @if ($subscription && $activeBranches > $subscription->plan->included_branches)
                        <span class="text-[11.5px] font-bold text-amber-deep"><i class="fa-solid fa-circle-info"></i> {{ __('admin.clients.extra_branch_note', ['count' => $activeBranches - $subscription->plan->included_branches]) }}</span>
                    @endif
                </header>
                <div class="overflow-x-auto">
                    <table class="w-full text-[13px]">
                        <thead><tr class="text-[11px] font-extrabold text-ink/60 bg-canvas/60">
                            <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.code') }}</th>
                            <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.branch') }}</th>
                            <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.manager') }}</th>
                            <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.users') }}</th>
                            <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.status') }}</th>
                        </tr></thead>
                        <tbody>
                            @forelse ($client->branches as $branch)
                                <tr class="border-t border-black/5">
                                    <td class="px-4 py-2.5 font-mono text-[12px]">{{ $branch->code }}</td>
                                    <td class="px-4 py-2.5 font-bold">{{ $branch->name }}<span class="block text-[11px] font-normal text-ink/55">{{ $branch->city }}</span></td>
                                    <td class="px-4 py-2.5">{{ $branch->manager ?? '—' }}</td>
                                    <td class="px-4 py-2.5 font-mono">{{ $branch->users_count }}</td>
                                    <td class="px-4 py-2.5">
                                        <span @class(['pill', 'bg-emerald-soft text-emerald-deep' => $branch->active, 'bg-canvas text-ink/55' => ! $branch->active])>{{ $branch->active ? __('common.active') : __('common.inactive') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-6 text-center text-ink/55">{{ __('admin.clients.no_branches') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- ───── Users ───── --}}
            <section class="card">
                <header class="px-5 py-4 border-b border-black/5"><h2 class="font-extrabold">{{ __('admin.clients.sections.users') }} <span class="font-mono text-xs text-ink/50">{{ $client->users->count() }}</span></h2></header>
                <div class="overflow-x-auto">
                    <table class="w-full text-[13px]">
                        <thead><tr class="text-[11px] font-extrabold text-ink/60 bg-canvas/60">
                            <th class="px-4 py-2 text-start">{{ __('common.fields.name') }}</th>
                            <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.role') }}</th>
                            <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.branch') }}</th>
                            <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.last_login') }}</th>
                            <th class="px-4 py-2 text-start">{{ __('admin.clients.columns.status') }}</th>
                        </tr></thead>
                        <tbody>
                            @foreach ($client->users as $user)
                                <tr class="border-t border-black/5">
                                    <td class="px-4 py-2.5"><span class="font-bold">{{ $user->name }}</span><span class="block font-mono text-[11px] text-ink/55">{{ $user->email }}</span></td>
                                    <td class="px-4 py-2.5 whitespace-nowrap">{{ $user->roleLabel() }}</td>
                                    <td class="px-4 py-2.5 whitespace-nowrap">{{ $user->branch?->name ?? __('portal.branches.all') }}</td>
                                    <td class="px-4 py-2.5 text-[12px] text-ink/65 whitespace-nowrap">{{ $user->last_login_at?->diffForHumans() ?? '—' }}</td>
                                    <td class="px-4 py-2.5">
                                        <span @class(['pill', 'bg-emerald-soft text-emerald-deep' => $user->active, 'bg-canvas text-ink/55' => ! $user->active])>{{ $user->active ? __('common.active') : __('common.inactive') }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- ───── Documents ───── --}}
            <section class="card">
                <header class="px-5 py-4 border-b border-black/5"><h2 class="font-extrabold">{{ __('admin.clients.sections.documents') }}</h2></header>
                <ul class="divide-y divide-black/5">
                    @forelse ($client->documents as $document)
                        <li class="px-5 py-3 flex items-center gap-3">
                            <i @class(['fa-solid text-lg', 'fa-file-pdf text-rose' => $document->mime_type === 'application/pdf', 'fa-file-image text-teal' => $document->mime_type !== 'application/pdf'])></i>
                            <span class="flex-1 min-w-0">
                                <span class="block font-bold truncate">{{ $document->original_name }}</span>
                                <span class="block text-[11px] text-ink/55 font-mono">{{ \Illuminate\Support\Number::fileSize($document->size) }} · {{ $document->created_at->toDateString() }}</span>
                            </span>
                            @can('clients.documents')
                                <a href="{{ route('admin.clients.documents.show', [$client, $document]) }}" class="btn-ghost"><i class="fa-solid fa-download"></i> {{ __('common.download') }}</a>
                            @endcan
                        </li>
                    @empty
                        <li class="px-5 py-6 text-center text-sm text-ink/55">{{ __('admin.clients.no_documents') }}</li>
                    @endforelse
                </ul>
            </section>
        </div>

        <div class="space-y-5">
            {{-- ───── Subscription ───── --}}
            <section class="card">
                <header class="px-5 py-4 border-b border-black/5"><h2 class="font-extrabold">{{ __('admin.clients.sections.subscription') }}</h2></header>
                @if ($subscription && $quote)
                    <div class="p-5 space-y-3 text-[13px]">
                        <div class="flex items-center justify-between">
                            <p class="font-extrabold text-base">{{ $subscription->plan->name }}</p>
                            <span class="pill bg-teal-soft text-teal-deep">{{ __('common.cycles.'.$subscription->cycle->value) }}</span>
                        </div>
                        <p class="text-ink/65 font-mono text-[12px]">{{ $subscription->start_date->toDateString() }} → <span @class(['text-rose font-bold' => $subscription->isExpired()])>{{ $subscription->end_date->toDateString() }}</span></p>
                        <dl class="rounded-xl bg-canvas p-3 space-y-1.5">
                            <div class="flex justify-between"><dt class="text-ink/65">{{ __('admin.clients.price.base') }}</dt><dd class="font-mono">{{ PriceQuote::toDecimal($quote->basePrice) }}</dd></div>
                            <div class="flex justify-between"><dt class="text-ink/65">{{ __('admin.clients.price.extra_branches', ['count' => $quote->extraBranches, 'price' => PriceQuote::toDecimal($quote->extraBranchPrice)]) }}</dt><dd class="font-mono">{{ PriceQuote::toDecimal($quote->extraBranchesTotal) }}</dd></div>
                            @if ($quote->discountAmount)
                                <div class="flex justify-between text-emerald-deep"><dt>{{ __('admin.clients.price.discount', ['percent' => $quote->discountPercent]) }}</dt><dd class="font-mono">−{{ PriceQuote::toDecimal($quote->discountAmount) }}</dd></div>
                            @endif
                            <div class="flex justify-between"><dt class="text-ink/65">{{ __('admin.clients.price.vat', ['percent' => $quote->vatRate * 100]) }}</dt><dd class="font-mono">{{ PriceQuote::toDecimal($quote->vatAmount) }}</dd></div>
                            <div class="flex justify-between border-t border-black/10 pt-1.5 font-extrabold"><dt>{{ __('admin.clients.price.total') }}</dt><dd class="font-mono">{{ PriceQuote::toDecimal($quote->total) }} {{ __('common.currency') }}</dd></div>
                        </dl>
                    </div>
                @else
                    <p class="p-5 text-sm text-ink/55">{{ __('admin.clients.no_subscription') }}</p>
                @endif
            </section>

            {{-- ───── Sectors ───── --}}
            <section class="card">
                <header class="px-5 py-4 border-b border-black/5"><h2 class="font-extrabold">{{ __('admin.clients.sections.sectors') }}</h2></header>
                <div class="p-5 flex flex-wrap gap-1.5">
                    @foreach ($client->category?->sectors ?? [] as $sector)
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-canvas text-[12px] font-bold" title="{{ __('admin.clients.sector_inherited') }}"><i class="fa-solid fa-lock text-[9px] text-ink/45"></i>{{ $sector->name }}</span>
                    @endforeach
                    @foreach ($client->extraSectors as $sector)
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-teal-soft text-teal-deep text-[12px] font-bold">{{ $sector->name }} <span class="text-[9.5px]">· {{ __('admin.clients.sector_source.'.$sector->pivot->source->value) }}</span></span>
                    @endforeach
                </div>
            </section>

            {{-- ───── Activity ───── --}}
            <section class="card">
                <header class="px-5 py-4 border-b border-black/5"><h2 class="font-extrabold">{{ __('admin.clients.sections.activity') }}</h2></header>
                <ol class="p-5 space-y-3">
                    @forelse ($client->activityLogs as $log)
                        <li class="flex gap-3 text-[12.5px]">
                            <span class="mt-1.5 w-2 h-2 shrink-0 rounded-full bg-teal"></span>
                            <span>
                                <span class="block font-bold">{{ $log->description() }}</span>
                                <span class="block text-[11px] text-ink/55">{{ $log->actor_name === 'system' ? __('common.system') : $log->actor_name }} · <span class="font-mono">{{ $log->created_at->format('Y-m-d H:i') }}</span></span>
                            </span>
                        </li>
                    @empty
                        <li class="text-sm text-ink/55">{{ __('admin.clients.no_activity') }}</li>
                    @endforelse
                </ol>
            </section>
        </div>
    </div>
</x-layouts.admin>
