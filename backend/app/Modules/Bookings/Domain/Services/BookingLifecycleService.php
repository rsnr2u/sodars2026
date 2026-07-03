<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Services;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Entities\BookingStatusHistory;
use App\Modules\Bookings\Domain\Entities\BookingActivity;
use App\Modules\Bookings\Domain\Enums\BookingStatus;
use App\Modules\Bookings\Domain\Events\BookingStatusChanged;
use App\Modules\Inventory\Domain\Entities\InventoryAvailability;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingLifecycleService
{
    public function __construct(
        protected OutboxService $outboxService
    ) {}

    /**
     * Standard state-machine–governed transition.
     * Validates that the target status is reachable from the current status.
     */
    public function transition(Booking $booking, string $targetStatus, ?string $comment = null): Booking
    {
        return DB::transaction(function () use ($booking, $targetStatus, $comment) {
            $fromStatus = $booking->status;

            if (!$fromStatus->canTransitionTo($targetStatus)) {
                throw ValidationException::withMessages([
                    'status' => [sprintf("Cannot transition booking from '%s' to '%s'.", $fromStatus->value, $targetStatus)],
                ]);
            }

            return $this->applyTransition($booking, $fromStatus->value, $targetStatus, $comment);
        });
    }

    /**
     * Workflow-governed transition.
     *
     * Bypasses the domain state-machine validation because the workflow engine
     * has already enforced its own multi-step approval pipeline. All domain
     * side effects (history, outbox, events, activity audit, inventory) are
     * still executed so the aggregate remains the single source of truth.
     */
    public function transitionFromWorkflow(Booking $booking, string $targetStatus, ?string $comment = null): Booking
    {
        return DB::transaction(function () use ($booking, $targetStatus, $comment) {
            $fromStatus = $booking->status->value;
            return $this->applyTransition($booking, $fromStatus, $targetStatus, $comment);
        });
    }

    /**
     * Shared transition logic: update status, record history, publish events, audit, side effects.
     */
    protected function applyTransition(Booking $booking, string $fromStatus, string $targetStatus, ?string $comment): Booking
    {
        // Update status
        $booking->update(['status' => $targetStatus]);

        // Save history log
        BookingStatusHistory::create([
            'id' => (string) Str::uuid(),
            'booking_id' => $booking->id,
            'changed_by' => auth()->id() ?? $booking->customer_id,
            'from_status' => $fromStatus,
            'to_status' => $targetStatus,
            'comment' => $comment,
        ]);

        $eventData = [
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'from_status' => $fromStatus,
            'to_status' => $targetStatus,
            'comment' => $comment,
        ];

        // 1. Transactional Outbox
        $this->outboxService->record(
            aggregateType: 'Booking',
            aggregateId: $booking->id,
            eventName: 'booking.status_changed.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        // 2. Dispatch Local Domain Event
        Event::dispatch(new BookingStatusChanged(
            aggregateId: $booking->id,
            aggregateVersion: 1,
            data: $eventData,
            occurredAt: now()->toIso8601String(),
            correlationId: TraceContext::correlationId() ?? (string) Str::uuid(),
            traceId: TraceContext::traceId() ?? (string) Str::uuid()
        ));

        // 3. Activity audit timeline entry
        BookingActivity::create([
            'id' => (string) Str::uuid(),
            'booking_id' => $booking->id,
            'performed_by' => auth()->id(),
            'event_name' => 'booking.status_changed.v1',
            'action' => 'StatusTransitioned',
            'old_values' => ['status' => $fromStatus],
            'new_values' => ['status' => $targetStatus, 'comment' => $comment],
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'trace_id' => TraceContext::traceId() ?? (string) Str::uuid(),
        ]);

        // 4. Handle specialized state callbacks
        $this->handleStateLifecycleSideEffects($booking, $targetStatus);

        return $booking;
    }

    protected function handleStateLifecycleSideEffects(Booking $booking, string $status): void
    {
        // If approved, lock availability ledger slots permanently
        if ($status === BookingStatus::Approved->value) {
            foreach ($booking->items as $item) {
                InventoryAvailability::create([
                    'id' => (string) Str::uuid(),
                    'inventory_face_id' => $item->inventory_face_id,
                    'start_at' => $item->start_date->startOfDay(),
                    'end_at' => $item->end_date->endOfDay(),
                    'availability_status' => 'reserved',
                    'reason' => "Booking approved: {$booking->booking_code}",
                    'source' => 'Booking',
                ]);
            }
        }

        // If cancelled or rejected, release locks (delete the reservations matching this booking code)
        if ($status === BookingStatus::Cancelled->value || $status === BookingStatus::Rejected->value) {
            InventoryAvailability::where('source', 'Booking')
                ->where('reason', 'like', "%{$booking->booking_code}%")
                ->delete();
        }
    }
}

