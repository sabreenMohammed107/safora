@if (session('status'))
    <div {{ $attributes->merge(['class' => 'rounded-xl bg-emerald-soft px-4 py-3 text-[13px] font-bold text-emerald-deep flex gap-2']) }} role="status">
        <i class="fa-solid fa-circle-check mt-0.5"></i><span>{{ session('status') }}</span>
    </div>
@endif

@if (session('error'))
    <div {{ $attributes->merge(['class' => 'rounded-xl bg-rose-soft px-4 py-3 text-[13px] font-bold text-rose-deep flex gap-2']) }} role="alert">
        <i class="fa-solid fa-circle-exclamation mt-0.5"></i><span>{{ session('error') }}</span>
    </div>
@endif
