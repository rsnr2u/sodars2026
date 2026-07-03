<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Infrastructure\Repositories;

use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Repositories\CampaignWriteRepositoryInterface;

class CampaignWriteRepository implements CampaignWriteRepositoryInterface
{
    public function create(array $data): Campaign
    {
        return Campaign::create($data);
    }

    public function update(string $id, array $data): Campaign
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->update($data);
        return $campaign;
    }

    public function delete(string $id): bool
    {
        $campaign = Campaign::findOrFail($id);
        return $campaign->delete();
    }

    public function associateFace(string $campaignId, string $faceId): void
    {
        $campaign = Campaign::findOrFail($campaignId);
        $campaign->inventoryFaces()->syncWithoutDetaching([$faceId => [
            'id' => (string) \Illuminate\Support\Str::uuid(),
        ]]);
    }

    public function dissociateFace(string $campaignId, string $faceId): void
    {
        $campaign = Campaign::findOrFail($campaignId);
        $campaign->inventoryFaces()->detach($faceId);
    }
}
