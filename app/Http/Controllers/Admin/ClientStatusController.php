<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Exceptions\InvalidClientStatusTransition;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveClientRequest;
use App\Http\Requests\Admin\ClientStatusReasonRequest;
use App\Models\Client;
use App\Services\ClientApprovalService;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Approve, reject, suspend and reactivate a client from its admin profile.
 */
class ClientStatusController extends Controller
{
    public function __construct(private readonly ClientApprovalService $approvals) {}

    public function approve(ApproveClientRequest $request, Client $client): RedirectResponse
    {
        return $this->attempt($client, 'approved', fn () => $this->approvals->approve(
            $client,
            $request->plan(),
            $request->cycle(),
            $request->startDate(),
            $request->discount(),
            $request->user('admin'),
        ));
    }

    public function reject(ClientStatusReasonRequest $request, Client $client): RedirectResponse
    {
        return $this->attempt($client, 'rejected', fn () => $this->approvals->reject($client, $request->reason(), $request->user('admin')));
    }

    public function suspend(ClientStatusReasonRequest $request, Client $client): RedirectResponse
    {
        return $this->attempt($client, 'suspended', fn () => $this->approvals->suspend($client, $request->reason(), $request->user('admin')));
    }

    public function reactivate(Request $request, Client $client): RedirectResponse
    {
        return $this->attempt($client, 'reactivated', fn () => $this->approvals->reactivate($client, $request->user('admin')));
    }

    private function attempt(Client $client, string $outcome, Closure $action): RedirectResponse
    {
        try {
            $action();
        } catch (InvalidClientStatusTransition) {
            return back()->with('error', __('admin.clients.flash.invalid_transition'));
        }

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('status', __('admin.clients.flash.'.$outcome, ['company' => $client->company]));
    }
}
