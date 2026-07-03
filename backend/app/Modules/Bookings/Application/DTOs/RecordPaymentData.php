<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\DTOs;

use Illuminate\Http\Request;

class RecordPaymentData
{
    public function __construct(
        public readonly string $paymentMethod,
        public readonly int $amountCents,
        public readonly string $referenceNumber,
        public readonly ?string $notes = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            paymentMethod: $request->input('payment_method'),
            amountCents: (int) $request->input('amount_cents'),
            referenceNumber: $request->input('reference_number'),
            notes: $request->input('notes')
        );
    }
}
