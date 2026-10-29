<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchRequest;
use App\Models\ClientBranch;
use App\Services\ClientAccountService;
use App\Services\PortalSession;
use App\Services\PricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Branches managed by the company from the portal (02_CLIENT_PORTAL.md "حسابي"), showing the
 * extra-branch cost. Requires branches.manage; {companyBranch} resolves within the company only.
 */
class CompanyBranchController extends Controller
{
    public function __construct(
        private readonly PortalSession $portal,
        private readonly ClientAccountService $accounts,
        private readonly PricingService $pricing,
    ) {}

    public function index(): View
    {
        $client = $this->portal->user()->client;
        $subscription = $client->subscription;
        $activeCount = $client->branches()->active()->count();

        return view('portal.branches.index', [
            'branches' => $client->branches()->withCount('users')->orderBy('code')->get(),
            'subscription' => $subscription,
            'activeCount' => $activeCount,
            'quote' => $subscription ? $this->pricing->quoteSubscription($subscription, $activeCount) : null,
            'branchCost' => $subscription ? $this->pricing->additionalBranchCost($subscription, $activeCount) : null,
        ]);
    }

    public function create(): View
    {
        return $this->form(new ClientBranch(['active' => true]));
    }

    public function store(BranchRequest $request): RedirectResponse
    {
        $branch = $this->accounts->createBranch($this->portal->user()->client, $request->branchData(), $this->portal->user());

        return redirect()->route('portal.branches.index')->with('status', __('accounts.flash.branch_created', ['name' => $branch->name]));
    }

    public function edit(ClientBranch $companyBranch): View
    {
        return $this->form($companyBranch);
    }

    public function update(BranchRequest $request, ClientBranch $companyBranch): RedirectResponse
    {
        $this->accounts->updateBranch($companyBranch, $request->branchData(), $this->portal->user());

        return redirect()->route('portal.branches.index')->with('status', __('accounts.flash.branch_updated', ['name' => $companyBranch->name]));
    }

    private function form(ClientBranch $branch): View
    {
        $client = $this->portal->user()->client;
        $subscription = $client->subscription;

        return view('portal.branches.form', [
            'branch' => $branch,
            'subscription' => $subscription,
            'branchCost' => $subscription ? $this->pricing->additionalBranchCost($subscription, $client->branches()->active()->count()) : null,
        ]);
    }
}
