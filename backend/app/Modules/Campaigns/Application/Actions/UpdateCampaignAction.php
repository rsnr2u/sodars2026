<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Campaigns\Application\DTOs\UpdateCampaignData;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Entities\CampaignActivity;
use App\Modules\Campaigns\Domain\Events\CampaignUpdated;
use App\Modules\Campaigns\Domain\Repositories\CampaignReadRepositoryInterface;
use App\Modules\Campaigns\Domain\Repositories\CampaignWriteRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class UpdateCampaignAction
{
    public function __construct(
        protected CampaignReadRepositoryInterface $readRepo,
        protected CampaignWriteRepositoryInterface $writeRepo,
        protected OutboxService $outboxService
    ) {}

    public function execute(string $id, UpdateCampaignData $dto): Campaign
    {
        return DB::transaction(function () use ($id, $dto) {
            $campaign = $this->readRepo->findOrFail($id);
            $oldValues = $campaign->toArray();

            // filter non-null update fields
            $updateData = array_filter([
                'name' => $dto->name,
                'description' => $dto->description,
                'start_date' => $dto->startDate,
                'end_date' => $dto->endDate,
                'objectives' => $dto->objectives,
                'budget_cents' => $dto->budgetCents,
                'booking_id' => $dto->bookingId,
            ], fn($val) => $val !== null);

            $updated = $this->writeRepo->update($id, $updateData);

            $eventData = [
                'campaign_id' => $id,
                'updates' => $updateData,
            ];

            // outbox
            $this->outboxService->record(
                aggregateType: 'Campaign',
                aggregateId: $id,
                eventName: 'campaign.updated.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );

            // domain event
            Event::dispatch(new CampaignUpdated(
                aggregateId: $id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId() ?? (string) Str::uuid(),
                traceId: TraceContext::traceId() ?? (string) Str::uuid()
            ));

            // audit activity
            CampaignActivity::create([
                'id' => (string) Str::uuid(),
                'campaign_id' => $id,
                'performed_by' => auth()->id(),
                'event_name' => 'campaign.updated.v1',
                'action' => 'Updated',
                'old_values' => $oldValues,
                'new_values' => $updated->toArray(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'trace_id' => TraceContext::traceId() ?? (string) Str::uuid(),
            ]);

            return $updated;
        });
    }
}
