@php
    $user = $portal->user();
    $client = $user->client;
@endphp

<aside :class="sidebarOpen && 'max-lg:translate-x-0!'"
       class="fixed lg:sticky top-0 inset-y-0 start-0 z-40 h-screen w-64 shrink-0 bg-ink text-white flex flex-col transition-transform duration-300 max-lg:ltr:-translate-x-full max-lg:rtl:translate-x-full">
    <div class="px-5 py-5 border-b border-white/10">
        <div class="flex items-center gap-2.5">
            <x-brand-mark class="w-8 h-8" />
            <span class="wordmark text-lg text-brand-cream" dir="ltr">SAFORA</span>
            <button type="button" @click="sidebarOpen = false" class="lg:hidden ms-auto text-white/60 hover:text-white" aria-label="{{ __('common.close') }}"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <p class="mt-3 text-[13px] font-extrabold leading-snug">{{ $client->company }}</p>
        <p class="text-[11px] text-ink-mist mt-0.5">{{ $client->category?->name }}</p>

        <form method="POST" action="{{ route('portal.branch.update') }}" class="mt-3" x-data>
            @csrf
            @method('PUT')
            <label for="portal-branch" class="text-[10.5px] font-bold text-white/50"><i class="fa-solid fa-store text-[9px]"></i> {{ __('portal.branches.label') }}</label>
            <select id="portal-branch" name="branch" @change="$el.form.submit()"
                    @disabled($portal->branches()->count() < 2 && ! $portal->canSelectAllBranches())
                    class="w-full mt-2 rounded-lg bg-white/10 border border-white/15 px-2.5 py-2 text-[12.5px] font-bold text-white outline-none focus:border-brand disabled:opacity-70">
                @if ($portal->canSelectAllBranches())
                    <option value="{{ \App\Services\PortalSession::ALL_BRANCHES }}" class="text-ink" @selected($portal->isAllBranches())>{{ __('portal.branches.all_compare') }}</option>
                @endif
                @foreach ($portal->branches() as $branch)
                    <option value="{{ $branch->id }}" class="text-ink" @selected($portal->currentBranch()?->is($branch))>{{ $branch->name }}</option>
                @endforeach
            </select>
            <noscript><button type="submit" class="mt-2 btn-ghost w-full">{{ __('common.apply') }}</button></noscript>
        </form>

        <p class="mt-2 text-[10.5px] text-white/50"><i class="fa-solid fa-user text-[9px]"></i> {{ $user->name }} · {{ $user->roleLabel() }}</p>
    </div>

    <nav class="flex-1 overflow-y-auto overscroll-contain scrollbar-dark p-3 space-y-1" aria-label="{{ __('portal.nav.label') }}">
        @foreach ($top as $item)
            <x-portal-nav-item :item="$item" />
        @endforeach

        @foreach ($groups as $group)
            <div class="pt-2" x-data="{ open: true }">
                <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-extrabold text-white/90 hover:bg-white/[0.05]">
                    <i class="fa-solid {{ $group['icon'] }} w-5 text-center text-white/55"></i>
                    <span class="flex flex-col items-start leading-tight"><span>{{ $group['label'] }}</span><span class="text-[10px] font-semibold text-white/40" dir="ltr">{{ $group['sub'] }}</span></span>
                    <i class="fa-solid fa-chevron-down ms-auto text-[10px] text-white/50 transition-transform" :class="open && 'rotate-180'"></i>
                </button>
                <div x-show="open" class="mt-0.5 ms-4 ps-1 border-s border-white/10 space-y-0.5">
                    @foreach ($group['items'] as $item)
                        <x-portal-nav-item :item="$item" small />
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="pt-2 mt-2 border-t border-white/10 space-y-1">
            @foreach ($bottom as $item)
                <x-portal-nav-item :item="$item" />
            @endforeach
        </div>
    </nav>

    <div class="p-4 border-t border-white/10 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-[12px] font-bold">{{ mb_substr($user->name, 0, 1) }}</span>
        <span class="text-[12px] text-white/75 truncate flex-1">{{ $user->name }}</span>
        <form method="POST" action="{{ route('portal.logout') }}">
            @csrf
            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-white/10 hover:bg-white/15 text-[11px] font-bold">
                <i class="fa-solid fa-arrow-right-from-bracket rtl:rotate-180"></i> {{ __('common.sign_out') }}
            </button>
        </form>
    </div>
</aside>
