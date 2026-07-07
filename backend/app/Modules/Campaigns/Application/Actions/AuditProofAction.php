<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Actions;

use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Entities\CampaignProof;
use App\Modules\Campaigns\Domain\Enums\ProofStatus;
use App\Modules\Campaigns\Domain\Repositories\CampaignReadRepositoryInterface;
use App\Modules\Campaigns\Domain\Repositories\CampaignProofRepositoryInterface;
use App\Modules\Campaigns\Application\Services\CampaignLifecycleService;
use Illuminate\Support\Facades\DB;

class AuditProofAction
{
    public function __construct(
        protected CampaignReadRepositoryInterface $campaignReadRepo,
        protected CampaignProofRepositoryInterface $proofRepo,
        protected CampaignLifecycleService $lifecycleService
    ) {}

    public function execute(string $campaignId, string $proofId, string $status): CampaignProof
    {
        return DB::transaction(function () use ($campaignId, $proofId, $status) {
            /** @var Campaign $campaign */
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

            // Delegate to CampaignLifecycleService
            if ($status === ProofStatus::Verified->value) {
                $this->lifecycleService->recordProofApproved($campaign, $eventData);
            } else {
                $this->lifecycleService->recordCreativeRemoved($campaign, $eventData); // Rejection
            }

            return $proof;
        });
    }
}
