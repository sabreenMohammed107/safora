<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Sector;
use App\Services\ClientSectorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Grant / revoke extra food sectors from the admin client profile.
 */
class ClientSectorController extends Controller
{
    public function __construct(private readonly ClientSectorService $sectors) {}

    public function store(Request $request, Client $client): RedirectResponse
    {
        $request->validate([
            'sector_id' => ['required', Rule::exists('sectors', 'id')->where('active', true)],
        ]);

        $sector = Sector::findOrFail($request->integer('sector_id'));
        $this->sectors->grant($client, $sector, $request->user('admin'));

        return back()->with('status', __('admin.clients.sectors.flash.granted', ['name' => $sector->name]));
    }

    public function destroy(Request $request, Client $client, Sector $sector): RedirectResponse
    {
        abort_unless($client->extraSectors()->whereKey($sector->getKey())->exists(), 404);

        $removed = $this->sectors->revoke($client, $sector, $request->user('admin'));

        return back()->with('status', trans_choice('admin.clients.sectors.flash.revoked', $removed, ['name' => $sector->name, 'count' => $removed]));
    }
}
