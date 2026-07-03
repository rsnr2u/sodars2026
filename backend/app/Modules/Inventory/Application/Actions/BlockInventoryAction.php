<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Inventory\Domain\Entities\InventoryAvailability;
use App\Modules\Inventory\Domain\Entities\InventoryActivity;
use App\Modules\Inventory\Domain\Events\InventoryAvailabilityChanged;
use App\Modules\Inventory\Domain\Repositories\InventoryFaceRepositoryInterface;
use App\Modules\Inventory\Domain\Repositories\InventoryAvailabilityRepositoryInterface;
use App\Modules\Inventory\Domain\Specifications\AvailabilityOverlapSpecification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class BlockInventoryAction
{
    public function __construct(
        protected InventoryFaceRepositoryInterface $faceRepo,
        protected InventoryAvailabilityRepositoryInterface $availabilityRepo,
        protected OutboxService $outboxService
    ) {}

    /**
     * Set a manual block/maintenance window.
     */
    public function execute(string $faceId, Carbon $startAt, Carbon $endAt, string $status, string $reason, ?string $remarks = null): InventoryAvailability
    {
        return DB::transaction(function () use ($faceId, $startAt, $endAt, $status, $reason, $remarks) {
            $face = $this->faceRepo->findOrFail($faceId);

            // 1. Validate overlap for blocks using AvailabilityOverlapSpecification
            $overlapSpec = new AvailabilityOverlapSpecification($faceId, $startAt, $endAt);
            $overlaps = $this->availabilityRepo->findByFaceId($faceId)->filter(function($avail) use ($overlapSpec) {
                $statusVal = $avail->availability_status instanceof \UnitEnum 
                    ? $avail->availability_status->value 
                    : (string) $avail->availability_status;
                
                return $overlapSpec->isSatisfiedBy($avail) && $statusVal !== 'operational';
            });

            if ($overlaps->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'start_at' => ['Proposed block window overlaps with an existing reservation or block.'],
                ]);
            }

            // 2. Create block
            $availability = $this->availabilityRepo->create([
                'inventory_face_id' => $faceId,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'availability_status' => $status,
                'reason' => $reason,
                'source' => 'Manual',
                'remarks' => $remarks,
            ]);

            $eventData = [
                'inventory_id' => $face->inventory_id,
                'face_id' => $faceId,
                'availability_id' => $availability->id,
                'status' => $status,
                'start_at' => $startAt->toIso8601String(),
                'end_at' => $endAt->toIso8601String(),
            ];

            // 3. Record outbox
            $this->outboxService->record(
                aggregateType: 'Inventory',
                aggregateId: $face->inventory_id,
                eventName: 'inventory.availability.changed.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );

            // 4. Dispatch event
            Event::dispatch(new InventoryAvailabilityChanged(
                aggregateId: $face->inventory_id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId() ?? (string) \Illuminate\Support\Str::uuid(),
                traceId: TraceContext::traceId() ?? (string) \Illuminate\Support\Str::uuid()
            ));

            // 5. Activity log
            InventoryActivity::create([
                'inventory_id' => $face->inventory_id,
                'performed_by' => auth()->id(),
                'event_name' => 'inventory.availability.changed.v1',
                'action' => 'AvailabilityBlocked',
                'old_values' => null,
                'new_values' => $availability->toArray(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'trace_id' => TraceContext::traceId(),
            ]);

            return $availability;
        });
    }
}
