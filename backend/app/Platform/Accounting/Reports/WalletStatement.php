<?php

declare(strict_types=1);

namespace App\Platform\Accounting\Reports;

class WalletStatement
{
    public function generate(string $walletId): array
    {
        return ['wallet_id' => $walletId, 'opening_balance' => 0, 'deposits' => 0, 'withdrawals' => 0, 'closing_balance' => 0];
    }
}
