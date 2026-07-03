<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Campaigns\Application\DTOs\UploadCreativeData;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Entities\CampaignCreative;
use App\Modules\Campaigns\Domain\Entities\CampaignActivity;
use App\Modules\Campaigns\Domain\Enums\CampaignStatus;
use App\Modules\Campaigns\Domain\Enums\CreativeStatus;
use App\Modules\Campaigns\Domain\Events\CreativeUploaded;
use App\Modules\Campaigns\Domain\Repositories\CampaignReadRepositoryInterface;
use App\Modules\Campaigns\Domain\Repositories\CampaignWriteRepositoryInterface;
use App\Modules\Campaigns\Domain\Repositories\CampaignCreativeRepositoryInterface;
use App\Platform\Shared\Domain\Entities\MediaLibrary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class UploadCreativeAction
{
    public function __construct(
        protected CampaignReadRepositoryInterface $campaignReadRepo,
        protected CampaignWriteRepositoryInterface $campaignWriteRepo,
        protected CampaignCreativeRepositoryInterface $creativeRepo,
        protected OutboxService $outboxService
    ) {}

    public function execute(string $campaignId, UploadCreativeData $dto): CampaignCreative
    {
        return DB::transaction(function () use ($campaignId, $dto) {
            $campaign = $this->campaignReadRepo->findOrFail($campaignId);

            // Determine version
            $latestVer = $campaign->creatives()->max('version') ?? 0;
            $newVersion = $latestVer + 1;

            // Create creative
            $creative = $this->creativeRepo->create([
                'id' => (string) Str::uuid(),
                'campaign_id' => $campaignId,
                'file_name' => $dto->fileName,
                'file_path' => $dto->filePath,
                'file_type' => $dto->fileType,
                'file_size_bytes' => $dto->fileSizeBytes,
                'version' => $newVersion,
                'status' => CreativeStatus::Pending->value,
            ]);

            // Add polymorphically to Shared MediaLibrary
            MediaLibrary::create([
                'id' => (string) Str::uuid(),
                'file_name' => $dto->fileName,
                'file_path' => $dto->filePath,
                'mime_type' => $this->getMimeType($dto->fileType),
                'file_size_bytes' => $dto->fileSizeBytes ?? 0,
                'mediable_type' => CampaignCreative::class,
                'mediable_id' => $creative->id,
            ]);

            // Shift campaign status to artwork_pending
            if ($campaign->status === CampaignStatus::Draft) {
                $this->campaignWriteRepo->update($campaignId, [
                    'status' => CampaignStatus::ArtworkPending->value,
                ]);
            }

            $eventData = [
                'creative_id' => $creative->id,
                'campaign_id' => $campaignId,
                'file_path' => $dto->filePath,
                'version' => $newVersion,
            ];

            // Outbox
            $this->outboxService->record(
                aggregateType: 'Campaign',
                aggregateId: $campaignId,
                eventName: 'campaign.creative.uploaded.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );

            // Domain Event
            Event::dispatch(new CreativeUploaded(
                aggregateId: $campaignId,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId() ?? (string) Str::uuid(),
                traceId: TraceContext::traceId() ?? (string) Str::uuid()
            ));

            // Activity Log
            CampaignActivity::create([
                'id' => (string) Str::uuid(),
                'campaign_id' => $campaignId,
                'performed_by' => auth()->id(),
                'event_name' => 'campaign.creative.uploaded.v1',
                'action' => 'CreativeUploaded',
                'old_values' => null,
                'new_values' => $creative->toArray(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'trace_id' => TraceContext::traceId() ?? (string) Str::uuid(),
            ]);

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
