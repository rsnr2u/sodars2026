<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Services;

use App\Platform\Workflows\Domain\Services\Rules\Lexer;
use App\Platform\Workflows\Domain\Services\Rules\Parser;
use App\Platform\Workflows\Domain\Services\Rules\Evaluator;
use App\Platform\Workflows\Domain\Services\Rules\FunctionRegistry;

class RuleEngine
{
    protected Lexer $lexer;
    protected Parser $parser;
    protected Evaluator $evaluator;
    protected FunctionRegistry $registry;

    public function __construct()
    {
        $this->lexer = new Lexer();
        $this->parser = new Parser();
        $this->registry = new FunctionRegistry();
        $this->evaluator = new Evaluator($this->registry);
    }

    /**
     * Parse and evaluate a rule expression against a set of variable inputs.
     */
    public function evaluate(string $expression, array $variables): bool
    {
        $expression = trim($expression);
        if ($expression === '') {
            return true;
        }

        $tokens = $this->lexer->tokenize($expression);
        $ast = $this->parser->parse($tokens);

        return (bool) $ast->accept($this->evaluator, $variables);
    }

    /**
     * Get the rule engine's function registry to register custom execution functions.
     */
    public function getFunctionRegistry(): FunctionRegistry
    {
        return $this->registry;
    }
}
