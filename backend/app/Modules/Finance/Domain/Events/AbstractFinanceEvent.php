<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Events;

use App\Core\Events\BusinessEvent;

abstract class AbstractFinanceEvent extends BusinessEvent
{
    /**
     * Setup AbstractFinanceEvent delegating parameters to BusinessEvent.
     */
    public function __construct(
        string $aggregateId,
        int $aggregateVersion,
        array $data,
        ?string $occurredAt = null,
        ?string $correlationId = null,
        ?string $traceId = null,
        ?string $userId = null,
        array $metadata = [],
        protected readonly string $entityClass = ''
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
        return $this->entityClass;
    }
}
