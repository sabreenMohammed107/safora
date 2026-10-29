<x-layouts.base :title="$block->title()">
    <div class="min-h-screen flex flex-col">
        <header class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-2">
                <x-brand-mark class="w-8 h-8" />
                <span dir="ltr" class="wordmark text-lg">SAFORA</span>
            </div>
            <x-locale-switcher />
        </header>

        <main class="flex-1 flex items-center justify-center px-6 py-10">
            <div class="card w-full max-w-lg p-8 text-center">
                <span class="mx-auto w-16 h-16 rounded-2xl bg-canvas flex items-center justify-center">
                    <i class="fa-solid {{ $block->icon() }} text-2xl"></i>
                </span>
                <p class="mt-4 text-[12px] font-bold text-ink/55">{{ $client->company }} · <span class="font-mono">{{ $client->code }}</span></p>
                <h1 class="mt-1 text-xl font-extrabold">{{ $block->title() }}</h1>
                <p class="mt-2 text-[14px] text-ink/70 leading-relaxed">{{ $block->message() }}</p>

                @if (in_array($block, [\App\Enums\PortalBlock::Rejected, \App\Enums\PortalBlock::Suspended], true) && $client->status_reason)
                    <p class="mt-4 rounded-xl bg-rose-soft px-4 py-3 text-[13px] font-bold text-rose-deep">{{ __('portal.blocked.reason') }}: {{ $client->status_reason }}</p>
                @endif

                <form method="POST" action="{{ route('portal.logout') }}" class="mt-6">
                    @csrf
                    <button type="submit" class="btn-dark">
                        <i class="fa-solid fa-arrow-right-from-bracket rtl:rotate-180"></i>
                        {{ $impersonated ? __('portal.impersonation.leave') : __('common.sign_out') }}
                    </button>
                </form>
            </div>
        </main>
    </div>
</x-layouts.base>
