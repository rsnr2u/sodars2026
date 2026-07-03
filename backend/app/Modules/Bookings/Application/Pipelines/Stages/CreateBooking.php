<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\Pipelines\Stages;

use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Bookings\Domain\Entities\BookingItem;
use App\Modules\Bookings\Domain\Enums\BookingStatus;
use App\Platform\Identifiers\BookingNumberGenerator;
use Closure;
use Illuminate\Support\Str;

class CreateBooking
{
    public function __construct(
        protected BookingNumberGenerator $numberGenerator
    ) {}

    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];
        $summary = $passable['financial_summary'];
        $prices = $passable['prices'];

        // Determine overall start/end dates
        $minDate = null;
        $maxDate = null;

        foreach ($dto->items as $item) {
            if (!$minDate || $item->startDate < $minDate) {
                $minDate = $item->startDate;
            }
            if (!$maxDate || $item->endDate > $maxDate) {
                $maxDate = $item->endDate;
            }
        }

        $code = $this->numberGenerator->generate();

        $booking = Booking::create([
            'id' => (string) Str::uuid(),
            'booking_code' => $code,
            'customer_id' => $dto->customerId,
            'branch_id' => $dto->branchId,
            'start_date' => $minDate,
            'end_date' => $maxDate,
            'subtotal_cents' => $summary->subtotal->getAmount(),
            'discount_cents' => $summary->discount->getAmount(),
            'tax_cents' => $summary->tax->getAmount(),
            'platform_fee_cents' => $summary->platformFee->getAmount(),
            'provider_share_cents' => $summary->providerShare->getAmount(),
            'commission_cents' => $summary->commission->getAmount(),
            'grand_total_cents' => $summary->grandTotal->getAmount(),
            'currency' => $dto->currency,
            'status' => BookingStatus::Draft->value,
        ]);

        $createdItems = [];

        foreach ($dto->items as $item) {
            $pricingDetails = $prices[$item->inventoryFaceId];

            $createdItems[] = BookingItem::create([
                'id' => (string) Str::uuid(),
                'booking_id' => $booking->id,
                'inventory_face_id' => $item->inventoryFaceId,
                'start_date' => $item->startDate,
                'end_date' => $item->endDate,
                'daily_frequency' => $item->dailyFrequency,
                'net_price_cents' => $pricingDetails['net_price'],
                'markup_percentage' => $pricingDetails['markup_percentage'],
                'retail_price_cents' => $pricingDetails['retail_price'],
                'total_item_price_cents' => $pricingDetails['total_item_price'],
                'pricing_snapshot' => [], // To be populated in pricing snapshot stage
            ]);
        }

        $passable['booking'] = $booking;
        $passable['items'] = $createdItems;

        return $next($passable);
    }
}
