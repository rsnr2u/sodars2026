<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\DTOs;

class GenerateSettlementData
{
    public function __construct(
        public readonly string $bookingId,
        public readonly string $invoiceId
    ) {}
}
