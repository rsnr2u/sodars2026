<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Services;

use InvalidArgumentException;

class WorkflowDefinitionCompiler
{
    public function __construct(
        protected WorkflowDefinitionValidator $validator
    ) {}

    /**
     * Compile the DSL structure.
     * Checks unreachable states and transitions completeness.
     */
    public function compile(array $dsl): array
    {
        // First validate
        $this->validator->validate($dsl);

        $states = $dsl['states'];
        $transitions = $dsl['transitions'] ?? [];
        $initialState = $dsl['initial_state'];

        // Graph reachability analysis: starting from initial state
        $reachable = [$initialState => true];
        $queue = [$initialState];

        while (!empty($queue)) {
            $current = array_shift($queue);
            foreach ($transitions as $transition) {
                if ($transition['from'] === $current) {
                    $to = $transition['to'];
                    if (!isset($reachable[$to])) {
                        $reachable[$to] = true;
                        $queue[] = $to;
                    }
                }
            }
        }

        // Detect unreachable states
        $unreachable = [];
        foreach ($states as $state) {
            if (!isset($reachable[$state])) {
                $unreachable[] = $state;
            }
        }

        if (!empty($unreachable)) {
            throw new InvalidArgumentException("Workflow definition has unreachable states: " . implode(', ', $unreachable));
        }

        // Add compilation header metadata
        $compiled = $dsl;
        $compiled['compiled_at'] = now()->toIso8601String();
        $compiled['checksum'] = md5(json_encode($dsl));

        return $compiled;
    }
}
