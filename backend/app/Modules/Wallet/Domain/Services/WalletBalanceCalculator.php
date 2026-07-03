<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Domain\Services;

use App\Modules\Wallet\Domain\Entities\Wallet;
use App\Platform\Accounting\Posting\GeneralLedger;

class WalletBalanceCalculator
{
    public function __construct(protected GeneralLedger $generalLedger) {}

    public function calculate(Wallet $wallet): int
    {
        $wallet->loadMissing('ledgerAccount');
        return $this->generalLedger->calculateBalance($wallet->ledgerAccount);
    }
}
