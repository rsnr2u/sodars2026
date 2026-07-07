<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Infrastructure\Registry;

use App\Platform\Workflows\Domain\Contracts\WorkflowHandler;
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

        // Verify it implements the correct interface
        if (!is_subclass_of($handlerClass, WorkflowHandler::class)) {
            throw new InvalidArgumentException("Handler class {$handlerClass} must implement " . WorkflowHandler::class);
        }

        $this->handlers[$entityType] = $handlerClass;
    }

    /**
     * Resolve a transition handler instance for an entity type.
     */
    public function resolve(string $entityType): WorkflowHandler
    {
        if (!isset($this->handlers[$entityType])) {
            // Check fallback for proxies or parent classes
            foreach ($this->handlers as $registeredType => $handlerClass) {
                if (is_subclass_of($entityType, $registeredType) || $entityType === $registeredType) {
                    return app($handlerClass);
                }
            }
            throw new InvalidArgumentException("No workflow transition handler registered for entity type: {$entityType}");
        }

        $handlerClass = $this->handlers[$entityType];
        return app($handlerClass);
    }
}
