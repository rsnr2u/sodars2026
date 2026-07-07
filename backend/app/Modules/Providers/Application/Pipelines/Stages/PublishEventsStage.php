<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Pipelines\Stages;

use App\Modules\Providers\Application\Services\ProviderLifecycleService;
use Closure;

class PublishEventsStage
{
    public function __construct(
        protected ProviderLifecycleService $lifecycleService
    ) {}

    /**
     * Record registered event to outbox and dispatch domain event via lifecycle service.
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $provider = $passable['provider'];
        $user = $passable['user'];

        // Delegate to canonical lifecycle service
        $this->lifecycleService->recordCreation($provider, [
            'actor_id' => $user?->id,
        ]);

        return $next($passable);
    }
}
