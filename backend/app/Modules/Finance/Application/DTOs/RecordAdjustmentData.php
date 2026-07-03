<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\DTOs;

use Illuminate\Http\Request;

class RecordAdjustmentData
{
    public function __construct(
        public readonly string $adjustmentType, // credit, debit
        public readonly int $amountCents,
        public readonly string $reason
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            adjustmentType: $request->input('adjustment_type'),
            amountCents: (int) $request->input('amount_cents'),
            reason: $request->input('reason')
        );
    }
}
