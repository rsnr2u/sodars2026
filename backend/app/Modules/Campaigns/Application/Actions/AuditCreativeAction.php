<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Actions;

use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Entities\CampaignCreative;
use App\Modules\Campaigns\Domain\Entities\CampaignSchedule;
use App\Modules\Campaigns\Domain\Enums\CreativeStatus;
use App\Modules\Campaigns\Domain\Repositories\CampaignReadRepositoryInterface;
use App\Modules\Campaigns\Domain\Repositories\CampaignCreativeRepositoryInterface;
use App\Modules\Campaigns\Application\Services\CampaignLifecycleService;
use App\Platform\Scheduling\DateRange;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditCreativeAction
{
    public function __construct(
        protected CampaignReadRepositoryInterface $campaignReadRepo,
        protected CampaignCreativeRepositoryInterface $creativeRepo,
        protected CampaignLifecycleService $lifecycleService
    ) {}

    public function execute(string $campaignId, string $creativeId, string $status, ?string $rejectionReason = null): CampaignCreative
    {
        return DB::transaction(function () use ($campaignId, $creativeId, $status, $rejectionReason) {
            /** @var Campaign $campaign */
            $campaign = $this->campaignReadRepo->findOrFail($campaignId);
            $creative = $this->creativeRepo->findOrFail($creativeId);

            $creative = $this->creativeRepo->update($creativeId, [
                'status' => $status,
                'rejection_reason' => $status === CreativeStatus::Rejected->value ? $rejectionReason : null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // If creative is approved, check if campaign can transition to scheduled
            if ($status === CreativeStatus::Approved->value) {
                // Ensure no pending/rejected creatives exist
                $allApproved = true;
                foreach ($campaign->creatives as $c) {
                    $itemStatus = $c->id === $creativeId ? $status : $c->status->value;
                    if ($itemStatus !== CreativeStatus::Approved->value) {
                        $allApproved = false;
                        break;
                    }
                }

                if ($allApproved && $campaign->status->value === 'planning') {
                    // Transition Planning -> Ready -> Approved -> Scheduled
                    $this->lifecycleService->transitionTo($campaign, 'ready');
                    $this->lifecycleService->transitionTo($campaign, 'approved');
                    $this->lifecycleService->transitionTo($campaign, 'scheduled');

                    // Automatically generate mapping grids in campaign_schedule
                    $this->generateScheduleGrid($campaign);
                }
            } else {
                // Creative rejected, campaign remains in or is reverted to planning if needed
                if ($campaign->status->value !== 'planning') {
                    // We don't have back transitions defined, but if it is already planning, no change is needed.
                }
            }

            // Delegate events and outbox logs to the lifecycle service
            $eventData = [
                'creative_id' => $creativeId,
                'campaign_id' => $campaignId,
                'status' => $status,
                'rejection_reason' => $rejectionReason,
            ];

            if ($status === CreativeStatus::Approved->value) {
                $this->lifecycleService->recordProofApproved($campaign, $eventData); // Notify approved
            } else {
                $this->lifecycleService->recordCreativeRemoved($campaign, $eventData); // Log rejection
            }

            return $creative;
        });
    }

    /**
     * Map schedules dynamically per date segment.
     */
    protected function generateScheduleGrid(Campaign $campaign): void
    {
        $range = new DateRange($campaign->start_date, $campaign->end_date);
        $days = $range->toDailySegments();

        foreach ($campaign->inventoryFaces as $face) {
            foreach ($days as $dayRange) {
                // Determine loop slot index (find next available slot index 1 to 6)
                $existingIndex = CampaignSchedule::where('inventory_face_id', $face->id)
                    ->where('date', $dayRange->start->toDateString())
                    ->max('slot_index') ?? 0;

                $nextIndex = $existingIndex + 1;
                if ($nextIndex > 6) {
                    $nextIndex = 1; // Fallback or loop allocation limit
                }

                CampaignSchedule::create([
                    'id' => (string) Str::uuid(),
                    'organization_id' => $campaign->organization_id,
                    'campaign_id' => $campaign->id,
                    'inventory_face_id' => $face->id,
                    'date' => $dayRange->start->toDateString(),
                    'slot_index' => $nextIndex,
                ]);
            }
        }
    }
}
