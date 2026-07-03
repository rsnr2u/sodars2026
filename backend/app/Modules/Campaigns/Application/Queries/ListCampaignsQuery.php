<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Queries;

use App\Modules\Campaigns\Application\DTOs\CampaignFilterData;
use App\Modules\Campaigns\Domain\Repositories\CampaignReadRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ListCampaignsQuery
{
    public function __construct(
        protected CampaignReadRepositoryInterface $readRepo
    ) {}

    /**
     * @return LengthAwarePaginator<\App\Modules\Campaigns\Domain\Entities\Campaign>
     */
    public function execute(CampaignFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->readRepo->paginate($filters, $perPage);
    }
}
