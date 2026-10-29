<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterClientRequest;
use App\Models\SectorCategory;
use App\Services\ClientRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function create(): View
    {
        return view('portal.auth.register', [
            'categories' => SectorCategory::query()->active()->orderBy('code')->get(),
        ]);
    }

    public function store(RegisterClientRequest $request, ClientRegistrationService $registration): RedirectResponse
    {
        $client = $registration->register(
            $request->companyData(),
            $request->string('password')->value(),
            $request->documents(),
        );

        return redirect()
            ->route('portal.login')
            ->with('status', __('portal.register.submitted', ['code' => $client->code]));
    }
}
