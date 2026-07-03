<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Infrastructure\Repositories;

use App\Modules\Campaigns\Domain\Entities\CampaignCreative;
use App\Modules\Campaigns\Domain\Repositories\CampaignCreativeRepositoryInterface;

class CampaignCreativeRepository implements CampaignCreativeRepositoryInterface
{
    public function findById(string $id): ?CampaignCreative
    {
        return CampaignCreative::find($id);
    }

    public function findOrFail(string $id): CampaignCreative
    {
        return CampaignCreative::findOrFail($id);
    }

    public function create(array $data): CampaignCreative
    {
        return CampaignCreative::create($data);
    }

    public function update(string $id, array $data): CampaignCreative
    {
        $creative = CampaignCreative::findOrFail($id);
        $creative->update($data);
        return $creative;
    }
}
