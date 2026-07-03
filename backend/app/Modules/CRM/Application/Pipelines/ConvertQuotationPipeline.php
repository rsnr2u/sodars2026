<?php

declare(strict_types=1);

namespace App\Modules\CRM\Application\Pipelines;

use App\Core\Context\TraceContext;
use App\Core\Services\OutboxService;
use App\Modules\CRM\Domain\Entities\Quotation;
use App\Modules\CRM\Domain\Enums\QuotationStatus;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Entities\BookingItem;
use App\Modules\Bookings\Domain\Entities\BookingActivity;
use App\Modules\Bookings\Domain\Events\BookingCreated;
use App\Modules\Inventory\Domain\Entities\InventoryAvailability;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use App\Modules\Inventory\Domain\Specifications\AvailabilityOverlapSpecification;
use App\Platform\Identifiers\BookingNumberGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ConvertQuotationPipeline
{
    public function __construct(
        protected BookingNumberGenerator $numberGenerator,
        protected OutboxService $outboxService
    ) {}

    public function execute(Quotation $quotation, string $branchId, string $customerId): Booking
    {
        return DB::transaction(function () use ($quotation, $branchId, $customerId) {
            // 1. Validate Quote
            if ($quotation->status !== QuotationStatus::ACCEPTED) {
                throw ValidationException::withMessages([
                    'quotation' => ['Only accepted quotations can be converted to bookings.'],
                ]);
            }

            $version = $quotation->activeVersion;
            if (!$version) {
                throw ValidationException::withMessages([
                    'quotation' => ['Quotation does not have an active version.'],
                ]);
            }

            if ($version->valid_until->isPast()) {
                throw ValidationException::withMessages([
                    'quotation' => ['The quotation version has expired.'],
                ]);
            }

            $items = $version->items;
            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'quotation' => ['Quotation must have at least one inventory face item.'],
                ]);
            }

            // 2. Validate availability
            foreach ($items as $item) {
                $face = InventoryFace::with(['availabilities'])->find($item->inventory_face_id);
                if (!$face || !$face->is_active) {
                    throw ValidationException::withMessages([
                        'inventory_face_id' => ["Inventory face '{$item->inventory_face_id}' is inactive or invalid."],
                    ]);
                }

                $overlapSpec = new AvailabilityOverlapSpecification($item->inventory_face_id, $item->start_date, $item->end_date);
                $overlaps = $face->availabilities->filter(function ($avail) use ($overlapSpec) {
                    $statusVal = $avail->availability_status instanceof \UnitEnum
                        ? $avail->availability_status->value
                        : (string) $avail->availability_status;

                    return $overlapSpec->isSatisfiedBy($avail) && $statusVal !== 'operational';
                });

                if ($overlaps->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'availability' => ["Selected dates for face '{$face->face_code}' overlap with an existing block."],
                    ]);
                }
            }

            // Determine overall flight dates
            $minDate = null;
            $maxDate = null;
            foreach ($items as $item) {
                if (!$minDate || $item->start_date->lessThan($minDate)) {
                    $minDate = $item->start_date;
                }
                if (!$maxDate || $item->end_date->greaterThan($maxDate)) {
                    $maxDate = $item->end_date;
                }
            }

            // 3. Freeze Snapshots
            $quotationSnapshot = [
                'quotation_id' => $quotation->id,
                'quotation_number' => $quotation->quotation_number,
                'version_number' => $version->version_number,
                'items' => $items->map(fn($it) => [
                    'inventory_face_id' => $it->inventory_face_id,
                    'start_date' => $it->start_date->toDateString(),
                    'end_date' => $it->end_date->toDateString(),
                    'daily_frequency' => $it->daily_frequency,
                    'price_cents' => $it->price_cents,
                ])->toArray(),
            ];

            $bookingSnapshot = [
                'quote_reference' => $quotation->quotation_number,
                'converted_at' => now()->toIso8601String(),
                'version' => $version->version_number,
            ];

            // 4. Create Booking
            $code = $this->numberGenerator->generate();

            // Calculate values
            $subtotal = $version->subtotal_cents;
            $discount = $version->discount_cents;
            $tax = $version->tax_cents;
            $grandTotal = $version->grand_total_cents;
            $currency = $version->currency;

            // Compute commissions & shares
            $commissionCents = (int) round($subtotal * 0.15); // Default 15% platform commission
            $platformFeeCents = 0; // Default
            $providerShareCents = $subtotal - $commissionCents;

            $booking = Booking::create([
                'id' => (string) Str::uuid(),
                'booking_code' => $code,
                'customer_id' => $customerId,
                'branch_id' => $branchId,
                'start_date' => $minDate,
                'end_date' => $maxDate,
                'subtotal_cents' => $subtotal,
                'discount_cents' => $discount,
                'tax_cents' => $tax,
                'platform_fee_cents' => $platformFeeCents,
                'provider_share_cents' => $providerShareCents,
                'commission_cents' => $commissionCents,
                'grand_total_cents' => $grandTotal,
                'currency' => $currency,
                'status' => 'approved', // Automatically approve converted quotes
                'booking_snapshot' => $bookingSnapshot,
                'quotation_snapshot' => $quotationSnapshot,
                'quotation_id' => $quotation->id,
                'quotation_version_id' => $version->id,
                'converted_from_quotation_at' => now(),
            ]);

            // Save booking items
            $createdItems = [];
            foreach ($items as $item) {
                $createdItems[] = BookingItem::create([
                    'id' => (string) Str::uuid(),
                    'booking_id' => $booking->id,
                    'inventory_face_id' => $item->inventory_face_id,
                    'start_date' => $item->start_date,
                    'end_date' => $item->end_date,
                    'daily_frequency' => $item->daily_frequency,
                    'net_price_cents' => (int) round($item->price_cents * 0.85),
                    'markup_percentage' => 0,
                    'retail_price_cents' => $item->price_cents,
                    'total_item_price_cents' => $item->price_cents,
                    'pricing_snapshot' => [
                        'quoted_price' => $item->price_cents,
                    ],
                ]);

                // Create availability block
                InventoryAvailability::create([
                    'id' => (string) Str::uuid(),
                    'inventory_face_id' => $item->inventory_face_id,
                    'start_at' => $item->start_date->startOfDay(),
                    'end_at' => $item->end_date->endOfDay(),
                    'availability_status' => 'blocked',
                    'reason' => "Booking converted from Quotation {$quotation->quotation_number}",
                    'source' => 'Booking',
                ]);
            }

            // Track activity polymorphic
            $quotation->activities()->create([
                'id' => (string) Str::uuid(),
                'performed_by' => auth()->id() ?? $customerId,
                'activity_type' => 'status_change',
                'description' => "Quotation {$quotation->quotation_number} converted to Booking {$code}",
            ]);

            // Publish events
            $eventData = [
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'customer_id' => $booking->customer_id,
                'branch_id' => $booking->branch_id,
                'start_date' => $booking->start_date->toDateString(),
                'end_date' => $booking->end_date->toDateString(),
                'grand_total_cents' => $booking->grand_total_cents,
                'status' => 'approved',
                'quotation_number' => $quotation->quotation_number,
            ];

            $this->outboxService->record(
                aggregateType: 'Booking',
                aggregateId: $booking->id,
                eventName: 'booking.created.v1',
                data: $eventData,
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );

            $this->outboxService->record(
                aggregateType: 'Quotation',
                aggregateId: $quotation->id,
                eventName: 'quotation.converted.v1',
                data: ['quotation_id' => $quotation->id, 'booking_id' => $booking->id],
                eventVersion: 1,
                schemaVersion: '1.0.0'
            );

            Event::dispatch(new BookingCreated(
                aggregateId: $booking->id,
                aggregateVersion: 1,
                data: $eventData,
                occurredAt: now()->toIso8601String(),
                correlationId: TraceContext::correlationId() ?? (string) Str::uuid(),
                traceId: TraceContext::traceId() ?? (string) Str::uuid()
            ));

            BookingActivity::create([
                'id' => (string) Str::uuid(),
                'booking_id' => $booking->id,
                'performed_by' => auth()->id() ?? $booking->customer_id,
                'event_name' => 'booking.created.v1',
                'action' => 'Created',
                'old_values' => null,
                'new_values' => $booking->toArray(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'trace_id' => TraceContext::traceId() ?? (string) Str::uuid(),
            ]);

            return $booking;
        });
    }
}
