<?php

declare(strict_types=1);

namespace App\Core\Context;

use App\Platform\Identity\Application\Services\IdentityContext;

class ContextManager
{
    /**
     * Boot all platform contexts (Trace, Identity, etc.) for a request or background task.
     */
    public static function boot(): void
    {
        // 1. Enforce TraceContext registration
        if (!app()->bound(TraceContext::class)) {
            app()->singleton(TraceContext::class, function () {
                return new TraceContext();
            });
        }

        // 2. Initialize Identity Context from auth
        IdentityContext::initFromAuth();
    }

    /**
     * Clear all platform contexts to prevent leakage (CLI, queue workers, testing).
     */
    public static function clear(): void
    {
        IdentityContext::clear();

        if (app()->bound(TraceContext::class)) {
            app()->forgetInstance(TraceContext::class);
        }
    }
}
