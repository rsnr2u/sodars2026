<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Infrastructure\Workflows;

use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Enums\BookingStatus;
use App\Modules\Bookings\Domain\Services\BookingLifecycleService;
use App\Platform\Workflows\Domain\Contracts\WorkflowTransitionHandler;
use App\Platform\Workflows\Domain\Entities\WorkflowInstance;
use App\Platform\Workflows\Domain\ValueObjects\WorkflowTransitionResult;

/**
 * Workflow transition handler for Bookings.
 *
 * Delegates to BookingLifecycleService::transitionFromWorkflow() so that the
 * Booking aggregate remains the single authority for state changes. The workflow
 * engine replaces the state-machine validation, but all domain side effects
 * (history, outbox, events, activity audit, inventory) still execute.
 */
class BookingWorkflowHandler implements WorkflowTransitionHandler
{
    public function __construct(
        protected BookingLifecycleService $lifecycleService
    ) {}

    public function approve(WorkflowInstance $instance): WorkflowTransitionResult
    {
        $booking = Booking::findOrFail($instance->entity_id);
        $comment = $this->extractLastComment($instance, 'approve_step');

        $this->lifecycleService->transitionFromWorkflow(
            $booking,
            BookingStatus::Approved->value,
            $comment
        );

        return WorkflowTransitionResult::create(
            true,
            BookingStatus::Approved->value,
            [],
            [],
            ['booking_code' => $booking->booking_code]
        );
    }

    public function reject(WorkflowInstance $instance): WorkflowTransitionResult
    {
        $booking = Booking::findOrFail($instance->entity_id);
        $comment = $this->extractLastComment($instance, 'reject_step');

        $this->lifecycleService->transitionFromWorkflow(
            $booking,
            BookingStatus::Rejected->value,
            $comment
        );

        return WorkflowTransitionResult::create(
            true,
            BookingStatus::Rejected->value,
            [],
            [],
            ['booking_code' => $booking->booking_code]
        );
    }

    public function requestChanges(WorkflowInstance $instance): WorkflowTransitionResult
    {
        $booking = Booking::findOrFail($instance->entity_id);
        $comment = $this->extractLastComment($instance, 'request_changes');

        $this->lifecycleService->transitionFromWorkflow(
            $booking,
            BookingStatus::Draft->value,
            $comment
        );

        return WorkflowTransitionResult::create(
            true,
            BookingStatus::Draft->value,
            [],
            [],
            ['booking_code' => $booking->booking_code]
        );
    }

    /**
     * Extract the most recent workflow history comment for a given action.
     */
    protected function extractLastComment(WorkflowInstance $instance, string $action): ?string
    {
        return $instance->histories()
            ->where('action', $action)
            ->orderBy('created_at', 'desc')
            ->first()
            ?->comments;
    }
}


