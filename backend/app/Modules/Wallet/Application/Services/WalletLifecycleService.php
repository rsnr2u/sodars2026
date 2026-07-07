<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Application\Services;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Wallet\Domain\Entities\Wallet;
use App\Modules\Wallet\Domain\Entities\WalletActivity;
use App\Modules\Wallet\Domain\Enums\WalletState;
use App\Modules\Wallet\Domain\Events\WalletCreated;
use App\Modules\Wallet\Domain\Events\WalletActivated;
use App\Modules\Wallet\Domain\Events\WalletSuspended;
use App\Modules\Wallet\Domain\Events\WalletDeposited;
use App\Modules\Wallet\Domain\Events\WalletTransferred;
use App\Modules\Wallet\Domain\Events\WalletAdjusted;
use App\Modules\Wallet\Domain\Events\WalletCredited;
use App\Modules\Wallet\Domain\Events\WalletDebited;
use App\Modules\Wallet\Domain\Events\SettlementCalculated;
use App\Modules\Wallet\Domain\Events\SettlementApproved;
use App\Modules\Wallet\Domain\Events\SettlementCredited;
use App\Modules\Wallet\Domain\Events\SettlementReversed;
use App\Modules\Wallet\Domain\Events\WithdrawalRequested;
use App\Modules\Wallet\Domain\Events\WithdrawalUnderReview;
use App\Modules\Wallet\Domain\Events\WithdrawalApproved;
use App\Modules\Wallet\Domain\Events\WithdrawalProcessing;
use App\Modules\Wallet\Domain\Events\WithdrawalCompleted;
use App\Modules\Wallet\Domain\Events\WithdrawalRejected;
use App\Modules\Wallet\Domain\Events\WithdrawalCancelled;
use App\Modules\Wallet\Domain\Events\WithdrawalFailed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;

class WalletLifecycleService
{
    public function __construct(
        protected OutboxService $outboxService
    ) {}

    public function recordCreation(Wallet $wallet, array $metadata = []): void
    {
        $eventData = $wallet->toArray();

        $this->outboxService->record(
            aggregateType: 'Wallet',
            aggregateId: $wallet->id,
            eventName: 'wallet.created.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new WalletCreated(
            aggregateId: $wallet->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : (\App\Platform\Identity\Application\Services\IdentityContext::userId() ? (string) IdentityContext::userId() : null),
            metadata: $metadata
        ));

        $this->logActivity($wallet, 'Created', "Wallet created with number {$wallet->wallet_number}");
    }

    public function transitionTo(Wallet $wallet, string $toState, array $metadata = []): void
    {
        $fromState = $wallet->status->value ?? $wallet->status;
        $allowed = WalletState::allowedTransitions();

        if (!isset($allowed[$fromState]) || !in_array($toState, $allowed[$fromState], true)) {
            throw ValidationException::withMessages([
                'status' => ["Status transition from {$fromState} to {$toState} is not allowed."],
            ]);
        }

        $wallet->status = WalletState::from($toState);
        $wallet->save();

        $eventData = [
            'wallet_id' => $wallet->id,
            'from_status' => $fromState,
            'to_status' => $toState,
        ];

        if ($toState === WalletState::Active->value) {
            $this->outboxService->record(
                aggregateType: 'Wallet',
                aggregateId: $wallet->id,
                eventName: 'wallet.activated.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );
            Event::dispatch(new WalletActivated(
                aggregateId: $wallet->id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId(),
                traceId: TraceContext::traceId(),
                actorId: Auth::id() ? (string) Auth::id() : (\App\Platform\Identity\Application\Services\IdentityContext::userId() ? (string) IdentityContext::userId() : null),
                metadata: $metadata
            ));
        } elseif ($toState === WalletState::Suspended->value) {
            $this->outboxService->record(
                aggregateType: 'Wallet',
                aggregateId: $wallet->id,
                eventName: 'wallet.suspended.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );
            Event::dispatch(new WalletSuspended(
                aggregateId: $wallet->id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId(),
                traceId: TraceContext::traceId(),
                actorId: Auth::id() ? (string) Auth::id() : (\App\Platform\Identity\Application\Services\IdentityContext::userId() ? (string) IdentityContext::userId() : null),
                metadata: $metadata
            ));
        }

        $this->logActivity($wallet, 'StatusTransitioned', "Wallet status transitioned from {$fromState} to {$toState}.");
    }

    public function recordDeposit(Wallet $wallet, array $eventData): void
    {
        $this->outboxService->record(
            aggregateType: 'Wallet',
            aggregateId: $wallet->id,
            eventName: 'wallet.deposited.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new WalletDeposited(
            aggregateId: $wallet->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : (\App\Platform\Identity\Application\Services\IdentityContext::userId() ? (string) IdentityContext::userId() : null),
            metadata: []
        ));

        $this->logActivity($wallet, 'Deposit', "Deposit of " . ($eventData['amount_cents'] ?? 0) . " cents completed.");
    }

    public function recordTransfer(Wallet $wallet, array $eventData): void
    {
        $this->outboxService->record(
            aggregateType: 'Wallet',
            aggregateId: $wallet->id,
            eventName: 'wallet.transferred.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new WalletTransferred(
            aggregateId: $wallet->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : (\App\Platform\Identity\Application\Services\IdentityContext::userId() ? (string) IdentityContext::userId() : null),
            metadata: []
        ));

        $this->logActivity($wallet, 'Transfer', "Funds transfer of " . ($eventData['amount_cents'] ?? 0) . " cents registered.");
    }

    public function recordSettlementCalculated(Wallet $wallet, array $eventData): void
    {
        Event::dispatch(new SettlementCalculated(
            aggregateId: $wallet->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : (\App\Platform\Identity\Application\Services\IdentityContext::userId() ? (string) IdentityContext::userId() : null),
            metadata: []
        ));
    }

    public function recordSettlementApproved(Wallet $wallet, array $eventData): void
    {
        Event::dispatch(new SettlementApproved(
            aggregateId: $wallet->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : (\App\Platform\Identity\Application\Services\IdentityContext::userId() ? (string) IdentityContext::userId() : null),
            metadata: []
        ));
    }

    public function recordSettlementCredited(Wallet $wallet, array $eventData): void
    {
        $this->outboxService->record(
            aggregateType: 'Wallet',
            aggregateId: $wallet->id,
            eventName: 'wallet.settlement_credited.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new SettlementCredited(
            aggregateId: $wallet->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : (\App\Platform\Identity\Application\Services\IdentityContext::userId() ? (string) IdentityContext::userId() : null),
            metadata: []
        ));

        $this->logActivity($wallet, 'SettlementCredited', "Settlement credited to wallet.");
    }

    public function recordSettlementReversed(Wallet $wallet, array $eventData): void
    {
        Event::dispatch(new SettlementReversed(
            aggregateId: $wallet->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : (\App\Platform\Identity\Application\Services\IdentityContext::userId() ? (string) IdentityContext::userId() : null),
            metadata: []
        ));

        $this->logActivity($wallet, 'SettlementReversed', "Settlement payout reversal processed.");
    }

    public function recordWithdrawalRequested(Wallet $wallet, array $eventData): void
    {
        $this->outboxService->record(
            aggregateType: 'Wallet',
            aggregateId: $wallet->id,
            eventName: 'wallet.withdrawal_requested.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new WithdrawalRequested(
            aggregateId: $wallet->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : (\App\Platform\Identity\Application\Services\IdentityContext::userId() ? (string) IdentityContext::userId() : null),
            metadata: []
        ));

        $this->logActivity($wallet, 'WithdrawalRequested', "Withdrawal request submitted.");
    }

    public function recordWithdrawalUnderReview(Wallet $wallet, array $eventData): void
    {
        Event::dispatch(new WithdrawalUnderReview(
            aggregateId: $wallet->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : (\App\Platform\Identity\Application\Services\IdentityContext::userId() ? (string) IdentityContext::userId() : null),
            metadata: []
        ));
    }

    public function recordWithdrawalApproved(Wallet $wallet, array $eventData): void
    {
        Event::dispatch(new WithdrawalApproved(
            aggregateId: $wallet->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : (\App\Platform\Identity\Application\Services\IdentityContext::userId() ? (string) IdentityContext::userId() : null),
            metadata: []
        ));
    }

    public function recordWithdrawalProcessing(Wallet $wallet, array $eventData): void
    {
        Event::dispatch(new WithdrawalProcessing(
            aggregateId: $wallet->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : (\App\Platform\Identity\Application\Services\IdentityContext::userId() ? (string) IdentityContext::userId() : null),
            metadata: []
        ));
    }

    public function recordWithdrawalCompleted(Wallet $wallet, array $eventData): void
    {
        $this->outboxService->record(
            aggregateType: 'Wallet',
            aggregateId: $wallet->id,
            eventName: 'wallet.withdrawal_completed.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        Event::dispatch(new WithdrawalCompleted(
            aggregateId: $wallet->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : (\App\Platform\Identity\Application\Services\IdentityContext::userId() ? (string) IdentityContext::userId() : null),
            metadata: []
        ));

        $this->logActivity($wallet, 'WithdrawalCompleted', "Withdrawal completed successfully.");
    }

    public function recordWithdrawalRejected(Wallet $wallet, array $eventData): void
    {
        Event::dispatch(new WithdrawalRejected(
            aggregateId: $wallet->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : (\App\Platform\Identity\Application\Services\IdentityContext::userId() ? (string) IdentityContext::userId() : null),
            metadata: []
        ));

        $this->logActivity($wallet, 'WithdrawalRejected', "Withdrawal request rejected.");
    }

    public function recordWithdrawalCancelled(Wallet $wallet, array $eventData): void
    {
        Event::dispatch(new WithdrawalCancelled(
            aggregateId: $wallet->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : (\App\Platform\Identity\Application\Services\IdentityContext::userId() ? (string) IdentityContext::userId() : null),
            metadata: []
        ));

        $this->logActivity($wallet, 'WithdrawalCancelled', "Withdrawal request cancelled.");
    }

    public function recordWithdrawalFailed(Wallet $wallet, array $eventData): void
    {
        Event::dispatch(new WithdrawalFailed(
            aggregateId: $wallet->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId(),
            traceId: TraceContext::traceId(),
            actorId: Auth::id() ? (string) Auth::id() : (\App\Platform\Identity\Application\Services\IdentityContext::userId() ? (string) IdentityContext::userId() : null),
            metadata: []
        ));

        $this->logActivity($wallet, 'WithdrawalFailed', "Withdrawal payout failed.");
    }

    protected function logActivity(Wallet $wallet, string $action, string $description): void
    {
        WalletActivity::create([
            'organization_id' => $wallet->organization_id,
            'wallet_id' => $wallet->id,
            'performed_by' => Auth::id() ? (string) Auth::id() : (\App\Platform\Identity\Application\Services\IdentityContext::userId() ? (string) IdentityContext::userId() : $wallet->holder_id),
            'action' => $action,
            'description' => $description,
            'trace_id' => TraceContext::traceId(),
        ]);
    }
}
