<?php

declare(strict_types=1);

namespace App\Platform\Automation\Domain\Registry;

use App\Platform\Automation\Domain\Contracts\AutomationActionStrategy;
use InvalidArgumentException;

class ActionStrategyRegistry
{
    /**
     * Maps namespaced action keys to strategy class strings.
     *
     * @var array<string, string>
     */
    private array $strategies = [];

    /**
     * Register a strategy class.
     */
    public function register(string $actionKey, string $strategyClass): void
    {
        if (!class_exists($strategyClass)) {
            throw new InvalidArgumentException("Strategy class {$strategyClass} does not exist.");
        }

        $this->strategies[$actionKey] = $strategyClass;
    }

    /**
     * Resolve and return a strategy instance.
     */
    public function resolve(string $actionKey): AutomationActionStrategy
    {
        if (!isset($this->strategies[$actionKey])) {
            throw new InvalidArgumentException("No automation action strategy registered for key: {$actionKey}");
        }

        $strategyClass = $this->strategies[$actionKey];
        return app($strategyClass);
    }
}
