{{-- Branch form fields shared by the admin client profile and the portal. Needs $branch, $subscription, $branchCost. --}}
<label class="block sm:col-span-2">
    <span class="lbl">{{ __('accounts.branch.fields.name') }} *</span>
    <input name="name" value="{{ old('name', $branch->name) }}" required maxlength="120" class="fld">
    @error('name')<p class="fld-error">{{ $message }}</p>@enderror
</label>
<label class="block">
    <span class="lbl">{{ __('accounts.branch.fields.city') }}</span>
    <input name="city" value="{{ old('city', $branch->city) }}" maxlength="80" class="fld">
    @error('city')<p class="fld-error">{{ $message }}</p>@enderror
</label>
<label class="block">
    <span class="lbl">{{ __('accounts.branch.fields.manager') }}</span>
    <input name="manager" value="{{ old('manager', $branch->manager) }}" maxlength="120" class="fld">
    @error('manager')<p class="fld-error">{{ $message }}</p>@enderror
</label>
<label class="block">
    <span class="lbl">{{ __('common.fields.phone') }}</span>
    <input name="phone" value="{{ old('phone', $branch->phone) }}" maxlength="32" dir="ltr" class="fld font-mono">
    @error('phone')<p class="fld-error">{{ $message }}</p>@enderror
</label>
<label class="block">
    <span class="lbl">{{ __('accounts.branch.fields.address') }}</span>
    <input name="address" value="{{ old('address', $branch->address) }}" maxlength="255" class="fld">
    @error('address')<p class="fld-error">{{ $message }}</p>@enderror
</label>

<div class="sm:col-span-2 rounded-xl border border-black/10 p-4 space-y-2">
    <label class="flex items-center gap-2 text-[13px] font-bold cursor-pointer">
        <input type="hidden" name="active" value="0">
        <input type="checkbox" name="active" value="1" class="w-4 h-4 accent-emerald" @checked(old('active', $branch->active))>
        {{ __('accounts.branch.active') }}
    </label>
    <p class="text-[12px] text-ink/60">{{ __('accounts.branch.deactivate_hint') }}</p>
    @error('active')<p class="fld-error">{{ $message }}</p>@enderror

    @if ($subscription && $branchCost !== null && (! $branch->exists || ! $branch->active))
        <p @class(['text-[12px] font-bold', 'text-amber-deep' => $branchCost > 0, 'text-emerald-deep' => $branchCost === 0])>
            <i class="fa-solid fa-circle-info"></i>
            {{ $branchCost > 0
                ? __('accounts.branch.cost_note', ['amount' => \App\Services\PriceQuote::toDecimal($branchCost), 'cycle' => __('common.cycles.'.$subscription->cycle->value)])
                : __('accounts.branch.included_note') }}
        </p>
    @endif
</div>
