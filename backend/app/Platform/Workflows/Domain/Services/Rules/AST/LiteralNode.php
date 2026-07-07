<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Services\Rules\AST;

use App\Platform\Workflows\Domain\Services\Rules\Evaluator;

class LiteralNode implements Node
{
    public function __construct(protected mixed $value) {}

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function accept(Evaluator $evaluator, array $variables): mixed
    {
        return $evaluator->visitLiteral($this, $variables);
    }
}
