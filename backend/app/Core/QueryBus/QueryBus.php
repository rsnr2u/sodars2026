<?php

declare(strict_types=1);

namespace App\Core\QueryBus;

use Illuminate\Support\Facades\App;
use RuntimeException;

class QueryBus
{
    /**
     * Resolve and execute the handler for a given query.
     */
    public function ask(object $query): mixed
    {
        $queryClass = get_class($query);

        // Auto-resolve: convert Query suffix to QueryHandler or Handler
        $handlerClass = str_replace('Query', 'QueryHandler', $queryClass);
        if (! class_exists($handlerClass)) {
            $handlerClass = str_replace('Query', 'Handler', $queryClass);
        }

        if (! class_exists($handlerClass)) {
            throw new RuntimeException("Handler class [{$handlerClass}] not found for query [{$queryClass}].");
        }

        $handler = App::make($handlerClass);

        if (! method_exists($handler, 'handle')) {
            throw new RuntimeException("Handler [{$handlerClass}] must implement handle() method.");
        }

        return $handler->handle($query);
    }
}
