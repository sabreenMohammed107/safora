<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Admin;
use App\Models\ClientBranch;
use App\Models\ClientUser;
use Illuminate\Contracts\Session\Session;
use Illuminate\Database\Eloquent\Collection;
use LogicException;

/**
 * Per-request portal state: the signed-in user, the branches they may open and the selected one.
 * The selection lives in the session; an owner may pick "all branches" (view and compare only).
 * Populated by the EnsurePortalAccess middleware.
 */
class PortalSession
{
    public const string BRANCH_KEY = 'portal.branch';

    public const string IMPERSONATOR_KEY = 'portal.impersonator_id';

    public const string ALL_BRANCHES = 'all';

    private ?ClientUser $user = null;

    /** @var Collection<int, ClientBranch> */
    private Collection $branches;

    private ?ClientBranch $currentBranch = null;

    public function __construct(private readonly Session $session)
    {
        $this->branches = new Collection;
    }

    public function start(ClientUser $user): void
    {
        $this->user = $user;
        $this->branches = $user->accessibleBranches()->get();

        $selection = $this->session->get(self::BRANCH_KEY);

        if (! $this->isValidSelection($selection)) {
            $selection = $this->defaultSelection();
            $this->session->put(self::BRANCH_KEY, $selection);
        }

        $this->currentBranch = $selection === self::ALL_BRANCHES ? null : $this->branches->find($selection);
    }

    public function select(string|int $selection): bool
    {
        if (! $this->isValidSelection($selection)) {
            return false;
        }

        $this->session->put(self::BRANCH_KEY, $selection === self::ALL_BRANCHES ? $selection : (int) $selection);

        return true;
    }

    public function user(): ClientUser
    {
        return $this->user ?? throw new LogicException('Portal session has not been started.');
    }

    /**
     * @return Collection<int, ClientBranch>
     */
    public function branches(): Collection
    {
        return $this->branches;
    }

    /**
     * The selected branch, or null in the owner's "all branches" view.
     */
    public function currentBranch(): ?ClientBranch
    {
        return $this->currentBranch;
    }

    public function isAllBranches(): bool
    {
        return $this->user !== null && $this->currentBranch === null;
    }

    public function canSelectAllBranches(): bool
    {
        return $this->user()->isOwner();
    }

    public function isImpersonated(): bool
    {
        return $this->session->has(self::IMPERSONATOR_KEY);
    }

    public function impersonator(): ?Admin
    {
        return Admin::find($this->session->get(self::IMPERSONATOR_KEY));
    }

    public static function forget(Session $session): void
    {
        $session->forget([self::BRANCH_KEY, self::IMPERSONATOR_KEY]);
    }

    private function isValidSelection(mixed $selection): bool
    {
        if ($selection === self::ALL_BRANCHES) {
            return $this->canSelectAllBranches();
        }

        return is_numeric($selection) && $this->branches->contains('id', (int) $selection);
    }

    private function defaultSelection(): string|int
    {
        return $this->canSelectAllBranches() && $this->branches->count() > 1
            ? self::ALL_BRANCHES
            : $this->branches->first()->id;
    }
}
