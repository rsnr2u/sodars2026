<?php

declare(strict_types=1);

namespace App\Platform\Accounting\Posting;

use App\Core\ValueObjects\Money;

class LedgerLineData
{
    public function __construct(
        public readonly string $accountId,
        public readonly string $entryType, // debit, credit
        public readonly Money $money,
        public readonly ?string $description = null,
        public readonly ?string $ledgerableType = null,
        public readonly ?string $ledgerableId = null,
        public readonly float $exchangeRate = 1.0000,
        public readonly ?string $baseCurrency = 'INR'
    ) {}
}
