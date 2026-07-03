<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Application\Queries;

use App\Modules\Wallet\Domain\Entities\Wallet;
use App\Modules\Wallet\Domain\Services\WalletBalanceCalculator;
use Illuminate\Support\Facades\Gate;

class GetWalletDetailsQuery
{
    public function __construct(protected WalletBalanceCalculator $balanceCalculator) {}

    public function execute(string $walletId): array
    {
        $wallet = Wallet::findOrFail($walletId);

        Gate::authorize('view', $wallet);

        $balance = $this->balanceCalculator->calculate($wallet);

        return [
            'wallet' => $wallet,
            'balance_cents' => $balance,
        ];
    }
}
