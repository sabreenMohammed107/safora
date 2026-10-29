<x-layouts.admin :title="__('admin.dashboard.title')" eyebrow="ADMIN DASHBOARD" :heading="__('admin.dashboard.heading', ['name' => auth('admin')->user()->name])" :intro="__('admin.dashboard.intro')">
    <x-slot:breadcrumbs><span class="font-bold text-ink">{{ __('admin.nav.dashboard') }}</span></x-slot:breadcrumbs>

    @cannot('clients.view')
        <section class="card p-8 text-center">
            <i class="fa-solid fa-user-shield text-3xl text-ink/25"></i>
            <p class="mt-3 font-extrabold">{{ __('admin.dashboard.limited_title') }}</p>
            <p class="mt-1 text-sm text-ink/60">{{ __('admin.dashboard.limited_text', ['role' => auth('admin')->user()->role?->name ?? '—']) }}</p>
        </section>
    @else
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <a href="{{ route('admin.clients.index') }}" class="card px-4 py-3 hover:border-black/15">
            <p class="text-[11px] font-bold text-ink/65">{{ __('admin.stats.total') }}</p>
            <p class="font-mono text-2xl font-bold mt-0.5">{{ $totalClients }}</p>
        </a>
        <a href="{{ route('admin.clients.index', ['status' => 'pending']) }}" @class(['card px-4 py-3', 'border-brand/50!' => $statusCounts->get('pending')])>
            <p class="text-[11px] font-bold text-ink/65">{{ __('admin.stats.pending') }}</p>
            <p class="font-mono text-2xl font-bold mt-0.5 text-brand-deep">{{ $statusCounts->get('pending', 0) }}</p>
        </a>
        <a href="{{ route('admin.clients.index', ['status' => 'active']) }}" class="card px-4 py-3">
            <p class="text-[11px] font-bold text-ink/65">{{ __('admin.stats.active') }}</p>
            <p class="font-mono text-2xl font-bold mt-0.5 text-emerald">{{ $statusCounts->get('active', 0) }}</p>
        </a>
        <a href="{{ route('admin.clients.index', ['status' => 'suspended']) }}" class="card px-4 py-3">
            <p class="text-[11px] font-bold text-ink/65">{{ __('admin.stats.suspended') }}</p>
            <p class="font-mono text-2xl font-bold mt-0.5 text-rose">{{ $statusCounts->get('suspended', 0) }}</p>
        </a>
        <div class="card px-4 py-3 col-span-2 lg:col-span-1">
            <p class="text-[11px] font-bold text-ink/65">{{ __('admin.stats.expiring', ['days' => \App\Http\Controllers\Admin\DashboardController::EXPIRY_WARNING_DAYS]) }}</p>
            <p class="font-mono text-2xl font-bold mt-0.5"><span class="text-amber-deep">{{ $expiringSoon }}</span><span class="text-ink/30"> / </span><span class="text-rose">{{ $expired }}</span></p>
        </div>
    </div>

    @if ($statusCounts->get('pending'))
        <div class="rounded-xl bg-brand-soft border border-brand/30 px-4 py-3 flex flex-wrap items-center justify-between gap-2">
            <p class="text-[13px] font-bold"><i class="fa-solid fa-bell text-brand-deep"></i> {{ trans_choice('admin.dashboard.pending_banner', $statusCounts->get('pending'), ['count' => $statusCounts->get('pending')]) }}</p>
            <a href="{{ route('admin.clients.index', ['status' => 'pending']) }}" class="btn-dark">{{ __('admin.dashboard.review_requests') }}</a>
        </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-5">
        <section class="card lg:col-span-2">
            <header class="px-5 py-4 border-b border-black/5 flex items-center justify-between">
                <h2 class="font-extrabold">{{ __('admin.dashboard.latest_requests') }}</h2>
                <a href="{{ route('admin.clients.index', ['status' => 'pending']) }}" class="text-xs font-bold text-teal-deep hover:underline">{{ __('common.view_all') }}</a>
            </header>
            <ul class="divide-y divide-black/5">
                @forelse ($pendingClients as $client)
                    <li>
                        <a href="{{ route('admin.clients.show', $client) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-canvas/60">
                            <span class="w-9 h-9 rounded-xl bg-brand-soft text-brand-deep flex items-center justify-center"><i class="fa-solid {{ $client->category?->icon ?? 'fa-building' }}"></i></span>
                            <span class="min-w-0 flex-1">
                                <span class="block font-extrabold truncate">{{ $client->company }}</span>
                                <span class="block text-[11.5px] text-ink/60"><span class="font-mono">{{ $client->code }}</span> · {{ $client->category?->name }} · {{ $client->registered_at->diffForHumans() }}</span>
                            </span>
                            @if ($client->documents_count)
                                <span class="text-[11px] font-bold text-teal-deep"><i class="fa-solid fa-paperclip"></i> {{ $client->documents_count }}</span>
                            @endif
                            <i class="fa-solid fa-chevron-left ltr:rotate-180 text-ink/30 text-xs"></i>
                        </a>
                    </li>
                @empty
                    <li class="px-5 py-10 text-center text-sm text-ink/55"><i class="fa-solid fa-inbox text-2xl text-ink/25 block mb-2"></i>{{ __('admin.dashboard.no_pending') }}</li>
                @endforelse
            </ul>
        </section>

        <section class="card">
            <header class="px-5 py-4 border-b border-black/5">
                <h2 class="font-extrabold">{{ __('admin.dashboard.plans') }}</h2>
            </header>
            <ul class="divide-y divide-black/5">
                @foreach ($plans as $plan)
                    <li class="px-5 py-4">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-extrabold">{{ $plan->name }}</p>
                            <span class="font-mono text-[11px] text-ink/50">{{ $plan->code }}</span>
                        </div>
                        <p class="text-[12px] text-ink/65 mt-1">
                            <span class="font-mono font-bold text-ink">{{ number_format((float) $plan->price_monthly) }}</span> {{ __('common.currency') }} / {{ __('common.cycles.monthly') }}
                            · <span class="font-mono font-bold text-ink">{{ number_format((float) $plan->price_annual) }}</span> {{ __('common.currency') }} / {{ __('common.cycles.annual') }}
                        </p>
                        <p class="text-[12px] text-ink/65 mt-1"><i class="fa-solid fa-building-circle-check text-emerald"></i> {{ trans_choice('admin.dashboard.active_subscriptions', $plan->active_subscriptions_count, ['count' => $plan->active_subscriptions_count]) }}</p>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
    @endcannot
</x-layouts.admin>
