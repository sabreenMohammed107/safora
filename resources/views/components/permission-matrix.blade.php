@props([
    'groups',
    'selected' => [],
    'groupLabels',
    'name' => 'permissions',
])

{{-- $groups: [groupKey => [['value', 'label', 'disabled', 'hint'], ...]] — shared by staff and client role forms. --}}
<div class="grid md:grid-cols-2 gap-4">
    @foreach ($groups as $groupKey => $permissions)
        @php($enabledValues = collect($permissions)->reject(fn (array $permission): bool => $permission['disabled'])->pluck('value')->values())
        <fieldset class="rounded-xl border border-black/10 bg-white" x-data>
            <legend class="sr-only">{{ $groupLabels[$groupKey] }}</legend>
            <div class="flex items-center justify-between gap-2 px-4 py-3 border-b border-black/5 bg-canvas/60 rounded-t-xl">
                <p class="text-[13px] font-extrabold">{{ $groupLabels[$groupKey] }}</p>
                @if ($enabledValues->isNotEmpty())
                    <button type="button" class="text-[11.5px] font-bold text-teal-deep hover:underline"
                            @click="const boxes = [...$root.querySelectorAll('input[type=checkbox]:not(:disabled)')]; const all = boxes.every(b => b.checked); boxes.forEach(b => b.checked = !all)">
                        {{ __('common.toggle_all') }}
                    </button>
                @endif
            </div>
            <div class="p-2">
                @foreach ($permissions as $permission)
                    <label @class([
                        'flex items-start gap-3 rounded-lg px-2.5 py-2',
                        'cursor-pointer hover:bg-canvas/70' => ! $permission['disabled'],
                        'opacity-55 cursor-not-allowed' => $permission['disabled'],
                    ])>
                        <input type="checkbox" name="{{ $name }}[]" value="{{ $permission['value'] }}"
                               class="mt-0.5 w-4 h-4 accent-teal"
                               @checked(in_array($permission['value'], $selected, true))
                               @disabled($permission['disabled'])>
                        <span class="min-w-0">
                            <span class="block text-[13px] font-bold">{{ $permission['label'] }}</span>
                            <span class="block font-mono text-[10.5px] text-ink/45" dir="ltr">{{ $permission['value'] }}</span>
                            @if ($permission['hint'])
                                <span class="block text-[11px] font-bold text-amber-deep mt-0.5"><i class="fa-solid fa-lock text-[9px]"></i> {{ $permission['hint'] }}</span>
                            @elseif ($permission['disabled'])
                                <span class="block text-[11px] text-ink/50 mt-0.5"><i class="fa-solid fa-lock text-[9px]"></i> {{ __('common.not_grantable') }}</span>
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endforeach
</div>
@error($name)<p class="fld-error">{{ $message }}</p>@enderror
@error($name.'.*')<p class="fld-error">{{ $message }}</p>@enderror
