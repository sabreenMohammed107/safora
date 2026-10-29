<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchRequest;
use App\Models\Client;
use App\Models\ClientBranch;
use App\Services\ClientAccountService;
use App\Services\PricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Branches managed from the admin client profile (01_ADMIN.md §E). Branches are deactivated, never deleted.
 */
class ClientBranchController extends Controller
{
    public function __construct(
        private readonly ClientAccountService $accounts,
        private readonly PricingService $pricing,
    ) {}

    public function create(Client $client): View
    {
        return $this->form($client, new ClientBranch(['active' => true]));
    }

    public function store(BranchRequest $request, Client $client): RedirectResponse
    {
        $branch = $this->accounts->createBranch($client, $request->branchData(), $request->user('admin'));

        return redirect()->route('admin.clients.show', $client)
            ->with('status', __('accounts.flash.branch_created', ['name' => $branch->name]));
    }

    public function edit(Client $client, ClientBranch $branch): View
    {
        return $this->form($client, $branch);
    }

    public function update(BranchRequest $request, Client $client, ClientBranch $branch): RedirectResponse
    {
        $this->accounts->updateBranch($branch, $request->branchData(), $request->user('admin'));

        return redirect()->route('admin.clients.show', $client)
            ->with('status', __('accounts.flash.branch_updated', ['name' => $branch->name]));
    }

    private function form(Client $client, ClientBranch $branch): View
    {
        $subscription = $client->subscription;

        return view('admin.clients.branch-form', [
            'client' => $client,
            'branch' => $branch,
            'subscription' => $subscription,
            'branchCost' => $subscription ? $this->pricing->additionalBranchCost($subscription, $client->branches()->active()->count()) : null,
        ]);
    }
}
