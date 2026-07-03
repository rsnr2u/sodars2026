<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Domain\Repositories;

use App\Modules\Campaigns\Domain\Entities\CampaignProof;

interface CampaignProofRepositoryInterface
{
    public function findById(string $id): ?CampaignProof;

    public function findOrFail(string $id): CampaignProof;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): CampaignProof;

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): CampaignProof;
}
