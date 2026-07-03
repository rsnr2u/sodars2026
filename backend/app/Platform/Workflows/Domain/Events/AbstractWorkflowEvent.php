<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class AbstractWorkflowEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly string $aggregateId,
        public readonly array $data = [],
        public readonly ?string $userId = null
    ) {}
}
