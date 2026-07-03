<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Domain\Repositories;

use App\Modules\Campaigns\Domain\Entities\CampaignCreative;

interface CampaignCreativeRepositoryInterface
{
    public function findById(string $id): ?CampaignCreative;

    public function findOrFail(string $id): CampaignCreative;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): CampaignCreative;

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): CampaignCreative;
}
