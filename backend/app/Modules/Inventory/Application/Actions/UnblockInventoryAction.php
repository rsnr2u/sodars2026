<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Inventory\Domain\Entities\InventoryActivity;
use App\Modules\Inventory\Domain\Events\InventoryAvailabilityChanged;
use App\Modules\Inventory\Domain\Repositories\InventoryFaceRepositoryInterface;
use App\Modules\Inventory\Domain\Repositories\InventoryAvailabilityRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class UnblockInventoryAction
{
    public function __construct(
        protected InventoryFaceRepositoryInterface $faceRepo,
        protected InventoryAvailabilityRepositoryInterface $availabilityRepo,
        protected OutboxService $outboxService
    ) {}

    /**
     * Remove block window.
     */
    public function execute(string $faceId, string $availabilityId): void
    {
        DB::transaction(function () use ($faceId, $availabilityId) {
            $face = $this->faceRepo->findOrFail($faceId);

            $this->availabilityRepo->delete($availabilityId);

            $eventData = [
                'inventory_id' => $face->inventory_id,
                'face_id' => $faceId,
                'availability_id' => $availabilityId,
                'status' => 'operational',
            ];

            // 1. Record outbox
            $this->outboxService->record(
                aggregateType: 'Inventory',
                aggregateId: $face->inventory_id,
                eventName: 'inventory.availability.changed.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );

            // 2. Dispatch event
            Event::dispatch(new InventoryAvailabilityChanged(
                aggregateId: $face->inventory_id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId() ?? (string) \Illuminate\Support\Str::uuid(),
                traceId: TraceContext::traceId() ?? (string) \Illuminate\Support\Str::uuid()
            ));

            // 3. Activity log
            InventoryActivity::create([
                'inventory_id' => $face->inventory_id,
                'performed_by' => auth()->id(),
                'event_name' => 'inventory.availability.changed.v1',
                'action' => 'AvailabilityUnblocked',
                'old_values' => ['availability_id' => $availabilityId],
                'new_values' => null,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'trace_id' => TraceContext::traceId(),
            ]);
        });
    }
}
