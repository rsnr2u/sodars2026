<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\DTOs;

use Illuminate\Http\Request;
use Carbon\Carbon;

class InventoryPricingData
{
    public function __construct(
        public readonly string $pricingType,
        public readonly int $rateCents,
        public readonly string $currency,
        public readonly bool $taxInclusive,
        public readonly int $minimumBookingDays,
        public readonly Carbon $effectiveFrom,
        public readonly ?Carbon $effectiveTo = null,
        public readonly int $priority = 0
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            pricingType: $request->input('pricing_type', 'baseline'),
            rateCents: (int) $request->input('rate_cents'),
            currency: $request->input('currency', 'INR'),
            taxInclusive: (bool) $request->input('tax_inclusive', false),
            minimumBookingDays: (int) $request->input('minimum_booking_days', 1),
            effectiveFrom: Carbon::parse($request->input('effective_from')),
            effectiveTo: $request->input('effective_to') ? Carbon::parse($request->input('effective_to')) : null,
            priority: (int) $request->input('priority', 0)
        );
    }

    /**
     * Map from raw array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            pricingType: $data['pricing_type'] ?? 'baseline',
            rateCents: (int) ($data['rate_cents'] ?? 0),
            currency: $data['currency'] ?? 'INR',
            taxInclusive: (bool) ($data['tax_inclusive'] ?? false),
            minimumBookingDays: (int) ($data['minimum_booking_days'] ?? 1),
            effectiveFrom: Carbon::parse($data['effective_from'] ?? 'now'),
            effectiveTo: isset($data['effective_to']) ? Carbon::parse($data['effective_to']) : null,
            priority: (int) ($data['priority'] ?? 0)
        );
    }
}
