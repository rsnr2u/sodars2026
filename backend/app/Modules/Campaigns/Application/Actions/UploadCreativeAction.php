<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Actions;

use App\Modules\Campaigns\Application\DTOs\UploadCreativeData;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Entities\CampaignCreative;
use App\Modules\Campaigns\Domain\Enums\CreativeStatus;
use App\Modules\Campaigns\Domain\Repositories\CampaignReadRepositoryInterface;
use App\Modules\Campaigns\Domain\Repositories\CampaignCreativeRepositoryInterface;
use App\Modules\Campaigns\Application\Services\CampaignLifecycleService;
use App\Platform\DAM\Domain\Entities\Asset;
use App\Platform\DAM\Domain\Entities\AssetVersion;
use App\Platform\DAM\Domain\Entities\StoredFile;
use App\Platform\DAM\Domain\Enums\AssetStatus;
use App\Platform\DAM\Domain\Enums\AssetType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UploadCreativeAction
{
    public function __construct(
        protected CampaignReadRepositoryInterface $campaignReadRepo,
        protected CampaignCreativeRepositoryInterface $creativeRepo,
        protected CampaignLifecycleService $lifecycleService
    ) {}

    public function execute(string $campaignId, UploadCreativeData $dto): CampaignCreative
    {
        return DB::transaction(function () use ($campaignId, $dto) {
            /** @var Campaign $campaign */
            $campaign = $this->campaignReadRepo->findOrFail($campaignId);
            $orgId = $campaign->organization_id;

            // Determine version
            $latestVer = $campaign->creatives()->max('version') ?? 0;
            $newVersion = $latestVer + 1;

            // 1. Create StoredFile (physical representation)
            $storedFile = StoredFile::create([
                'id' => (string) Str::uuid(),
                'organization_id' => $orgId,
                'storage_provider' => 'local',
                'disk' => 'public',
                'path' => $dto->filePath,
                'checksum_sha256' => hash('sha256', $dto->filePath),
                'checksum_md5' => md5($dto->filePath),
                'mime_type' => $this->getMimeType($dto->fileType),
                'file_size' => $dto->fileSizeBytes ?? 1024,
            ]);

            $assetType = match (strtolower($dto->fileType)) {
                'jpg', 'jpeg', 'png' => AssetType::IMAGE,
                'mp4' => AssetType::VIDEO,
                'pdf' => AssetType::DOCUMENT,
                'zip' => AssetType::ARCHIVE,
                default => AssetType::OTHER,
            };

            // 2. Create Asset (logical representation)
            $asset = Asset::create([
                'id' => (string) Str::uuid(),
                'organization_id' => $orgId,
                'title' => $dto->fileName,
                'asset_type' => $assetType,
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

            // 4. Create Creative pointing to the asset
            /** @var CampaignCreative $creative */
            $creative = $this->creativeRepo->create([
                'id' => (string) Str::uuid(),
                'organization_id' => $orgId,
                'campaign_id' => $campaignId,
                'asset_id' => $asset->id,
                'file_name' => $dto->fileName,
                'file_path' => $dto->filePath,
                'file_type' => $dto->fileType,
                'file_size_bytes' => $dto->fileSizeBytes,
                'version' => $newVersion,
                'status' => CreativeStatus::Pending->value,
            ]);

            // Shift campaign status to planning/artwork_pending if in draft
            if ($campaign->status->value === 'draft') {
                $this->lifecycleService->transitionTo($campaign, 'planning');
            }

            $eventData = [
                'creative_id' => $creative->id,
                'campaign_id' => $campaignId,
                'asset_id' => $asset->id,
                'file_path' => $dto->filePath,
                'version' => $newVersion,
            ];

            // 5. Delegate to canonical CampaignLifecycleService
            $this->lifecycleService->recordCreativeAdded($campaign, $eventData);

            return $creative;
        });
    }

    private function getMimeType(string $ext): string
    {
        return match (strtolower($ext)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
            'zip' => 'application/zip',
            'mp4' => 'video/mp4',
            default => 'application/octet-stream',
        };
    }
}
