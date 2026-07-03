<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Infrastructure\Listeners;

use App\Modules\Finance\Domain\Events\SettlementPaid;
use App\Modules\Finance\Domain\Entities\ProviderSettlement;
use App\Modules\Wallet\Domain\Services\WalletService;
use App\Modules\Wallet\Domain\Entities\Wallet;
use App\Modules\Providers\Domain\Entities\Provider;
use Illuminate\Support\Facades\Log;

class SettlementListener
{
    public function __construct(protected WalletService $walletService) {}

    public function handleSettlementPaid(SettlementPaid $event): void
    {
        $settlementId = $event->aggregateId;
        $settlement = ProviderSettlement::find($settlementId);

        if (!$settlement) {
            return;
        }

        $providerId = $settlement->provider_id;
        $provider = Provider::find($providerId);

        if (!$provider) {
            return;
        }

        // Find or create wallet for the partner provider
        $wallet = Wallet::where('holder_type', Provider::class)
            ->where('holder_id', $providerId)
            ->first();

        if (!$wallet) {
            $wallet = $this->walletService->createWallet($provider, 'provider', 'INR');
        }

        // Auto credit the net payout amount cents to the provider's wallet liability account
        $this->walletService->creditSettlement(
            $wallet,
            (int) $settlement->provider_share_cents,
            $settlement->id
        );
    }

    public function subscribe(mixed $events): void
    {
        $events->listen(
            SettlementPaid::class,
            [self::class, 'handleSettlementPaid']
        );
    }
}
