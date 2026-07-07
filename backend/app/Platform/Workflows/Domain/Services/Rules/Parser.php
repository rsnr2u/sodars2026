<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Services\Rules;

use App\Platform\Workflows\Domain\Services\Rules\AST\Node;
use App\Platform\Workflows\Domain\Services\Rules\AST\LiteralNode;
use App\Platform\Workflows\Domain\Services\Rules\AST\VariableNode;
use App\Platform\Workflows\Domain\Services\Rules\AST\BinaryOperatorNode;
use App\Platform\Workflows\Domain\Services\Rules\AST\LogicalNode;
use App\Platform\Workflows\Domain\Services\Rules\AST\FunctionNode;
use RuntimeException;

class Parser
{
    protected array $tokens = [];
    protected int $index = 0;

    /**
     * Parse tokens into a Node AST structure.
     */
    public function parse(array $tokens): Node
    {
        $this->tokens = $tokens;
        $this->index = 0;

        return $this->parseLogical();
    }

    protected function parseLogical(): Node
    {
        $left = $this->parseComparison();

        while ($this->hasMore() && $this->peek()['type'] === 'LOGICAL') {
            $operatorToken = $this->consume();
            $right = $this->parseComparison();
            $left = new LogicalNode($left, $operatorToken['value'], $right);
        }

        return $left;
    }

    protected function parseComparison(): Node
    {
        $left = $this->parsePrimary();

        if ($this->hasMore() && $this->peek()['type'] === 'OPERATOR') {
            $operatorToken = $this->consume();
            $right = $this->parsePrimary();
            return new BinaryOperatorNode($left, $operatorToken['value'], $right);
        }

        return $left;
    }

    protected function parsePrimary(): Node
    {
        if (!$this->hasMore()) {
            throw new RuntimeException("Unexpected end of expression while parsing.");
        }

        $token = $this->peek();

        if ($token['type'] === 'LITERAL') {
            $this->consume();
            return new LiteralNode($token['value']);
        }

        if ($token['type'] === 'IDENTIFIER') {
            $nameToken = $this->consume();

            // Check if function call
            if ($this->hasMore() && $this->peek()['value'] === '(') {
                $this->consume(); // consume '('
                $args = [];

                if ($this->hasMore() && $this->peek()['value'] !== ')') {
                    $args[] = $this->parseLogical();
                    while ($this->hasMore() && $this->peek()['value'] === ',') {
                        $this->consume(); // consume ','
                        $args[] = $this->parseLogical();
                    }
                }

                if (!$this->hasMore() || $this->peek()['value'] !== ')') {
                    throw new RuntimeException("Missing closing parenthesis in function call.");
                }
                $this->consume(); // consume ')'

                return new FunctionNode($nameToken['value'], $args);
            }

            return new VariableNode($nameToken['value']);
        }

        if ($token['value'] === '(') {
            $this->consume(); // consume '('
            $node = $this->parseLogical();
            if (!$this->hasMore() || $this->peek()['value'] !== ')') {
                throw new RuntimeException("Missing closing parenthesis.");
            }
            $this->consume(); // consume ')'
            return $node;
        }

        throw new RuntimeException("Unexpected token: " . json_encode($token));
    }

    protected function peek(): ?array
    {
        return $this->tokens[$this->index] ?? null;
    }

    protected function consume(): array
    {
        $token = $this->tokens[$this->index] ?? null;
        if ($token === null) {
            throw new RuntimeException("Unexpected end of token stream.");
        }
        $this->index++;
        return $token;
    }

    protected function hasMore(): bool
    {
        return $this->index < count($this->tokens);
    }
}
