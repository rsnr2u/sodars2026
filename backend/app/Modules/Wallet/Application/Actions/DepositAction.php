<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Application\Actions;

use App\Modules\Wallet\Domain\Entities\Wallet;
use App\Modules\Wallet\Domain\Services\WalletService;
use App\Modules\Wallet\Domain\Entities\WalletTransaction;
use Illuminate\Support\Facades\Gate;

class DepositAction
{
    public function __construct(protected WalletService $walletService) {}

    public function execute(string $walletId, int $amountCents, string $reference, array $metadata = []): WalletTransaction
    {
        $wallet = Wallet::findOrFail($walletId);
        
        Gate::authorize('view', $wallet);

        return $this->walletService->deposit($wallet, $amountCents, $reference, $metadata);
    }
}
