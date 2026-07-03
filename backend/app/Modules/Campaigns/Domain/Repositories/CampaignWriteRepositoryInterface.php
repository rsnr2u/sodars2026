<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Domain\Repositories;

use App\Modules\Campaigns\Domain\Entities\Campaign;

interface CampaignWriteRepositoryInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Campaign;

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): Campaign;

    public function delete(string $id): bool;

    public function associateFace(string $campaignId, string $faceId): void;

    public function dissociateFace(string $campaignId, string $faceId): void;
}
