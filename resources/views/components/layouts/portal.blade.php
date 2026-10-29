@props(['title'])

{{-- Client portal shell (prototype: 11_ClientCenter.html#portal). Requires the portal.access middleware. --}}
<x-layouts.base :title="$title" x-data="{ sidebarOpen: false }">
    @if ($portal->isImpersonated())
        <div class="bg-amber-soft border-b border-amber/30 px-5 py-2 flex flex-wrap items-center gap-2 text-[12.5px] font-bold text-amber-deep">
            <i class="fa-solid fa-user-secret"></i> {{ __('portal.impersonation.banner') }}
            <form method="POST" action="{{ route('portal.logout') }}" class="ms-auto">
                @csrf
                <button type="submit" class="px-3 py-1 rounded-lg bg-ink text-white text-[11.5px]">{{ __('portal.impersonation.leave') }}</button>
            </form>
        </div>
    @endif

    <div class="min-h-screen flex">
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-ink/50 lg:hidden"></div>

        <x-portal-sidebar :portal="$portal" />

        <div class="flex-1 min-w-0 flex flex-col">
            <header class="sticky top-0 z-20 flex items-center gap-3 bg-white/90 backdrop-blur border-b border-black/5 px-5 lg:px-7 py-3">
                <button type="button" @click="sidebarOpen = true" class="lg:hidden w-9 h-9 rounded-lg border border-black/10 text-ink/70" aria-label="{{ __('common.menu') }}">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <p class="text-sm font-bold truncate">
                    <i class="fa-solid fa-store text-ink/40 text-xs"></i>
                    {{ $portal->currentBranch()?->name ?? __('portal.branches.all') }}
                </p>
                <div class="ms-auto"><x-locale-switcher /></div>
            </header>

            <main class="flex-1 p-5 lg:p-7 space-y-5">
                <x-flash />
                <x-credentials-card />
                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.base>
