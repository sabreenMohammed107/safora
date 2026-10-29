{{-- One-time display of a temporary password with a ready-to-copy message (01_ADMIN.md §E). The password is flashed, never stored. --}}
@if ($credentials = session('credentials'))
    @php
        $message = __('accounts.credentials.message', [
            'name' => $credentials['name'],
            'company' => $credentials['company'] ?? 'SAFORA',
            'url' => $credentials['login_url'],
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ]);
    @endphp
    <section {{ $attributes->merge(['class' => 'rounded-2xl border border-teal/30 bg-teal-soft/60 p-4']) }}
             x-data="{ copied: false, copy() { navigator.clipboard.writeText(this.$refs.message.innerText).then(() => { this.copied = true; setTimeout(() => this.copied = false, 2000) }) } }">
        <div class="flex flex-wrap items-center gap-2">
            <p class="text-[13px] font-extrabold text-teal-deep"><i class="fa-solid fa-key"></i> {{ __('accounts.credentials.title') }}</p>
            <span class="text-[11.5px] font-bold text-amber-deep"><i class="fa-solid fa-eye-slash text-[10px]"></i> {{ __('accounts.credentials.shown_once') }}</span>
            <button type="button" @click="copy()" class="ms-auto btn-dark">
                <i class="fa-regular" :class="copied ? 'fa-circle-check' : 'fa-copy'"></i>
                <span x-text="copied ? @js(__('accounts.credentials.copied')) : @js(__('accounts.credentials.copy'))">{{ __('accounts.credentials.copy') }}</span>
            </button>
        </div>
        <pre x-ref="message" class="mt-3 whitespace-pre-wrap rounded-xl bg-white border border-black/5 p-3 text-[13px] leading-relaxed font-sans">{{ $message }}</pre>
    </section>
@endif
