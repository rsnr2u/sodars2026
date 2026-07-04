<?php

declare(strict_types=1);

namespace App\Platform\Identity\Domain\Contracts;

/**
 * ABAC Policy Evaluator placeholder.
 * Future: Attribute-Based Access Control evaluation.
 */
interface PolicyEvaluator
{
    public function evaluate(string $action, mixed $subject, array $context = []): bool;
}
