<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Contracts;

interface WorkflowGuard
{
    /**
     * Evaluate if a transition state transition can proceed.
     *
     * @param object $entity Target business model.
     * @param array $variables Runtime state variables.
     * @param array $options Configuration options mapped from the DSL definition.
     * @return bool True if the transition is allowed.
     */
    public function evaluate(object $entity, array $variables, array $options = []): bool;
}
