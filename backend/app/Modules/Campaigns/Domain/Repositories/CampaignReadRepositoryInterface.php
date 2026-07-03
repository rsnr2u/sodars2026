<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Domain\Repositories;

use App\Modules\Campaigns\Application\DTOs\CampaignFilterData;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CampaignReadRepositoryInterface
{
    public function findById(string $id): ?Campaign;

    public function findOrFail(string $id): Campaign;

    public function findByCode(string $code): ?Campaign;

    /**
     * @return LengthAwarePaginator<Campaign>
     */
    public function paginate(CampaignFilterData $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * @return Collection<int, Campaign>
     */
    public function getActiveCampaigns(): Collection;
}
