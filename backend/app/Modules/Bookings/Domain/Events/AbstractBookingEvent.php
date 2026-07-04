<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Core\Events\BusinessEvent;

abstract class AbstractBookingEvent extends BusinessEvent
{
    /**
     * Map old properties to standard BusinessEvent constructor.
     */
    public function __construct(
        string $aggregateId,
        int $aggregateVersion,
        array $data,
        ?string $occurredAt = null,
        ?string $correlationId = null,
        ?string $traceId = null,
        ?string $userId = null,
        array $metadata = []
    ) {
        parent::__construct(
            aggregateId: $aggregateId,
            aggregateVersion: $aggregateVersion,
            data: $data,
            occurredAt: $occurredAt,
            correlationId: $correlationId,
            traceId: $traceId,
            actorId: $userId,
            metadata: $metadata
        );
    }

    public function getEntityClass(): string
    {
        return \App\Modules\Bookings\Domain\Entities\Booking::class;
    }
}
