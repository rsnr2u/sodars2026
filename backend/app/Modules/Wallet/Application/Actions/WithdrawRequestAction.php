<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Application\Actions;

use App\Modules\Wallet\Domain\Entities\Wallet;
use App\Modules\Wallet\Domain\Services\WalletService;
use App\Modules\Wallet\Domain\Entities\Withdrawal;
use Illuminate\Support\Facades\Gate;

class WithdrawRequestAction
{
    public function __construct(protected WalletService $walletService) {}

    public function execute(string $walletId, int $amountCents, array $bankDetails): Withdrawal
    {
        $wallet = Wallet::findOrFail($walletId);

        Gate::authorize('view', $wallet);

        return $this->walletService->requestWithdrawal($wallet, $amountCents, $bankDetails);
    }
}
