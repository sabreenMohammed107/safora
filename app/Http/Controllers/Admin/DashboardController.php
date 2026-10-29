<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ClientStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public const int EXPIRY_WARNING_DAYS = 30;

    public function __invoke(): View
    {
        $statusCounts = Client::query()
            ->toBase()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $activeWithSubscription = fn (): Builder => Client::query()->withStatus(ClientStatus::Active);

        return view('admin.dashboard', [
            'statusCounts' => $statusCounts,
            'totalClients' => $statusCounts->sum(),
            'expiringSoon' => $activeWithSubscription()
                ->whereHas('subscription', fn (Builder $query) => $query
                    ->whereBetween('end_date', [today(), today()->addDays(self::EXPIRY_WARNING_DAYS)]))
                ->count(),
            'expired' => $activeWithSubscription()
                ->whereHas('subscription', fn (Builder $query) => $query->where('end_date', '<', today()))
                ->count(),
            'pendingClients' => Client::query()
                ->withStatus(ClientStatus::Pending)
                ->with('category')
                ->withCount('documents')
                ->latest('registered_at')
                ->limit(5)
                ->get(),
            'plans' => Plan::query()
                ->withCount(['subscriptions as active_subscriptions_count' => fn (Builder $query) => $query
                    ->where('end_date', '>=', today())
                    ->whereHas('client', fn (Builder $client) => $client->withStatus(ClientStatus::Active))])
                ->orderBy('price_monthly')
                ->get(),
        ]);
    }
}
