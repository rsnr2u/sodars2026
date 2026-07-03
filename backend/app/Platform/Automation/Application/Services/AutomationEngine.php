<?php

declare(strict_types=1);

namespace App\Platform\Automation\Application\Services;

use App\Platform\Automation\Domain\Entities\AutomationRule;
use App\Platform\Automation\Domain\Entities\AutomationExecution;
use App\Platform\Automation\Domain\Registry\ActionStrategyRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class AutomationEngine
{
    public function __construct(
        protected ExpressionCompiler $compiler,
        protected ExpressionEvaluator $evaluator,
        protected ActionStrategyRegistry $registry
    ) {}

    /**
     * Run all active automation rules matching an event class.
     */
    public function run(string $eventClass, array $eventPayload): void
    {
        $rules = AutomationRule::where('event_class', $eventClass)
            ->where('is_active', true)
            ->get();

        foreach ($rules as $rule) {
            $startTime = microtime(true);
            $executionId = (string) Str::uuid();

            // Create baseline execution record
            $execution = AutomationExecution::create([
                'id' => $executionId,
                'rule_id' => $rule->id,
                'event_name' => $eventClass,
                'context_snapshot' => $eventPayload,
                'status' => 'processing',
                'started_at' => now(),
            ]);

            try {
                // 1. Compile & evaluate conditions
                $compiledConditions = $this->compiler->compile($rule->conditions ?? []);
                $isMatched = $this->evaluator->evaluate($compiledConditions, $eventPayload);

                if ($isMatched) {
                    // 2. Execute actions
                    foreach ($rule->actions ?? [] as $actionConfig) {
                        $actionKey = $actionConfig['type'] ?? null;
                        $params = $actionConfig['params'] ?? [];

                        if ($actionKey) {
                            $strategy = $this->registry->resolve($actionKey);
                            $strategy->execute($rule, $params, $eventPayload);
                        }
                    }

                    $execution->update([
                        'status' => 'success',
                        'completed_at' => now(),
                        'execution_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
                    ]);
                } else {
                    $execution->update([
                        'status' => 'skipped',
                        'completed_at' => now(),
                        'execution_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
                    ]);
                }
            } catch (Throwable $e) {
                $execution->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'completed_at' => now(),
                    'execution_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
                ]);

                \Illuminate\Support\Facades\Log::error("Automation rule {$rule->name} failed to execute: " . $e->getMessage(), [
                    'exception' => $e,
                    'rule_id' => $rule->id,
                    'event' => $eventClass,
                ]);
            }
        }
    }
}
