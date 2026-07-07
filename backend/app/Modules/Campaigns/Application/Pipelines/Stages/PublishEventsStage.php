<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Pipelines\Stages;

use App\Modules\Campaigns\Application\Services\CampaignLifecycleService;
use Closure;

class PublishEventsStage
{
    public function __construct(
        protected CampaignLifecycleService $lifecycleService
    ) {}

    public function handle(array $passable, Closure $next): mixed
    {
        $campaign = $passable['campaign'];

        // Delegate to canonical CampaignLifecycleService
        $this->lifecycleService->recordCreation($campaign);

        return $next($passable);
    }
}
