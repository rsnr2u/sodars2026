<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Services\Rules;

use App\Platform\Workflows\Domain\Services\Rules\AST\LiteralNode;
use App\Platform\Workflows\Domain\Services\Rules\AST\VariableNode;
use App\Platform\Workflows\Domain\Services\Rules\AST\BinaryOperatorNode;
use App\Platform\Workflows\Domain\Services\Rules\AST\LogicalNode;
use App\Platform\Workflows\Domain\Services\Rules\AST\FunctionNode;
use RuntimeException;

class Evaluator
{
    public function __construct(protected FunctionRegistry $registry) {}

    public function visitLiteral(LiteralNode $node, array $variables): mixed
    {
        return $node->getValue();
    }

    public function visitVariable(VariableNode $node, array $variables): mixed
    {
        $name = $node->getName();
        return $variables[$name] ?? null;
    }

    public function visitBinaryOperator(BinaryOperatorNode $node, array $variables): bool
    {
        $left = $node->getLeft()->accept($this, $variables);
        $operator = $node->getOperator();
        $right = $node->getRight()->accept($this, $variables);

        if (is_string($left) && is_numeric($right)) {
            $left = str_contains((string)$left, '.') ? (float)$left : (int)$left;
        }

        return match ($operator) {
            '==' => $left == $right,
            '!=' => $left != $right,
            '>' => $left > $right,
            '<' => $left < $right,
            '>=' => $left >= $right,
            '<=' => $left <= $right,
            default => false,
        };
    }

    public function visitLogical(LogicalNode $node, array $variables): bool
    {
        $left = (bool) $node->getLeft()->accept($this, $variables);
        $operator = $node->getOperator();

        if ($operator === '&&') {
            return $left && (bool) $node->getRight()->accept($this, $variables);
        }

        if ($operator === '||') {
            return $left || (bool) $node->getRight()->accept($this, $variables);
        }

        return false;
    }

    public function visitFunction(FunctionNode $node, array $variables): mixed
    {
        $args = [];
        foreach ($node->getArguments() as $arg) {
            $args[] = $arg->accept($this, $variables);
        }

        return $this->registry->call($node->getName(), $args);
    }
}
