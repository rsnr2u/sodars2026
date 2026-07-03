<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class AbstractProviderEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly string $aggregateId,
        public readonly int $aggregateVersion,
        public readonly array $data,
        public readonly string $occurredAt,
        public readonly string $correlationId,
        public readonly string $traceId,
        public readonly ?string $userId
    ) {}
}
