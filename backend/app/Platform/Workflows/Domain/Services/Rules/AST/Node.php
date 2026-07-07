<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Services\Rules\AST;

use App\Platform\Workflows\Domain\Services\Rules\Evaluator;

interface Node
{
    /**
     * Accept the visitor evaluator to compute the node's value.
     */
    public function accept(Evaluator $evaluator, array $variables): mixed;
}
