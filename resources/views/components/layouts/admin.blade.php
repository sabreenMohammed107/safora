@props(['title', 'eyebrow' => null, 'heading' => null, 'intro' => null])

@php
    $soon = '<span class="ms-auto rounded bg-white/10 px-1.5 py-0.5 text-[9.5px] font-bold text-white/45">'.e(__('common.soon')).'</span>';
@endphp

{{-- Admin shell (prototype: 0_HACCPCenter.html sidebar, 01_ADMIN.md menu). --}}
<x-layouts.base :title="$title" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-ink/50 lg:hidden"></div>

        <aside :class="sidebarOpen && 'max-lg:translate-x-0!'"
               class="fixed lg:sticky top-0 inset-y-0 start-0 z-40 h-screen w-72 shrink-0 bg-ink text-white flex flex-col transition-transform duration-300 max-lg:ltr:-translate-x-full max-lg:rtl:translate-x-full">
            <div class="flex items-center gap-3 px-6 py-6 border-b border-ink-border">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 min-w-0" aria-label="SAFORA">
                    <x-brand-mark class="w-11 h-11" />
                    <div dir="ltr" class="text-left leading-none">
                        <p class="wordmark text-[23px] text-brand-cream">SAFORA<sup class="text-[9px] font-bold ms-0.5 align-super">TM</sup></p>
                        <p class="text-[9.5px] font-semibold tracking-[.08em] text-ink-mist mt-1.5">by ELITE INTEGRATED BUSINESS</p>
                    </div>
                </a>
                <button type="button" @click="sidebarOpen = false" class="lg:hidden ms-auto text-white/60 hover:text-white" aria-label="{{ __('common.close') }}">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto overscroll-contain scrollbar-dark" aria-label="{{ __('admin.nav.label') }}">
                <a href="{{ route('admin.dashboard') }}" class="nav-link" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                    <i class="fa-solid fa-gauge-high w-5 text-brand"></i> {{ __('admin.nav.dashboard') }}
                </a>

                <div class="pt-3 mt-2 border-t border-ink-border">
                    <span class="nav-link" aria-disabled="true"><i class="fa-solid fa-shield-heart w-5"></i> {{ __('admin.nav.haccp_center') }} {!! $soon !!}</span>
                    <div class="ps-8 me-3 mt-1 space-y-0.5 border-s border-white/10 text-[13px]">
                        @foreach (['prp' => 'fa-layer-group', 'haccp_forms' => 'fa-table-list', 'haccp_plans' => 'fa-file-lines', 'traceability' => 'fa-route', 'critical_limits' => 'fa-temperature-half', 'control_points' => 'fa-crosshairs'] as $key => $icon)
                            <span class="flex items-center gap-2 px-3 py-1.5 text-white/35 cursor-not-allowed"><i class="fa-solid {{ $icon }} w-4 text-xs"></i> {{ __('admin.nav.'.$key) }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="pt-3 mt-2 border-t border-ink-border">
                    <span class="nav-link" aria-disabled="true"><i class="fa-solid fa-award w-5"></i> {{ __('admin.nav.iso_center') }} {!! $soon !!}</span>
                    <span class="nav-link" aria-disabled="true"><i class="fa-solid fa-folder-tree w-5"></i> {{ __('admin.nav.full_files') }} {!! $soon !!}</span>
                </div>

                <div class="pt-3 mt-2 border-t border-ink-border">
                    <p class="px-3 pb-1 text-[10.5px] font-bold text-white/40">{{ __('admin.nav.client_center') }}</p>
                    @can('clients.view')
                        <a href="{{ route('admin.clients.index') }}" class="nav-link" @if (request()->routeIs('admin.clients.*')) aria-current="page" @endif>
                            <i class="fa-solid fa-building-user w-5 text-brand"></i> {{ __('admin.nav.clients') }}
                        </a>
                    @endcan
                    <span class="nav-link" aria-disabled="true"><i class="fa-solid fa-tags w-5"></i> {{ __('admin.nav.plans') }} {!! $soon !!}</span>
                    <span class="nav-link" aria-disabled="true"><i class="fa-solid fa-sitemap w-5"></i> {{ __('admin.nav.categories') }} {!! $soon !!}</span>
                </div>

                @canany(['staff.manage', 'roles.manage'])
                    <div class="pt-3 mt-2 border-t border-ink-border">
                        <p class="px-3 pb-1 text-[10.5px] font-bold text-white/40">{{ __('admin.nav.administration') }}</p>
                        <a href="{{ route('admin.staff.index') }}" class="nav-link" @if (request()->routeIs('admin.staff.*', 'admin.roles.*')) aria-current="page" @endif>
                            <i class="fa-solid fa-user-shield w-5 text-brand"></i> {{ __('admin.nav.staff') }}
                        </a>
                    </div>
                @endcanany
            </nav>

            <div class="p-4 border-t border-ink-border flex items-center gap-3">
                <span class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center text-sm font-bold">{{ mb_substr(auth('admin')->user()->name, 0, 1) }}</span>
                <div class="min-w-0 flex-1">
                    <p class="text-[13px] font-bold truncate">{{ auth('admin')->user()->name }}</p>
                    <p class="text-[11px] text-ink-mist truncate">{{ auth('admin')->user()->title ?? __('admin.role') }}</p>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/15 text-white/80" title="{{ __('common.sign_out') }}" aria-label="{{ __('common.sign_out') }}">
                        <i class="fa-solid fa-arrow-right-from-bracket rtl:rotate-180"></i>
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 min-w-0 flex flex-col">
            <header class="sticky top-0 z-20 flex items-center gap-3 bg-white/90 backdrop-blur border-b border-black/5 px-5 lg:px-7 py-3">
                <button type="button" @click="sidebarOpen = true" class="lg:hidden w-9 h-9 rounded-lg border border-black/10 text-ink/70" aria-label="{{ __('common.menu') }}">
                    <i class="fa-solid fa-bars"></i>
                </button>
                @isset($breadcrumbs)
                    <nav class="flex items-center gap-2 text-sm text-ink/60 min-w-0" aria-label="{{ __('common.breadcrumbs') }}">{{ $breadcrumbs }}</nav>
                @endisset
                <div class="ms-auto flex items-center gap-2">
                    <x-locale-switcher />
                </div>
            </header>

            <main class="flex-1 p-5 lg:p-7 space-y-5">
                @if ($heading)
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            @if ($eyebrow)
                                <p class="font-mono text-[11px] text-teal-deep font-semibold tracking-widest">{{ $eyebrow }}</p>
                            @endif
                            <h1 class="text-2xl lg:text-[26px] font-extrabold tracking-tight mt-1">{{ $heading }}</h1>
                            @if ($intro)
                                <p class="text-sm text-ink/70 mt-1 max-w-3xl">{{ $intro }}</p>
                            @endif
                        </div>
                        {{ $actions ?? '' }}
                    </div>
                @endif

                <x-flash />

                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.base>
