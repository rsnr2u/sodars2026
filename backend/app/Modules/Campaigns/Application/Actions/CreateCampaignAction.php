<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Actions;

use App\Modules\Campaigns\Application\DTOs\CreateCampaignData;
use App\Modules\Campaigns\Application\Pipelines\CreateCampaignPipeline;
use App\Modules\Campaigns\Domain\Entities\Campaign;

class CreateCampaignAction
{
    public function __construct(
        protected CreateCampaignPipeline $pipeline
    ) {}

    public function execute(CreateCampaignData $dto): Campaign
    {
        return $this->pipeline->execute($dto);
    }
}
