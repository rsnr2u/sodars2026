<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Infrastructure\Registry;

use App\Platform\Workflows\Domain\Contracts\WorkflowTransitionHandler;
use InvalidArgumentException;

class WorkflowRegistry
{
    /**
     * Maps entity class string to transition handler class string.
     *
     * @var array<string, string>
     */
    private array $handlers = [];

    /**
     * Register a transition handler for an entity type.
     */
    public function register(string $entityType, string $handlerClass): void
    {
        if (!class_exists($handlerClass)) {
            throw new InvalidArgumentException("Handler class {$handlerClass} does not exist.");
        }

        $this->handlers[$entityType] = $handlerClass;
    }

    /**
     * Resolve a transition handler instance for an entity type.
     */
    public function resolve(string $entityType): WorkflowTransitionHandler
    {
        if (!isset($this->handlers[$entityType])) {
            throw new InvalidArgumentException("No workflow transition handler registered for entity type: {$entityType}");
        }

        $handlerClass = $this->handlers[$entityType];
        return app($handlerClass);
    }
}
