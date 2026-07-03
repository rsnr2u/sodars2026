<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Campaigns\Application\DTOs\UploadProofData;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Entities\CampaignProof;
use App\Modules\Campaigns\Domain\Entities\CampaignActivity;
use App\Modules\Campaigns\Domain\Enums\ProofStatus;
use App\Modules\Campaigns\Domain\Events\ProofUploaded;
use App\Modules\Campaigns\Domain\Repositories\CampaignReadRepositoryInterface;
use App\Modules\Campaigns\Domain\Repositories\CampaignProofRepositoryInterface;
use App\Platform\Shared\Domain\Entities\MediaLibrary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class UploadProofAction
{
    public function __construct(
        protected CampaignReadRepositoryInterface $campaignReadRepo,
        protected CampaignProofRepositoryInterface $proofRepo,
        protected OutboxService $outboxService
    ) {}

    public function execute(string $campaignId, UploadProofData $dto): CampaignProof
    {
        return DB::transaction(function () use ($campaignId, $dto) {
            $campaign = $this->campaignReadRepo->findOrFail($campaignId);

            // Create proof record
            $proof = $this->proofRepo->create([
                'id' => (string) Str::uuid(),
                'campaign_id' => $campaignId,
                'inventory_face_id' => $dto->inventoryFaceId,
                'file_path' => $dto->filePath,
                'notes' => $dto->notes,
                'uploaded_by' => auth()->id() ?? $campaign->customer_id,
                'status' => ProofStatus::Pending->value,
            ]);

            // Register in Shared MediaLibrary
            MediaLibrary::create([
                'id' => (string) Str::uuid(),
                'file_name' => basename($dto->filePath),
                'file_path' => $dto->filePath,
                'mime_type' => 'image/jpeg', // default photo mime-type
                'file_size_bytes' => 0,
                'mediable_type' => CampaignProof::class,
                'mediable_id' => $proof->id,
            ]);

            $eventData = [
                'proof_id' => $proof->id,
                'campaign_id' => $campaignId,
                'file_path' => $dto->filePath,
            ];

            // Outbox
            $this->outboxService->record(
                aggregateType: 'Campaign',
                aggregateId: $campaignId,
                eventName: 'campaign.proof.uploaded.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );

            // Domain Event
            Event::dispatch(new ProofUploaded(
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
                'event_name' => 'campaign.proof.uploaded.v1',
                'action' => 'ProofUploaded',
                'old_values' => null,
                'new_values' => $proof->toArray(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'trace_id' => TraceContext::traceId() ?? (string) Str::uuid(),
            ]);

            return $proof;
        });
    }
}
