<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Inventory\Application\DTOs\UpdateInventoryData;
use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Domain\Entities\InventoryActivity;
use App\Modules\Inventory\Domain\Events\InventoryUpdated;
use App\Modules\Inventory\Domain\Repositories\InventoryReadRepositoryInterface;
use App\Modules\Inventory\Domain\Repositories\InventoryWriteRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class UpdateInventoryAction
{
    public function __construct(
        protected InventoryReadRepositoryInterface $readRepo,
        protected InventoryWriteRepositoryInterface $writeRepo,
        protected OutboxService $outboxService
    ) {}

    /**
     * Update inventory configurations.
     */
    public function execute(string $id, UpdateInventoryData $data): Inventory
    {
        return DB::transaction(function () use ($id, $data) {
            $inventory = $this->readRepo->findOrFail($id);
            $oldValues = $inventory->toArray();

            $this->writeRepo->update($id, $data->toArray());

            $updated = $this->readRepo->findOrFail($id);

            $eventData = [
                'inventory_id' => $id,
                'inventory_code' => $updated->inventory_code,
                'old_values' => $oldValues,
                'new_values' => $updated->toArray(),
            ];

            // 1. Record outbox event
            $this->outboxService->record(
                aggregateType: 'Inventory',
                aggregateId: $id,
                eventName: 'inventory.updated.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );

            // 2. Dispatch domain event
            Event::dispatch(new InventoryUpdated(
                aggregateId: $id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId() ?? (string) \Illuminate\Support\Str::uuid(),
                traceId: TraceContext::traceId() ?? (string) \Illuminate\Support\Str::uuid()
            ));

            // 3. Log timeline activity
            InventoryActivity::create([
                'inventory_id' => $id,
                'performed_by' => auth()->id(),
                'event_name' => 'inventory.updated.v1',
                'action' => 'Updated',
                'old_values' => $oldValues,
                'new_values' => $updated->toArray(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'trace_id' => TraceContext::traceId(),
            ]);

            return $updated;
        });
    }
}
