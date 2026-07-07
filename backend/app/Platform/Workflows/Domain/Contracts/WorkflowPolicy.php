<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Contracts;

interface WorkflowPolicy
{
    /**
     * Determine if a user or role is authorized to perform action/transition in this step.
     */
    public function authorize(
        object $entity,
        array $tasks,
        string $role,
        ?string $userId = null
    ): bool;
}
