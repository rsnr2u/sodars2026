<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Events;

use App\Core\Events\BusinessEvent;

abstract class AbstractInventoryEvent extends BusinessEvent
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
        return \App\Modules\Inventory\Domain\Entities\Inventory::class;
    }
}
