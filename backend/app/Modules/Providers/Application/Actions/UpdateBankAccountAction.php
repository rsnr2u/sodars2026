<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Actions;

use App\Modules\Providers\Application\DTOs\UpdateBankAccountData;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Entities\ProviderBankAccount;
use App\Modules\Providers\Domain\Repositories\ProviderReadRepositoryInterface;
use App\Modules\Providers\Application\Services\ProviderLifecycleService;

class UpdateBankAccountAction
{
    public function __construct(
        protected ProviderReadRepositoryInterface $providerReadRepo,
        protected ProviderLifecycleService $lifecycleService
    ) {}

    /**
     * Configure payout bank account parameters using canonical lifecycle service.
     */
    public function execute(string $providerId, UpdateBankAccountData $data): ProviderBankAccount
    {
        /** @var Provider $provider */
        $provider = $this->providerReadRepo->findOrFail($providerId);

        if ($data->isPrimary) {
            ProviderBankAccount::where('provider_id', $providerId)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }

        /** @var ProviderBankAccount $account */
        $account = ProviderBankAccount::updateOrCreate([
            'provider_id' => $providerId,
            'account_number' => $data->accountNumber,
        ], [
            'bank_name' => $data->bankName,
            'account_holder' => $data->accountHolder,
            'routing_code' => $data->routingCode,
            'is_primary' => $data->isPrimary,
            'verification_status' => 'pending',
        ]);

        // Load relation for the event context
        $provider->load('primaryBankAccount');

        // Delegate to canonical lifecycle service
        $this->lifecycleService->recordBankAccountUpdate($provider);

        return $account;
    }
}
