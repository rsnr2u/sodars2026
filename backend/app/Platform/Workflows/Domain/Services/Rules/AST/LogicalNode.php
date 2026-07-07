<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Services\Rules\AST;

use App\Platform\Workflows\Domain\Services\Rules\Evaluator;

class LogicalNode implements Node
{
    public function __construct(
        protected Node $left,
        protected string $operator,
        protected Node $right
    ) {}

    public function getLeft(): Node
    {
        return $this->left;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getRight(): Node
    {
        return $this->right;
    }

    public function accept(Evaluator $evaluator, array $variables): mixed
    {
        return $evaluator->visitLogical($this, $variables);
    }
}
