<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ProgramStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HaccpProgramRequest;
use App\Models\HaccpProgram;
use App\Models\HaccpProgramGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * HACCP command center — PRP programs (Sprint 3, prototype 0_HACCPCenter.html): the program strip
 * grouped by the 7 groups, and the program content always open for editing in both languages.
 */
class HaccpProgramController extends Controller
{
    public function index(Request $request): View
    {
        $groups = HaccpProgramGroup::query()->with('programs')->orderBy('sort_order')->get();
        $programs = $groups->flatMap->programs;
        $creating = $request->boolean('new');

        $selected = $creating ? null : ($programs->firstWhere('code', $request->string('program')->value()) ?? $programs->first());

        return view('admin.haccp.programs', [
            'groups' => $groups,
            'program' => $creating || $selected === null
                ? new HaccpProgram(['code' => HaccpProgram::nextCode(), 'status' => ProgramStatus::Draft, 'icon' => 'fa-layer-group', 'group_id' => $groups->first()?->id])
                : $selected,
            'stats' => [
                'total' => $programs->count(),
                'active' => $programs->where('status', ProgramStatus::Active)->count(),
                'custom' => $programs->where('is_custom', true)->count(),
                'untranslated' => $programs->filter(fn (HaccpProgram $program): bool => $program->missingTranslations() !== [])->count(),
            ],
        ]);
    }

    public function store(HaccpProgramRequest $request): RedirectResponse
    {
        $program = HaccpProgram::create([
            ...$request->programData(),
            'code' => HaccpProgram::nextCode(),
            'is_custom' => true,
            'sort_order' => (int) HaccpProgram::max('sort_order') + 1,
        ]);

        return redirect()->route('admin.haccp.programs.index', ['program' => $program->code])
            ->with('status', __('haccp.flash.created', ['code' => $program->code]));
    }

    public function update(HaccpProgramRequest $request, HaccpProgram $program): RedirectResponse
    {
        $program->update($request->programData());

        return redirect()->route('admin.haccp.programs.index', ['program' => $program->code])
            ->with('status', __('haccp.flash.saved', ['code' => $program->code]));
    }

    /**
     * Only programs added by the admin may be deleted; the 22 library programs can be set to draft instead.
     */
    public function destroy(HaccpProgram $program): RedirectResponse
    {
        if (! $program->is_custom) {
            return back()->with('error', __('haccp.flash.library_locked'));
        }

        $program->delete();

        return redirect()->route('admin.haccp.programs.index')->with('status', __('haccp.flash.deleted', ['code' => $program->code]));
    }
}
