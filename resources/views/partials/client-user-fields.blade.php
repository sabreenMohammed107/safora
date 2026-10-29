{{--
    Portal account fields shared by the portal team page and the admin client profile.
    Needs: $member, $branches, $roles, $canAppointOwner, $passwordHint (shown under the password field).
--}}
@php($editing = $member->exists)

<label class="block">
    <span class="lbl">{{ __('common.fields.name') }} *</span>
    <input name="name" value="{{ old('name', $member->name) }}" required class="fld">
    @error('name')<p class="fld-error">{{ $message }}</p>@enderror
</label>
<label class="block">
    <span class="lbl">{{ __('common.fields.email') }} *</span>
    <input name="email" type="email" value="{{ old('email', $member->email) }}" required dir="ltr" class="fld font-mono">
    @error('email')<p class="fld-error">{{ $message }}</p>@enderror
</label>
<label class="block">
    <span class="lbl">{{ __('common.fields.password') }}</span>
    <input name="password" type="password" dir="ltr" autocomplete="new-password" class="fld font-mono">
    <p class="mt-1 text-[11px] text-ink/50">{{ $passwordHint }}</p>
    @error('password')<p class="fld-error">{{ $message }}</p>@enderror
</label>
<label class="block">
    <span class="lbl">{{ __('common.fields.password_confirmation') }}</span>
    <input name="password_confirmation" type="password" dir="ltr" autocomplete="new-password" class="fld font-mono">
</label>

<fieldset class="sm:col-span-2 rounded-xl border border-black/10 p-4 grid sm:grid-cols-2 gap-4"
          x-data="{ role: @js(old('role', $member->role?->value)) }">
    <legend class="px-1 text-[12px] font-extrabold text-ink/70">{{ __('portal.team.access') }}</legend>

    <div class="sm:col-span-2 grid sm:grid-cols-2 gap-2" role="radiogroup">
        @foreach (\App\Enums\ClientUserRole::cases() as $type)
            @php($disabled = $type === \App\Enums\ClientUserRole::Owner && ! $canAppointOwner && ! $member->isOwner())
            <label @class(['flex items-start gap-3 rounded-lg border px-3 py-2.5', 'border-black/10 cursor-pointer' => ! $disabled, 'border-black/5 opacity-55 cursor-not-allowed' => $disabled])
                   :class="role === @js($type->value) && 'border-teal! bg-teal-soft/40'">
                <input type="radio" name="role" value="{{ $type->value }}" x-model="role" class="mt-1 accent-teal" @disabled($disabled)>
                <span>
                    <span class="block text-[13px] font-extrabold">{{ $type->label() }}</span>
                    <span class="block text-[11.5px] text-ink/60">{{ __('portal.team.type_hint.'.$type->value) }}</span>
                </span>
            </label>
        @endforeach
        @error('role')<p class="fld-error sm:col-span-2">{{ $message }}</p>@enderror
    </div>

    <label class="block" x-show="role === 'member'">
        <span class="lbl">{{ __('portal.branches.label') }} *</span>
        <select name="branch_id" class="fld" :disabled="role !== 'member'">
            <option value="">{{ __('common.choose') }}</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected(old('branch_id', $member->branch_id) == $branch->id)>{{ $branch->name }}{{ $branch->active ? '' : ' ('.__('common.inactive').')' }}</option>
            @endforeach
        </select>
        @error('branch_id')<p class="fld-error">{{ $message }}</p>@enderror
    </label>
    <label class="block" x-show="role === 'member'">
        <span class="lbl">{{ __('portal.team.columns.role') }} *</span>
        <select name="client_role_id" class="fld" :disabled="role !== 'member'">
            <option value="">{{ __('common.choose') }}</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected(old('client_role_id', $member->client_role_id) == $role->id)>{{ $role->displayName() }}</option>
            @endforeach
        </select>
        @error('client_role_id')<p class="fld-error">{{ $message }}</p>@enderror
    </label>
</fieldset>

<label class="flex items-center gap-2 text-[13px] font-bold cursor-pointer sm:col-span-2">
    <input type="hidden" name="active" value="0">
    <input type="checkbox" name="active" value="1" class="w-4 h-4 accent-emerald" @checked(old('active', $member->active))>
    {{ __('common.account_active') }}
</label>
@error('active')<p class="fld-error sm:col-span-2">{{ $message }}</p>@enderror
