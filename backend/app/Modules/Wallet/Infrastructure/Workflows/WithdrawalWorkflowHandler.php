<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Infrastructure\Workflows;

use App\Modules\Wallet\Domain\Entities\Withdrawal;
use App\Modules\Wallet\Domain\Enums\WithdrawalStatus;
use App\Modules\Wallet\Domain\Services\WalletService;
use App\Platform\Workflows\Domain\Contracts\WorkflowHandler;
use App\Platform\Workflows\Domain\ValueObjects\WorkflowContext;
use App\Platform\Workflows\Domain\ValueObjects\WorkflowResult;
use Illuminate\Support\Str;

class WithdrawalWorkflowHandler implements WorkflowHandler
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    public function entityClass(): string
    {
        return Withdrawal::class;
    }

    public function workflowKey(): string
    {
        return 'wallet.withdrawal_approval';
    }

    public function availableTransitions(object $entity): array
    {
        return ['approve', 'reject', 'request_changes'];
    }

    public function transition(
        object $entity,
        string $transition,
        WorkflowContext $context
    ): WorkflowResult {
        $withdrawal = $entity;
        $previousState = $withdrawal->status->value ?? $withdrawal->status;

        if ($transition === 'approve') {
            $payoutRef = $context->metadata['payout_reference'] ?? ('PAYOUT-' . Str::upper(Str::random(10)));
            $this->walletService->completeWithdrawal($withdrawal, $payoutRef);
            $newState = WithdrawalStatus::Completed->value;
        } elseif ($transition === 'reject') {
            $reason = $context->comments ?? 'Rejected through workflow process.';
            $this->walletService->rejectWithdrawal($withdrawal, $reason);
            $newState = WithdrawalStatus::Rejected->value;
        } elseif ($transition === 'request_changes') {
            $newState = WithdrawalStatus::Requested->value; // Stay in requested status
        } else {
            throw new \InvalidArgumentException("Invalid workflow transition: {$transition}");
        }

        return WorkflowResult::create(
            success: true,
            previousState: $previousState,
            newState: $newState,
            metadata: ['withdrawal_id' => $withdrawal->id, 'amount_cents' => $withdrawal->amount_cents]
        );
    }

    public function compensate(
        object $entity,
        \App\Platform\Workflows\Domain\Entities\WorkflowHistory $history,
        WorkflowContext $context
    ): WorkflowResult {
        $withdrawal = $entity;
        $previousState = $withdrawal->status->value ?? $withdrawal->status;

        // If completed, reject it to refund the amount back to the ledger
        if ($previousState === WithdrawalStatus::Completed->value) {
            $withdrawal->status = WithdrawalStatus::Requested; // Temp revert requested status to allow reject
            $withdrawal->save();
            $this->walletService->rejectWithdrawal($withdrawal, 'Saga rollback compensation executed.');
        }

        return WorkflowResult::create(
            success: true,
            previousState: $previousState,
            newState: WithdrawalStatus::Rejected->value
        );
    }
}
