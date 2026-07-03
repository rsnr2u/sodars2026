<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Queries;

use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Repositories\CampaignReadRepositoryInterface;

class GetCampaignDetailsQuery
{
    public function __construct(
        protected CampaignReadRepositoryInterface $readRepo
    ) {}

    public function execute(string $id): Campaign
    {
        return $this->readRepo->findOrFail($id);
    }
}
