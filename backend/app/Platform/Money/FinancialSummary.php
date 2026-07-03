<?php

declare(strict_types=1);

namespace App\Platform\Money;

use App\Core\ValueObjects\Money;
use JsonSerializable;

/**
 * Immutable financial summary representation.
 */
final class FinancialSummary implements JsonSerializable
{
    public function __construct(
        public readonly Money $subtotal,
        public readonly Money $discount,
        public readonly Money $tax,
        public readonly Money $platformFee,
        public readonly Money $providerShare,
        public readonly Money $commission,
        public readonly Money $grandTotal
    ) {}

    /**
     * @return array{subtotal: int, discount: int, tax: int, platform_fee: int, provider_share: int, commission: int, grand_total: int, currency: string}
     */
    public function toArray(): array
    {
        return [
            'subtotal' => $this->subtotal->getAmount(),
            'discount' => $this->discount->getAmount(),
            'tax' => $this->tax->getAmount(),
            'platform_fee' => $this->platformFee->getAmount(),
            'provider_share' => $this->providerShare->getAmount(),
            'commission' => $this->commission->getAmount(),
            'grand_total' => $this->grandTotal->getAmount(),
            'currency' => $this->grandTotal->getCurrency()->getCode(),
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
