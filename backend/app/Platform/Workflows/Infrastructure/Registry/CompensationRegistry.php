<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Infrastructure\Registry;

use App\Platform\Workflows\Domain\Contracts\WorkflowHandler;

class CompensationRegistry
{
    public function __construct(
        protected WorkflowRegistry $workflowRegistry
    ) {}

    /**
     * Resolve the compensation handler associated with the target entity type.
     */
    public function resolve(string $entityType): WorkflowHandler
    {
        return $this->workflowRegistry->resolve($entityType);
    }
}
