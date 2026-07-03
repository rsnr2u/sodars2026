<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\DTOs;

use Illuminate\Http\Request;

class RecordInvoicePaymentData
{
    public function __construct(
        public readonly int $amountCents,
        public readonly string $paymentMethod,
        public readonly string $referenceNumber,
        public readonly ?string $notes = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            amountCents: (int) $request->input('amount_cents'),
            paymentMethod: $request->input('payment_method'),
            referenceNumber: $request->input('reference_number'),
            notes: $request->input('notes')
        );
    }
}
