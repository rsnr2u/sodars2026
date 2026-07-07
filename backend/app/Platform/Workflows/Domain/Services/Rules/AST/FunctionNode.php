<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Services\Rules\AST;

use App\Platform\Workflows\Domain\Services\Rules\Evaluator;

class FunctionNode implements Node
{
    /**
     * @param Node[] $arguments
     */
    public function __construct(
        protected string $name,
        protected array $arguments
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Node[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function accept(Evaluator $evaluator, array $variables): mixed
    {
        return $evaluator->visitFunction($this, $variables);
    }
}
