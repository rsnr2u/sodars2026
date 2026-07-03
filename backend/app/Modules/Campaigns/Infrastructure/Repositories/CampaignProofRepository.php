<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Infrastructure\Repositories;

use App\Modules\Campaigns\Domain\Entities\CampaignProof;
use App\Modules\Campaigns\Domain\Repositories\CampaignProofRepositoryInterface;

class CampaignProofRepository implements CampaignProofRepositoryInterface
{
    public function findById(string $id): ?CampaignProof
    {
        return CampaignProof::find($id);
    }

    public function findOrFail(string $id): CampaignProof
    {
        return CampaignProof::findOrFail($id);
    }

    public function create(array $data): CampaignProof
    {
        return CampaignProof::create($data);
    }

    public function update(string $id, array $data): CampaignProof
    {
        $proof = CampaignProof::findOrFail($id);
        $proof->update($data);
        return $proof;
    }
}
