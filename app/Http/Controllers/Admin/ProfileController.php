<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetLocale;
use App\Http\Requests\UpdateOwnPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The signed-in staff member's own profile (name, title, language) and password (01_ADMIN.md top menu).
 */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile.edit', ['admin' => $request->user('admin')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'locale' => ['required', Rule::in(array_keys(config('safora.locales')))],
        ]);

        $request->user('admin')->update($data);
        $request->session()->put(SetLocale::SESSION_KEY, $data['locale']);

        return back()->with('status', __('accounts.flash.profile_saved'));
    }

    public function updatePassword(UpdateOwnPasswordRequest $request): RedirectResponse
    {
        $request->user('admin')->assignOwnPassword($request->newPassword())->save();

        return redirect()->route('admin.dashboard')->with('status', __('accounts.flash.password_changed'));
    }
}
