<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Actions;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\Finance\Domain\Entities\ProviderSettlement;
use App\Modules\Finance\Domain\Entities\ProviderSettlementItem;
use App\Modules\Finance\Domain\Entities\ProviderSettlementActivity;
use App\Modules\Finance\Domain\Services\Settlement\SettlementCalculator;
use App\Modules\Finance\Domain\Events\SettlementGenerated;
use App\Modules\Finance\Domain\Enums\SettlementStatus;
use App\Modules\Finance\Domain\Repositories\InvoiceReadRepositoryInterface;
use App\Modules\Finance\Domain\Repositories\ProviderSettlementRepositoryInterface;
use App\Modules\Bookings\Domain\Repositories\BookingReadRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class GenerateSettlementAction
{
    public function __construct(
        protected BookingReadRepositoryInterface $bookingReadRepo,
        protected InvoiceReadRepositoryInterface $invoiceReadRepo,
        protected ProviderSettlementRepositoryInterface $settlementRepo,
        protected SettlementCalculator $calculator,
        protected OutboxService $outboxService
    ) {}

    public function execute(string $bookingId, string $invoiceId): ProviderSettlement
    {
        return DB::transaction(function () use ($bookingId, $invoiceId) {
            $booking = $this->bookingReadRepo->findOrFail($bookingId);
            $invoice = $this->invoiceReadRepo->findOrFail($invoiceId);

            $booking->load(['items.face.inventory']);
            $provider = $booking->items->first()?->face?->inventory?->provider;

            if (!$provider) {
                throw new \InvalidArgumentException("No provider associated with booking items.");
            }

            $splits = $this->calculator->calculate($booking);
            $year = date('Y');
            $count = ProviderSettlement::whereYear('created_at', (int) $year)->count();
            $settlementNumber = 'SET-' . $year . '-' . str_pad((string)($count + 1), 6, '0', STR_PAD_LEFT);

            $settlement = $this->settlementRepo->create([
                'id' => (string) Str::uuid(),
                'settlement_number' => $settlementNumber,
                'provider_id' => $provider->id,
                'booking_id' => $bookingId,
                'invoice_id' => $invoiceId,
                'total_amount_cents' => $splits['total_amount_cents'],
                'provider_share_cents' => $splits['provider_share_cents'],
                'commission_cents' => $splits['commission_cents'],
                'tax_cents' => $splits['tax_cents'],
                'status' => SettlementStatus::Pending->value,
            ]);

            foreach ($splits['items'] as $item) {
                ProviderSettlementItem::create([
                    'id' => (string) Str::uuid(),
                    'provider_settlement_id' => $settlement->id,
                    'booking_item_id' => $item['booking_item_id'],
                    'amount_cents' => $item['amount_cents'],
                ]);
            }

            $eventData = [
                'settlement_id' => $settlement->id,
                'settlement_number' => $settlement->settlement_number,
                'provider_id' => $provider->id,
                'total_amount_cents' => $splits['total_amount_cents'],
                'provider_share_cents' => $splits['provider_share_cents'],
            ];

            // Outbox
            $this->outboxService->record(
                aggregateType: 'ProviderSettlement',
                aggregateId: $settlement->id,
                eventName: 'settlement.generated.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );

            // Event
            Event::dispatch(new SettlementGenerated(
                aggregateId: $settlement->id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId() ?? (string) Str::uuid(),
                traceId: TraceContext::traceId() ?? (string) Str::uuid()
            ));

            // Activity
            ProviderSettlementActivity::create([
                'id' => (string) Str::uuid(),
                'provider_settlement_id' => $settlement->id,
                'performed_by' => auth()->id() ?? $provider->id,
                'action' => 'Generated',
                'description' => "Settlement ledger statement generated for provider #{$provider->company_name}.",
                'trace_id' => TraceContext::traceId() ?? (string) Str::uuid(),
            ]);

            return $settlement;
        });
    }
}
