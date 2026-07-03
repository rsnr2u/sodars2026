<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Inventory\Application\DTOs\InventoryPricingData;
use App\Modules\Inventory\Domain\Entities\InventoryPricing;
use App\Modules\Inventory\Domain\Entities\InventoryActivity;
use App\Modules\Inventory\Domain\Events\InventoryPricingCreated;
use App\Modules\Inventory\Domain\Repositories\InventoryFaceRepositoryInterface;
use App\Modules\Inventory\Domain\Repositories\InventoryPricingRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class UpdatePricingAction
{
    public function __construct(
        protected InventoryFaceRepositoryInterface $faceRepo,
        protected InventoryPricingRepositoryInterface $pricingRepo,
        protected OutboxService $outboxService
    ) {}

    /**
     * Create a new pricing rate on face.
     */
    public function execute(string $faceId, InventoryPricingData $data): InventoryPricing
    {
        return DB::transaction(function () use ($faceId, $data) {
            $face = $this->faceRepo->findOrFail($faceId);
            
            $pricing = $this->pricingRepo->create([
                'inventory_face_id' => $faceId,
                'pricing_type' => $data->pricingType,
                'rate_cents' => $data->rateCents,
                'currency' => $data->currency,
                'tax_inclusive' => $data->taxInclusive,
                'minimum_booking_days' => $data->minimumBookingDays,
                'effective_from' => $data->effectiveFrom,
                'effective_to' => $data->effectiveTo,
                'priority' => $data->priority,
            ]);

            $eventData = [
                'inventory_id' => $face->inventory_id,
                'face_id' => $faceId,
                'pricing_id' => $pricing->id,
                'rate_cents' => $pricing->rate_cents,
            ];

            // 1. Record outbox
            $this->outboxService->record(
                aggregateType: 'Inventory',
                aggregateId: $face->inventory_id,
                eventName: 'inventory.pricing.created.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );

            // 2. Dispatch event
            Event::dispatch(new InventoryPricingCreated(
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
                'event_name' => 'inventory.pricing.created.v1',
                'action' => 'PricingUpdated',
                'old_values' => null,
                'new_values' => $pricing->toArray(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'trace_id' => TraceContext::traceId(),
            ]);

            return $pricing;
        });
    }
}
