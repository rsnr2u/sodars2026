<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Actions;

use App\Modules\Campaigns\Application\DTOs\UploadProofData;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Entities\CampaignProof;
use App\Modules\Campaigns\Domain\Enums\ProofStatus;
use App\Modules\Campaigns\Domain\Repositories\CampaignReadRepositoryInterface;
use App\Modules\Campaigns\Domain\Repositories\CampaignProofRepositoryInterface;
use App\Modules\Campaigns\Application\Services\CampaignLifecycleService;
use App\Platform\DAM\Domain\Entities\Asset;
use App\Platform\DAM\Domain\Entities\AssetVersion;
use App\Platform\DAM\Domain\Entities\StoredFile;
use App\Platform\DAM\Domain\Enums\AssetStatus;
use App\Platform\DAM\Domain\Enums\AssetType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UploadProofAction
{
    public function __construct(
        protected CampaignReadRepositoryInterface $campaignReadRepo,
        protected CampaignProofRepositoryInterface $proofRepo,
        protected CampaignLifecycleService $lifecycleService
    ) {}

    public function execute(string $campaignId, UploadProofData $dto): CampaignProof
    {
        return DB::transaction(function () use ($campaignId, $dto) {
            /** @var Campaign $campaign */
            $campaign = $this->campaignReadRepo->findOrFail($campaignId);
            $orgId = $campaign->organization_id;

            // 1. Create StoredFile (physical representation)
            $storedFile = StoredFile::create([
                'id' => (string) Str::uuid(),
                'organization_id' => $orgId,
                'storage_provider' => 'local',
                'disk' => 'public',
                'path' => $dto->filePath,
                'checksum_sha256' => hash('sha256', $dto->filePath),
                'checksum_md5' => md5($dto->filePath),
                'mime_type' => 'image/jpeg',
                'file_size' => 1024,
            ]);

            // 2. Create Asset (logical representation)
            $asset = Asset::create([
                'id' => (string) Str::uuid(),
                'organization_id' => $orgId,
                'title' => basename($dto->filePath),
                'asset_type' => AssetType::IMAGE,
                'status' => AssetStatus::READY,
            ]);

            // 3. Create Version
            $version = AssetVersion::create([
                'id' => (string) Str::uuid(),
                'organization_id' => $orgId,
                'asset_id' => $asset->id,
                'file_id' => $storedFile->id,
                'version_number' => 1,
            ]);
            $asset->update(['current_version_id' => $version->id]);

            // 4. Create proof record
            /** @var CampaignProof $proof */
            $proof = $this->proofRepo->create([
                'id' => (string) Str::uuid(),
                'organization_id' => $orgId,
                'campaign_id' => $campaignId,
                'inventory_face_id' => $dto->inventoryFaceId,
                'asset_id' => $asset->id,
                'file_path' => $dto->filePath,
                'notes' => $dto->notes,
                'uploaded_by' => auth()->id() ?? $campaign->customer_id,
                'status' => ProofStatus::Pending->value,
            ]);

            $eventData = [
                'proof_id' => $proof->id,
                'campaign_id' => $campaignId,
                'asset_id' => $asset->id,
                'file_path' => $dto->filePath,
            ];

            // 5. Delegate to canonical CampaignLifecycleService
            $this->lifecycleService->recordProofUploaded($campaign, $eventData);

            return $proof;
        });
    }
}
