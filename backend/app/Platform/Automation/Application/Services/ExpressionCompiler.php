<?php

declare(strict_types=1);

namespace App\Platform\Automation\Application\Services;

use InvalidArgumentException;

class ExpressionCompiler
{
    private array $cache = [];

    /**
     * Compile and validate the JSON condition tree.
     */
    public function compile(array $conditions): array
    {
        $hash = md5(serialize($conditions));
        if (isset($this->cache[$hash])) {
            return $this->cache[$hash];
        }

        $this->validateNode($conditions);

        $this->cache[$hash] = $conditions;
        return $conditions;
    }

    /**
     * Recursively validate condition tree nodes.
     */
    protected function validateNode(array $node): void
    {
        if (empty($node)) {
            return;
        }

        if (!isset($node['logical_operator'])) {
            // Must be a simple rule node
            if (!isset($node['field'])) {
                throw new InvalidArgumentException("Condition rule must specify a 'field'.");
            }
            return;
        }

        $operator = strtolower($node['logical_operator']);
        if (!in_array($operator, ['and', 'or', 'not'], true)) {
            throw new InvalidArgumentException("Invalid logical operator: {$operator}");
        }

        $rules = $node['rules'] ?? [];
        if (!is_array($rules)) {
            throw new InvalidArgumentException("Logical group 'rules' must be an array.");
        }

        foreach ($rules as $rule) {
            $this->validateNode($rule);
        }
    }
}
