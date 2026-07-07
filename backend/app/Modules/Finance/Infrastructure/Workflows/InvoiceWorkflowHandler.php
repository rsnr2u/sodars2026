<?php

declare(strict_types=1);

namespace App\Modules\Finance\Infrastructure\Workflows;

use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Application\Services\InvoiceLifecycleService;
use App\Platform\Workflows\Domain\Contracts\WorkflowHandler;
use App\Platform\Workflows\Domain\ValueObjects\WorkflowContext;
use App\Platform\Workflows\Domain\ValueObjects\WorkflowResult;

class InvoiceWorkflowHandler implements WorkflowHandler
{
    public function __construct(
        protected InvoiceLifecycleService $lifecycleService
    ) {}

    public function entityClass(): string
    {
        return Invoice::class;
    }

    public function workflowKey(): string
    {
        return 'invoice.approval';
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
        $invoice = $entity;
        $previousState = $invoice->status;

        $targetStatus = match ($transition) {
            'approve' => 'posted',
            'reject' => 'voided',
            'request_changes' => 'draft',
            default => throw new \InvalidArgumentException("Invalid workflow transition: {$transition}"),
        };

        $invoice->update(['status' => $targetStatus]);

        if ($targetStatus === 'posted') {
            $this->lifecycleService->recordIssue($invoice);
        } elseif ($targetStatus === 'voided') {
            $this->lifecycleService->recordVoid($invoice);
        }

        return WorkflowResult::create(
            success: true,
            previousState: $previousState,
            newState: $targetStatus,
            metadata: ['invoice_number' => $invoice->invoice_number, 'grand_total' => $invoice->grand_total_cents]
        );
    }

    public function compensate(
        object $entity,
        \App\Platform\Workflows\Domain\Entities\WorkflowHistory $history,
        WorkflowContext $context
    ): WorkflowResult {
        $invoice = $entity;
        $previousState = $invoice->status;
        $targetStatus = $history->from_state ?? 'draft';

        $invoice->update(['status' => $targetStatus]);

        if ($targetStatus === 'voided') {
            $this->lifecycleService->recordVoid($invoice);
        }

        return WorkflowResult::create(
            success: true,
            previousState: $previousState,
            newState: $targetStatus
        );
    }
}
