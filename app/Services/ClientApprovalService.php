<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BillingCycle;
use App\Enums\ClientStatus;
use App\Exceptions\InvalidClientStatusTransition;
use App\Models\Client;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Admin review of client accounts: approve (with a subscription), reject, suspend and reactivate.
 * Each change locks the client row, checks the status transition and writes an activity log entry.
 */
class ClientApprovalService
{
    public function approve(
        Client $client,
        Plan $plan,
        BillingCycle $cycle,
        ?CarbonInterface $startDate = null,
        float $discountPercent = 0,
        ?Model $actor = null,
    ): Subscription {
        if (! $plan->active) {
            throw new InvalidArgumentException("Plan [{$plan->code}] is not active.");
        }

        if ($discountPercent < 0 || $discountPercent > 100) {
            throw new InvalidArgumentException('Discount must be between 0 and 100 percent.');
        }

        return DB::transaction(function () use ($client, $plan, $cycle, $startDate, $discountPercent, $actor): Subscription {
            $this->lockAndGuard($client, ClientStatus::Active);

            $start = CarbonImmutable::instance($startDate ?? now())->startOfDay();

            $subscription = $client->subscriptions()->create([
                'plan_id' => $plan->getKey(),
                'cycle' => $cycle,
                'discount' => $discountPercent,
                'start_date' => $start,
                'end_date' => $cycle->endDateFrom($start),
            ]);

            $client->update([
                'status' => ClientStatus::Active,
                'status_reason' => null,
                'approved_at' => now(),
            ]);

            $client->logActivity('approved', ['plan' => $plan->code, 'cycle' => $cycle->value], $actor);

            return $subscription;
        });
    }

    public function reject(Client $client, string $reason, ?Model $actor = null): void
    {
        $this->changeStatus($client, ClientStatus::Rejected, 'rejected', $reason, $actor);
    }

    public function suspend(Client $client, string $reason, ?Model $actor = null): void
    {
        $this->changeStatus($client, ClientStatus::Suspended, 'suspended', $reason, $actor);
    }

    public function reactivate(Client $client, ?Model $actor = null): void
    {
        $this->changeStatus($client, ClientStatus::Active, 'reactivated', null, $actor);
    }

    private function changeStatus(Client $client, ClientStatus $to, string $event, ?string $reason, ?Model $actor): void
    {
        DB::transaction(function () use ($client, $to, $event, $reason, $actor): void {
            $this->lockAndGuard($client, $to);

            $client->update(['status' => $to, 'status_reason' => $reason]);

            $client->logActivity($event, array_filter(['reason' => $reason]), $actor);
        });
    }

    /**
     * Re-reads the client under a row lock so two admins cannot act on the same stale status.
     */
    private function lockAndGuard(Client $client, ClientStatus $to): void
    {
        Client::query()->whereKey($client->getKey())->lockForUpdate()->first();
        $client->refresh();

        if (! $client->status->canTransitionTo($to)) {
            throw InvalidClientStatusTransition::make($client, $to);
        }
    }
}
