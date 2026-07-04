<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Events;

use App\Modules\CRM\Domain\Entities\Opportunity;

class OpportunityStageChanged extends AbstractCrmEvent
{
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
            entityClass: Opportunity::class,
            aggregateId: $aggregateId,
            aggregateVersion: $aggregateVersion,
            data: $data,
            occurredAt: $occurredAt,
            correlationId: $correlationId,
            traceId: $traceId,
            userId: $userId,
            metadata: $metadata
        );
    }
}
