<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\PortalSession;
use App\Services\PricingService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(PortalSession $portal, PricingService $pricing): View
    {
        $client = $portal->user()->client->load(['subscription.plan.modules', 'subscription.plan.includedPlan.modules', 'category']);
        $subscription = $client->subscription;

        return view('portal.home', [
            'client' => $client,
            'subscription' => $subscription,
            'plan' => $subscription->plan,
            'daysLeft' => (int) today()->diffInDays($subscription->end_date, absolute: false),
            'branches' => $portal->branches()->loadCount('users'),
        ]);
    }
}
