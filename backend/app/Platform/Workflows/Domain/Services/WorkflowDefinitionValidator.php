<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Services;

use InvalidArgumentException;

class WorkflowDefinitionValidator
{
    /**
     * Validate the workflow DSL structure.
     *
     * @throws InvalidArgumentException if validation fails.
     */
    public function validate(array $dsl): void
    {
        if (empty($dsl['key'])) {
            throw new InvalidArgumentException("Workflow DSL must define a 'key'.");
        }

        if (empty($dsl['states']) || !is_array($dsl['states'])) {
            throw new InvalidArgumentException("Workflow DSL must define an array of 'states'.");
        }

        if (empty($dsl['initial_state']) || !in_array($dsl['initial_state'], $dsl['states'], true)) {
            throw new InvalidArgumentException("Workflow DSL must define a valid 'initial_state' present in states.");
        }

        if (isset($dsl['steps'])) {
            if (!is_array($dsl['steps'])) {
                throw new InvalidArgumentException("'steps' must be a valid array.");
            }
            foreach ($dsl['steps'] as $index => $step) {
                if (empty($step['name'])) {
                    throw new InvalidArgumentException("Step at index {$index} is missing 'name'.");
                }
                if (!isset($step['order'])) {
                    throw new InvalidArgumentException("Step [{$step['name']}] is missing 'order'.");
                }
            }
        }

        if (isset($dsl['transitions'])) {
            if (!is_array($dsl['transitions'])) {
                throw new InvalidArgumentException("'transitions' must be a valid array.");
            }
            foreach ($dsl['transitions'] as $index => $transition) {
                if (empty($transition['name'])) {
                    throw new InvalidArgumentException("Transition at index {$index} is missing 'name'.");
                }
                if (empty($transition['from']) || !in_array($transition['from'], $dsl['states'], true)) {
                    throw new InvalidArgumentException("Transition [{$transition['name']}] contains an invalid 'from' state.");
                }
                if (empty($transition['to']) || !in_array($transition['to'], $dsl['states'], true)) {
                    throw new InvalidArgumentException("Transition [{$transition['name']}] contains an invalid 'to' state.");
                }
            }
        }
    }
}
