<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Contracts;

use App\Platform\Workflows\Domain\ValueObjects\WorkflowContext;
use App\Platform\Workflows\Domain\ValueObjects\WorkflowResult;

interface WorkflowHandler
{
    /**
     * Get the fully qualified class name of the target entity.
     */
    public function entityClass(): string;

    /**
     * Get the workflow template key associated with this handler.
     */
    public function workflowKey(): string;

    /**
     * Get a list of available transition names for the entity.
     */
    public function availableTransitions(object $entity): array;

    /**
     * Execute a specific transition on the entity within the provided context.
     */
    public function transition(
        object $entity,
        string $transition,
        WorkflowContext $context
    ): WorkflowResult;

    /**
     * Compensate (rollback) a previously executed transition using history log detail.
     */
    public function compensate(
        object $entity,
        \App\Platform\Workflows\Domain\Entities\WorkflowHistory $history,
        WorkflowContext $context
    ): WorkflowResult;
}
