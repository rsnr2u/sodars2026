<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Core\Services\StateService;
use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Inventory\Domain\Entities\InventoryActivity;
use App\Modules\Inventory\Domain\Enums\InventoryStatus;
use App\Modules\Inventory\Domain\Events\InventoryApproved;
use App\Modules\Inventory\Domain\Events\InventorySuspended;
use App\Modules\Inventory\Domain\Events\InventoryStatusChanged;
use App\Modules\Inventory\Domain\Repositories\InventoryReadRepositoryInterface;
use App\Modules\Inventory\Domain\Repositories\InventoryWriteRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class ChangeInventoryStatusAction
{
    public function __construct(
        protected InventoryReadRepositoryInterface $readRepo,
        protected InventoryWriteRepositoryInterface $writeRepo,
        protected StateService $stateService,
        protected OutboxService $outboxService
    ) {}

    /**
     * Transition inventory status.
     */
    public function execute(string $id, string $newStatus): Inventory
    {
        return DB::transaction(function () use ($id, $newStatus) {
            $inventory = $this->readRepo->findOrFail($id);
            $currentStatus = $inventory->status instanceof InventoryStatus ? $inventory->status->value : (string) $inventory->status;

            // Validate status transitions using allowed transitions
            $this->stateService->validateTransition(
                $currentStatus,
                $newStatus,
                InventoryStatus::allowedTransitions()
            );

            $this->writeRepo->update($id, ['status' => $newStatus]);
            $updated = $this->readRepo->findOrFail($id);

            $eventData = [
                'inventory_id' => $id,
                'inventory_code' => $updated->inventory_code,
                'organization_id' => $updated->organization_id,
                'from_status' => $currentStatus,
                'to_status' => $newStatus,
                'status' => $newStatus,
            ];

            $eventName = $newStatus === 'approved' 
                ? 'inventory.approved.v1' 
                : ($newStatus === 'suspended' ? 'inventory.suspended.v1' : 'inventory.status.changed.v1');

            // 1. Record outbox event
            $this->outboxService->record(
                aggregateType: 'Inventory',
                aggregateId: $id,
                eventName: $eventName,
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );

            // 2. Dispatch domain events
            Event::dispatch(new InventoryStatusChanged(
                aggregateId: $id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId() ?? (string) \Illuminate\Support\Str::uuid(),
                traceId: TraceContext::traceId() ?? (string) \Illuminate\Support\Str::uuid()
            ));

            if ($newStatus === 'approved') {
                Event::dispatch(new InventoryApproved(
                    aggregateId: $id,
                    aggregateVersion: 1,
                    data: $eventData,
                    occurredAt: now()->toIso8601String(),
                    correlationId: TraceContext::correlationId() ?? (string) \Illuminate\Support\Str::uuid(),
                    traceId: TraceContext::traceId() ?? (string) \Illuminate\Support\Str::uuid()
                ));
            } elseif ($newStatus === 'suspended') {
                Event::dispatch(new InventorySuspended(
                    aggregateId: $id,
                    aggregateVersion: 1,
                    data: $eventData,
                    occurredAt: now()->toIso8601String(),
                    correlationId: TraceContext::correlationId() ?? (string) \Illuminate\Support\Str::uuid(),
                    traceId: TraceContext::traceId() ?? (string) \Illuminate\Support\Str::uuid()
                ));
            }

            // 3. Log timeline activity
            InventoryActivity::create([
                'inventory_id' => $id,
                'performed_by' => auth()->id(),
                'event_name' => $eventName,
                'action' => 'StatusChanged',
                'old_values' => ['status' => $currentStatus],
                'new_values' => ['status' => $newStatus],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'trace_id' => TraceContext::traceId(),
            ]);

            return $updated;
        });
    }
}
