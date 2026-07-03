<?php

declare(strict_types=1);

namespace App\Platform\Automation\Application\Services;

class ExpressionEvaluator
{
    /**
     * Evaluate the condition tree against the event payload.
     */
    public function evaluate(array $tree, array $payload): bool
    {
        if (empty($tree)) {
            return true;
        }

        $logicalOperator = strtolower($tree['logical_operator'] ?? 'and');
        $rules = $tree['rules'] ?? [];

        if (empty($rules)) {
            return true;
        }

        $results = [];
        foreach ($rules as $rule) {
            if (isset($rule['logical_operator'])) {
                // Nested group
                $results[] = $this->evaluate($rule, $payload);
            } else {
                // Simple rule
                $results[] = $this->evaluateRule($rule, $payload);
            }
        }

        if ($logicalOperator === 'or') {
            return in_array(true, $results, true);
        }

        // Default to AND
        return !in_array(false, $results, true);
    }

    /**
     * Evaluate a single condition rule.
     */
    protected function evaluateRule(array $rule, array $payload): bool
    {
        $field = $rule['field'] ?? null;
        $operator = $rule['operator'] ?? '==';
        $expected = $rule['value'] ?? null;

        if ($field === null) {
            return false;
        }

        $actual = data_get($payload, $field);

        switch ($operator) {
            case '==':
                return $actual == $expected;
            case '!=':
                return $actual != $expected;
            case '>':
                return $actual > $expected;
            case '<':
                return $actual < $expected;
            case '>=':
                return $actual >= $expected;
            case '<=':
                return $actual <= $expected;
            case 'in':
                return is_array($expected) && in_array($actual, $expected, true);
            case 'contains':
                return is_string($actual) && is_string($expected) && str_contains($actual, $expected);
            default:
                return false;
        }
    }
}
