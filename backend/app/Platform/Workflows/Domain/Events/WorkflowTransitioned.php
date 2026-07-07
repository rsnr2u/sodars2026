<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkflowTransitioned
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly string $aggregateId,
        public readonly string $fromState,
        public readonly string $toState,
        public readonly ?string $userId = null
    ) {}
}
