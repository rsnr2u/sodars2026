<?php

declare(strict_types=1);

namespace App\Core\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

abstract class BaseService
{
    /**
     * Execute a callback within a database transaction.
     */
    protected function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    /**
     * Dispatch a domain event.
     */
    protected function dispatchEvent(object $event): void
    {
        Event::dispatch($event);
    }
}
