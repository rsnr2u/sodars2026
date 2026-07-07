<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Infrastructure\Workflows;

use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Enums\BookingStatus;
use App\Modules\Bookings\Domain\Services\BookingLifecycleService;
use App\Platform\Workflows\Domain\Contracts\WorkflowHandler;
use App\Platform\Workflows\Domain\ValueObjects\WorkflowContext;
use App\Platform\Workflows\Domain\ValueObjects\WorkflowResult;

class BookingWorkflowHandler implements WorkflowHandler
{
    public function __construct(
        protected BookingLifecycleService $lifecycleService
    ) {}

    public function entityClass(): string
    {
        return Booking::class;
    }

    public function workflowKey(): string
    {
        return 'booking.approval';
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
        $booking = $entity;
        $stateValue = $context->metadata['target_state'] ?? null;
        $targetStatus = $stateValue ? strtolower($stateValue) : match ($transition) {
            'approve' => BookingStatus::Approved->value,
            'reject' => BookingStatus::Rejected->value,
            'request_changes' => BookingStatus::Draft->value,
            default => throw new \InvalidArgumentException("Invalid workflow transition: {$transition}"),
        };

        $this->lifecycleService->transitionFromWorkflow(
            $booking,
            $targetStatus,
            $context->comments
        );

        return WorkflowResult::create(
            success: true,
            previousState: $booking->status->value ?? $booking->status,
            newState: $targetStatus,
            metadata: ['booking_code' => $booking->booking_code]
        );
    }

    public function compensate(
        object $entity,
        \App\Platform\Workflows\Domain\Entities\WorkflowHistory $history,
        WorkflowContext $context
    ): WorkflowResult {
        $booking = $entity;
        $previousState = $booking->status->value ?? $booking->status;
        $targetStatus = $history->from_state ?? BookingStatus::Draft->value;

        $this->lifecycleService->transitionFromWorkflow(
            $booking,
            $targetStatus,
            'Saga rollback compensation executed.'
        );

        return WorkflowResult::create(
            success: true,
            previousState: $previousState,
            newState: $targetStatus
        );
    }
}
