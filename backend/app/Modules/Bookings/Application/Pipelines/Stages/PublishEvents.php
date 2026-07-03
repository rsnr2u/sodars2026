<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\Pipelines\Stages;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Bookings\Domain\Events\BookingCreated;
use App\Modules\Bookings\Domain\Entities\BookingActivity;
use Closure;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class PublishEvents
{
    public function __construct(
        protected OutboxService $outboxService
    ) {}

    public function handle(array $passable, Closure $next): mixed
    {
        $booking = $passable['booking'];

        $eventData = [
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'customer_id' => $booking->customer_id,
            'branch_id' => $booking->branch_id,
            'start_date' => $booking->start_date->toDateString(),
            'end_date' => $booking->end_date->toDateString(),
            'grand_total_cents' => $booking->grand_total_cents,
            'status' => $booking->status->value,
        ];

        // 1. Transactional Outbox
        $this->outboxService->record(
            aggregateType: 'Booking',
            aggregateId: $booking->id,
            eventName: 'booking.created.v1',
            data: $eventData,
            eventVersion: 1,
            schemaVersion: '1.0.0'
        );

        // 2. Dispatch Local Domain Event
        Event::dispatch(new BookingCreated(
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
            'performed_by' => auth()->id() ?? $booking->customer_id,
            'event_name' => 'booking.created.v1',
            'action' => 'Created',
            'old_values' => null,
            'new_values' => $booking->toArray(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'trace_id' => TraceContext::traceId() ?? (string) Str::uuid(),
        ]);

        return $next($passable);
    }
}
