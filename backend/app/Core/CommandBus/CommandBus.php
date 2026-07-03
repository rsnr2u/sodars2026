<?php

declare(strict_types=1);

namespace App\Core\CommandBus;

use Illuminate\Support\Facades\App;
use RuntimeException;

class CommandBus
{
    /**
     * Resolve and execute the handler for a given command.
     */
    public function dispatch(object $command): mixed
    {
        $commandClass = get_class($command);

        // Auto-resolve: convert Command suffix to Handler or Action
        $handlerClass = str_replace('Command', 'Handler', $commandClass);
        if (! class_exists($handlerClass)) {
            $handlerClass = str_replace('Command', 'Action', $commandClass);
        }

        if (! class_exists($handlerClass)) {
            throw new RuntimeException("Handler class [{$handlerClass}] not found for command [{$commandClass}].");
        }

        $handler = App::make($handlerClass);

        if (! method_exists($handler, 'handle')) {
            throw new RuntimeException("Handler [{$handlerClass}] must implement handle() method.");
        }

        return $handler->handle($command);
    }
}
