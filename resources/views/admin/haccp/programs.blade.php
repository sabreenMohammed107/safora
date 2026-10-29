@php
    use App\Enums\ProgramStatus;
    use App\Models\HaccpProgram;

    $editing = $program->exists;
    $longFields = ['acceptance', 'limit_text', 'steps', 'evidence', 'failure', 'haccp_link', 'checks'];
    $missing = $editing ? $program->missingTranslations() : [];
@endphp

<x-layouts.admin :title="__('haccp.programs.title')" eyebrow="HACCP COMMAND CENTER · PRPs" :heading="__('haccp.programs.heading')" :intro="__('haccp.programs.intro')">
    <x-slot:breadcrumbs>
        <span class="font-bold text-ink">{{ __('admin.nav.haccp_center') }}</span>
        <span class="text-ink/30">/</span>
        <span>{{ __('admin.nav.prp') }}</span>
    </x-slot:breadcrumbs>

    <x-slot:actions>
        <a href="{{ route('admin.haccp.programs.index', ['new' => 1]) }}" class="btn-primary"><i class="fa-solid fa-plus"></i> {{ __('haccp.programs.new') }}</a>
    </x-slot:actions>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        @foreach (['total' => 'text-ink', 'active' => 'text-emerald', 'custom' => 'text-teal-deep', 'untranslated' => 'text-amber-deep'] as $key => $color)
            <div class="card px-4 py-3">
                <p class="text-[11px] font-bold text-ink/65">{{ __('haccp.stats.'.$key) }}</p>
                <p class="font-mono text-2xl font-bold mt-0.5 {{ $color }}">{{ $stats[$key] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid xl:grid-cols-[320px_minmax(0,1fr)] gap-5 items-start">
        {{-- ───── Program strip, grouped ───── --}}
        <aside class="card xl:sticky xl:top-20 xl:max-h-[calc(100vh-6rem)] flex flex-col" x-data="{ q: '' }">
            <label class="m-3 flex items-center gap-2 rounded-lg bg-canvas border border-black/5 px-3 py-2">
                <i class="fa-solid fa-magnifying-glass text-ink/50 text-xs"></i>
                <input x-model="q" placeholder="{{ __('haccp.programs.search') }}" class="bg-transparent outline-none text-sm w-full placeholder:text-ink/40">
            </label>
            <nav class="flex-1 overflow-y-auto px-3 pb-3 space-y-3" aria-label="{{ __('haccp.programs.heading') }}">
                @foreach ($groups as $group)
                    <div>
                        <p class="px-1 pb-1 text-[11px] font-extrabold text-ink/50"><i class="fa-solid {{ $group->icon }} text-[10px]"></i> {{ $group->label }}</p>
                        @foreach ($group->programs as $item)
                            @php($haystack = mb_strtolower($item->code.' '.$item->getTranslation('name', 'ar').' '.$item->getTranslation('name', 'en', false)))
                            <a href="{{ route('admin.haccp.programs.index', ['program' => $item->code]) }}"
                               x-show="!q || @js($haystack).includes(q.toLowerCase())"
                               @if ($editing && $item->is($program)) aria-current="page" @endif
                               @class(['flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-[13px] transition', 'bg-ink text-white' => $editing && $item->is($program), 'hover:bg-canvas' => ! ($editing && $item->is($program))])>
                                <i class="fa-solid {{ $item->icon }} w-4 text-center text-xs opacity-70"></i>
                                <span class="font-mono text-[10.5px] opacity-60 shrink-0">{{ $item->code }}</span>
                                <span class="font-bold truncate flex-1">{{ $item->name }}</span>
                                @if ($item->status === ProgramStatus::Draft)
                                    <span class="w-2 h-2 rounded-full bg-amber" title="{{ ProgramStatus::Draft->label() }}"></span>
                                @endif
                                @if ($item->missingTranslations() !== [])
                                    <span class="text-[9px] font-extrabold opacity-60" title="{{ __('haccp.programs.missing_en') }}">EN</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>
        </aside>

        {{-- ───── Program content (always open) ───── --}}
        <div class="space-y-4" x-data="{ dirty: false, lang: 'ar' }"
             x-init="window.addEventListener('beforeunload', e => { if (dirty) { e.preventDefault(); e.returnValue = ''; } })">
            <form method="POST" action="{{ $editing ? route('admin.haccp.programs.update', $program) : route('admin.haccp.programs.store') }}"
                  class="card" @input="dirty = true" @change="dirty = true" @submit="dirty = false">
                @csrf
                @if ($editing) @method('PUT') @endif

                <header class="px-5 py-4 border-b border-black/5 flex flex-wrap items-center gap-3">
                    <span class="w-11 h-11 rounded-xl bg-brand-soft text-brand-deep flex items-center justify-center text-lg"><i class="fa-solid {{ $program->icon }}"></i></span>
                    <div class="min-w-0 flex-1">
                        <p class="font-mono text-[11px] text-ink/50">{{ $program->code }} @if (! $editing) · {{ __('haccp.programs.new') }} @endif</p>
                        <h2 class="text-lg font-extrabold truncate">{{ $editing ? $program->name : __('haccp.programs.new') }}</h2>
                    </div>
                    @if ($editing)
                        <span @class(['pill', 'bg-emerald-soft text-emerald-deep' => $program->status === ProgramStatus::Active, 'bg-amber-soft text-amber-deep' => $program->status === ProgramStatus::Draft])>{{ $program->status->label() }}</span>
                        <span class="pill bg-canvas text-ink/60">{{ $program->is_custom ? __('haccp.programs.custom') : __('haccp.programs.library') }}</span>
                    @endif
                </header>

                <div class="p-5 grid sm:grid-cols-3 gap-4 border-b border-black/5">
                    <label class="block">
                        <span class="lbl">{{ __('haccp.fields.group') }} *</span>
                        <select name="group_id" class="fld" required>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}" @selected(old('group_id', $program->group_id) == $group->id)>{{ $group->label }}</option>
                            @endforeach
                        </select>
                        @error('group_id')<p class="fld-error">{{ $message }}</p>@enderror
                    </label>
                    <label class="block">
                        <span class="lbl">{{ __('haccp.fields.icon') }} *</span>
                        <input name="icon" value="{{ old('icon', $program->icon) }}" required dir="ltr" class="fld font-mono" placeholder="fa-layer-group">
                        @error('icon')<p class="fld-error">{{ $message }}</p>@enderror
                    </label>
                    <label class="block">
                        <span class="lbl">{{ __('haccp.fields.status') }} *</span>
                        <select name="status" class="fld">
                            @foreach (ProgramStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $program->status?->value) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-[11px] text-ink/50">{{ __('haccp.programs.status_hint') }}</p>
                    </label>
                </div>

                <div class="px-5 pt-4 flex flex-wrap items-center gap-3">
                    <div class="inline-flex p-1 rounded-xl bg-canvas" role="tablist" aria-label="{{ __('common.language') }}">
                        @foreach (config('safora.locales') as $code => $locale)
                            <button type="button" role="tab" @click="lang = @js($code)" :aria-selected="(lang === @js($code)).toString()"
                                    class="px-4 py-1.5 rounded-lg text-[12px] font-bold" :class="lang === @js($code) ? 'bg-white shadow-sm text-ink' : 'text-ink/60'">
                                {{ $locale['name'] }}
                                @if ($code === 'en' && $missing !== [])
                                    <span class="ms-1 pill bg-amber-soft text-amber-deep">{{ count($missing) }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                @foreach (config('safora.locales') as $code => $locale)
                    <div x-show="lang === @js($code)" @if ($code !== 'ar') x-cloak @endif dir="{{ $locale['dir'] }}" lang="{{ $code }}" class="p-5 grid sm:grid-cols-2 gap-4">
                        @foreach (HaccpProgram::CONTENT_FIELDS as $field)
                            @php($value = old($field.'.'.$code, $program->getTranslation($field, $code, false)))
                            <label @class(['block', 'sm:col-span-2' => in_array($field, $longFields, true)])>
                                <span class="lbl">
                                    {{ __('haccp.fields.'.$field) }} @if ($field === 'name' && $code === 'ar') * @endif
                                    @if ($code === 'en' && in_array($field, $missing, true))
                                        <span class="text-amber-deep" title="{{ __('haccp.programs.missing_en') }}"><i class="fa-solid fa-circle-exclamation text-[10px]"></i></span>
                                    @endif
                                </span>
                                @if (in_array($field, $longFields, true))
                                    <textarea name="{{ $field }}[{{ $code }}]" rows="{{ $field === 'steps' ? 7 : 3 }}" class="fld leading-relaxed"
                                              @if ($code === 'en') placeholder="{{ $program->getTranslation($field, 'ar', false) ? __('haccp.programs.translate_placeholder') : '' }}" @endif>{{ $value }}</textarea>
                                @else
                                    <input name="{{ $field }}[{{ $code }}]" value="{{ $value }}" class="fld" @if ($field === 'name' && $code === 'ar') required @endif>
                                @endif
                                @if ($field === 'checks')<p class="mt-1 text-[11px] text-ink/50">{{ __('haccp.programs.checks_hint') }}</p>@endif
                                @error($field.'.'.$code)<p class="fld-error">{{ $message }}</p>@enderror
                            </label>
                        @endforeach
                    </div>
                @endforeach

                <div class="sticky bottom-0 z-10 flex flex-wrap items-center gap-3 px-5 py-3 border-t border-black/5 bg-white/95 backdrop-blur rounded-b-2xl">
                    <p x-show="dirty" x-cloak class="text-[12px] font-bold text-amber-deep"><i class="fa-solid fa-circle-dot text-[9px]"></i> {{ __('haccp.programs.unsaved') }}</p>
                    <div class="ms-auto flex gap-2">
                        <a href="{{ route('admin.haccp.programs.index', $editing ? ['program' => $program->code] : []) }}" class="btn-ghost" x-show="dirty" x-cloak @click="dirty = false">{{ __('haccp.programs.discard') }}</a>
                        <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> {{ $editing ? __('common.save') : __('haccp.programs.create') }}</button>
                    </div>
                </div>
            </form>

            @if ($editing && $program->is_custom)
                <form method="POST" action="{{ route('admin.haccp.programs.destroy', $program) }}" class="flex justify-end"
                      onsubmit="return confirm(@js(__('common.confirm_delete')))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-ghost text-rose-deep!"><i class="fa-regular fa-trash-can"></i> {{ __('haccp.programs.delete') }}</button>
                </form>
            @endif
        </div>
    </div>
</x-layouts.admin>
