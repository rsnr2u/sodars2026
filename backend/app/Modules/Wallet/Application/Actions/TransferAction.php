<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Application\Actions;

use App\Modules\Wallet\Domain\Entities\Wallet;
use App\Modules\Wallet\Domain\Services\WalletService;
use Illuminate\Support\Facades\Gate;

class TransferAction
{
    public function __construct(protected WalletService $walletService) {}

    public function execute(string $fromWalletId, string $toWalletId, int $amountCents, string $reference): void
    {
        $fromWallet = Wallet::findOrFail($fromWalletId);
        $toWallet = Wallet::findOrFail($toWalletId);

        Gate::authorize('view', $fromWallet);

        $this->walletService->transfer($fromWallet, $toWallet, $amountCents, $reference);
    }
}
