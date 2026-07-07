<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Services\Rules\AST;

use App\Platform\Workflows\Domain\Services\Rules\Evaluator;

class VariableNode implements Node
{
    public function __construct(protected string $name) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function accept(Evaluator $evaluator, array $variables): mixed
    {
        return $evaluator->visitVariable($this, $variables);
    }
}
