<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\BillingCycle;
use App\Enums\ClientStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Plan;
use App\Models\SectorCategory;
use App\Services\PricingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function __construct(private readonly PricingService $pricing) {}

    public function index(Request $request): View
    {
        $status = ClientStatus::tryFrom($request->string('status')->value());
        $search = $request->string('q')->trim()->value();
        $categoryId = $request->integer('category') ?: null;

        $clients = Client::query()
            ->with(['category', 'subscription.plan'])
            ->withCount(['branches as active_branches_count' => fn (Builder $query) => $query->where('active', true)])
            ->withCount('documents')
            ->when($status, fn (Builder $query) => $query->withStatus($status))
            ->when($search !== '', fn (Builder $query) => $query->search($search))
            ->when($categoryId, fn (Builder $query) => $query->where('sector_category_id', $categoryId))
            ->orderByRaw('case status when ? then 0 when ? then 1 when ? then 2 else 3 end', [
                ClientStatus::Pending->value, ClientStatus::Active->value, ClientStatus::Suspended->value,
            ])
            ->latest('registered_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.clients.index', [
            'clients' => $clients,
            'status' => $status,
            'search' => $search,
            'categoryId' => $categoryId,
            'categories' => SectorCategory::query()->orderBy('code')->get(),
            'statusCounts' => Client::query()->toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'pricing' => $this->pricing,
        ]);
    }

    public function show(Client $client): View
    {
        $client->load([
            'category.sectors',
            'extraSectors',
            'subscription.plan',
            'branches' => fn ($query) => $query->withCount('users')->orderBy('code'),
            'users' => fn ($query) => $query->with(['branch', 'clientRole'])->orderBy('role')->orderBy('name'),
            'documents',
            'activityLogs' => fn ($query) => $query->limit(30),
        ]);

        $activeBranches = $client->branches->where('active', true)->count();

        return view('admin.clients.show', [
            'client' => $client,
            'activeBranches' => $activeBranches,
            'quote' => $client->subscription ? $this->pricing->quoteSubscription($client->subscription, $activeBranches) : null,
            'plans' => Plan::query()->active()->orderBy('price_monthly')->get(),
            'cycles' => BillingCycle::cases(),
        ]);
    }
}
