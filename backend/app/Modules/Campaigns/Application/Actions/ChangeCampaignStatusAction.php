<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Entities\CampaignActivity;
use App\Modules\Campaigns\Domain\Enums\CampaignStatus;
use App\Modules\Campaigns\Domain\Events\CampaignStatusChanged;
use App\Modules\Campaigns\Domain\Repositories\CampaignReadRepositoryInterface;
use App\Modules\Campaigns\Domain\Repositories\CampaignWriteRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class ChangeCampaignStatusAction
{
    public function __construct(
        protected CampaignReadRepositoryInterface $readRepo,
        protected CampaignWriteRepositoryInterface $writeRepo,
        protected OutboxService $outboxService
    ) {}

    public function execute(string $id, string $targetStatus): Campaign
    {
        return DB::transaction(function () use ($id, $targetStatus) {
            $campaign = $this->readRepo->findOrFail($id);
            $oldStatus = $campaign->status;

            if (!$oldStatus->canTransitionTo($targetStatus)) {
                throw ValidationException::withMessages([
                    'status' => ["Cannot transition campaign from '{$oldStatus->value}' to '{$targetStatus}'."],
                ]);
            }

            $updated = $this->writeRepo->update($id, [
                'status' => $targetStatus,
            ]);

            $eventData = [
                'campaign_id' => $id,
                'old_status' => $oldStatus->value,
                'new_status' => $targetStatus,
            ];

            // Outbox
            $this->outboxService->record(
                aggregateType: 'Campaign',
                aggregateId: $id,
                eventName: 'campaign.status_changed.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );

            // Domain Event
            Event::dispatch(new CampaignStatusChanged(
                aggregateId: $id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId() ?? (string) Str::uuid(),
                traceId: TraceContext::traceId() ?? (string) Str::uuid()
            ));

            // Audit
            CampaignActivity::create([
                'id' => (string) Str::uuid(),
                'campaign_id' => $id,
                'performed_by' => auth()->id(),
                'event_name' => 'campaign.status_changed.v1',
                'action' => 'StatusTransitioned',
                'old_values' => ['status' => $oldStatus->value],
                'new_values' => ['status' => $targetStatus],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'trace_id' => TraceContext::traceId() ?? (string) Str::uuid(),
            ]);

            return $updated;
        });
    }
}
