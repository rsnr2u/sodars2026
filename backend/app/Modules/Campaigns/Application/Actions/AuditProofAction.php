<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Entities\CampaignProof;
use App\Modules\Campaigns\Domain\Entities\CampaignActivity;
use App\Modules\Campaigns\Domain\Enums\ProofStatus;
use App\Modules\Campaigns\Domain\Events\ProofAudited;
use App\Modules\Campaigns\Domain\Repositories\CampaignReadRepositoryInterface;
use App\Modules\Campaigns\Domain\Repositories\CampaignProofRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class AuditProofAction
{
    public function __construct(
        protected CampaignReadRepositoryInterface $campaignReadRepo,
        protected CampaignProofRepositoryInterface $proofRepo,
        protected OutboxService $outboxService
    ) {}

    public function execute(string $campaignId, string $proofId, string $status): CampaignProof
    {
        return DB::transaction(function () use ($campaignId, $proofId, $status) {
            $campaign = $this->campaignReadRepo->findOrFail($campaignId);
            $proof = $this->proofRepo->findOrFail($proofId);

            $proof = $this->proofRepo->update($proofId, [
                'status' => $status,
                'verified_by' => auth()->id(),
                'verified_at' => $status === ProofStatus::Verified->value ? now() : null,
            ]);

            $eventData = [
                'proof_id' => $proofId,
                'campaign_id' => $campaignId,
                'status' => $status,
            ];

            // Outbox
            $this->outboxService->record(
                aggregateType: 'Campaign',
                aggregateId: $campaignId,
                eventName: 'campaign.proof.audited.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );

            // Domain Event
            Event::dispatch(new ProofAudited(
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
                'event_name' => 'campaign.proof.audited.v1',
                'action' => 'ProofAudited',
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
