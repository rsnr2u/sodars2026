<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Database\Seeders;

use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Wallet\Domain\Services\WalletService;
use App\Modules\Wallet\Domain\Entities\Wallet;
use App\Platform\Accounting\Database\Seeders\ChartOfAccountsSeeder;
use App\Platform\Accounting\ChartOfAccounts\LedgerAccount;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Chart of Accounts is seeded
        if (LedgerAccount::count() === 0) {
            $this->call(ChartOfAccountsSeeder::class);
        }

        $provider = Provider::first();

        if ($provider) {
            $walletService = app(WalletService::class);
            
            // Create Provider Wallet if missing
            $wallet = Wallet::where('holder_type', Provider::class)
                ->where('holder_id', $provider->id)
                ->first();

            if (!$wallet) {
                $wallet = $walletService->createWallet($provider, 'provider', 'INR');
                
                // Initialize with a demo opening deposit of 100,000 cents (INR 1,000)
                $walletService->deposit(
                    $wallet,
                    100000,
                    'DEP-INIT-001',
                    ['note' => 'Demo opening balance deposit']
                );
            }
        }
    }
}
