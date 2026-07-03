<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Inventory\Application\DTOs\InventoryFaceData;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use App\Modules\Inventory\Domain\Entities\InventoryActivity;
use App\Modules\Inventory\Domain\Events\InventoryFaceCreated;
use App\Modules\Inventory\Domain\Repositories\InventoryFaceRepositoryInterface;
use App\Modules\Inventory\Domain\Repositories\InventoryReadRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class AddInventoryFaceAction
{
    public function __construct(
        protected InventoryReadRepositoryInterface $inventoryReadRepo,
        protected InventoryFaceRepositoryInterface $faceRepo,
        protected OutboxService $outboxService
    ) {}

    /**
     * Add face record to inventory.
     */
    public function execute(string $inventoryId, InventoryFaceData $data): InventoryFace
    {
        return DB::transaction(function () use ($inventoryId, $data) {
            $inventory = $this->inventoryReadRepo->findOrFail($inventoryId);

            $face = $this->faceRepo->create([
                'inventory_id' => $inventoryId,
                'face_code' => $data->faceCode,
                'display_name' => $data->displayName,
                'facing_direction' => $data->facingDirection,
                'display_order' => $data->displayOrder,
                'physical_specifications' => $data->physicalSpecifications,
                'is_active' => $data->isActive,
            ]);

            $eventData = [
                'inventory_id' => $inventoryId,
                'face_id' => $face->id,
                'face_code' => $face->face_code,
            ];

            // 1. Record outbox
            $this->outboxService->record(
                aggregateType: 'Inventory',
                aggregateId: $inventoryId,
                eventName: 'inventory.face.created.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );

            // 2. Dispatch event
            Event::dispatch(new InventoryFaceCreated(
                aggregateId: $inventoryId,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId() ?? (string) \Illuminate\Support\Str::uuid(),
                traceId: TraceContext::traceId() ?? (string) \Illuminate\Support\Str::uuid()
            ));

            // 3. Activity timeline log
            InventoryActivity::create([
                'inventory_id' => $inventoryId,
                'performed_by' => auth()->id(),
                'event_name' => 'inventory.face.created.v1',
                'action' => 'FaceAdded',
                'old_values' => null,
                'new_values' => $face->toArray(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'trace_id' => TraceContext::traceId(),
            ]);

            return $face;
        });
    }
}
