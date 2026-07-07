<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Actions;

use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Repositories\CampaignReadRepositoryInterface;
use App\Modules\Campaigns\Application\Services\CampaignLifecycleService;

class ChangeCampaignStatusAction
{
    public function __construct(
        protected CampaignReadRepositoryInterface $readRepo,
        protected CampaignLifecycleService $lifecycleService
    ) {}

    public function execute(string $id, string $targetStatus): Campaign
    {
        /** @var Campaign $campaign */
        $campaign = $this->readRepo->findOrFail($id);

        // Delegate status transition and validation to the lifecycle service
        $this->lifecycleService->transitionTo($campaign, $targetStatus);

        return $campaign;
    }
}
