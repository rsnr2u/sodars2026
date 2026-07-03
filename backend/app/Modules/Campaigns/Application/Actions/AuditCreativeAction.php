<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Entities\CampaignCreative;
use App\Modules\Campaigns\Domain\Entities\CampaignActivity;
use App\Modules\Campaigns\Domain\Entities\CampaignSchedule;
use App\Modules\Campaigns\Domain\Enums\CampaignStatus;
use App\Modules\Campaigns\Domain\Enums\CreativeStatus;
use App\Modules\Campaigns\Domain\Events\CreativeAudited;
use App\Modules\Campaigns\Domain\Repositories\CampaignReadRepositoryInterface;
use App\Modules\Campaigns\Domain\Repositories\CampaignWriteRepositoryInterface;
use App\Modules\Campaigns\Domain\Repositories\CampaignCreativeRepositoryInterface;
use App\Platform\Scheduling\DateRange;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class AuditCreativeAction
{
    public function __construct(
        protected CampaignReadRepositoryInterface $campaignReadRepo,
        protected CampaignWriteRepositoryInterface $campaignWriteRepo,
        protected CampaignCreativeRepositoryInterface $creativeRepo,
        protected OutboxService $outboxService
    ) {}

    public function execute(string $campaignId, string $creativeId, string $status, ?string $rejectionReason = null): CampaignCreative
    {
        return DB::transaction(function () use ($campaignId, $creativeId, $status, $rejectionReason) {
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

                if ($allApproved && $campaign->status === CampaignStatus::ArtworkPending) {
                    $this->campaignWriteRepo->update($campaignId, [
                        'status' => CampaignStatus::Scheduled->value,
                    ]);

                    // Automatically generate mapping grids in campaign_schedule
                    $this->generateScheduleGrid($campaign);
                }
            } else {
                // Creative rejected, revert campaign status to artwork_pending
                $this->campaignWriteRepo->update($campaignId, [
                    'status' => CampaignStatus::ArtworkPending->value,
                ]);
            }

            $eventData = [
                'creative_id' => $creativeId,
                'campaign_id' => $campaignId,
                'status' => $status,
                'rejection_reason' => $rejectionReason,
            ];

            // Outbox
            $this->outboxService->record(
                aggregateType: 'Campaign',
                aggregateId: $campaignId,
                eventName: 'campaign.creative.audited.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );

            // Domain Event
            Event::dispatch(new CreativeAudited(
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
                'event_name' => 'campaign.creative.audited.v1',
                'action' => 'CreativeAudited',
                'old_values' => null,
                'new_values' => $creative->toArray(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'trace_id' => TraceContext::traceId() ?? (string) Str::uuid(),
            ]);

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
                    'campaign_id' => $campaign->id,
                    'inventory_face_id' => $face->id,
                    'date' => $dayRange->start->toDateString(),
                    'slot_index' => $nextIndex,
                ]);
            }
        }
    }
}
