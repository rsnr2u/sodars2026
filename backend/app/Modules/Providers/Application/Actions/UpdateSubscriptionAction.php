<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Actions;

use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Entities\ProviderSubscription;
use App\Modules\Providers\Domain\Repositories\ProviderReadRepositoryInterface;
use App\Modules\Providers\Application\Services\ProviderLifecycleService;

class UpdateSubscriptionAction
{
    public function __construct(
        protected ProviderReadRepositoryInterface $providerReadRepo,
        protected ProviderLifecycleService $lifecycleService
    ) {}

    /**
     * Update active subscription plans and limits using canonical lifecycle service.
     */
    public function execute(string $providerId, ?string $planId, int $maxScreens, string $billingCycle): ProviderSubscription
    {
        /** @var Provider $provider */
        $provider = $this->providerReadRepo->findOrFail($providerId);

        ProviderSubscription::where('provider_id', $providerId)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        /** @var ProviderSubscription $sub */
        $sub = ProviderSubscription::create([
            'provider_id' => $providerId,
            'subscription_plan_id' => $planId,
            'max_active_screens' => $maxScreens,
            'billing_cycle' => $billingCycle,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'is_active' => true,
        ]);

        // Load relation for event context
        $provider->load('activeSubscription');

        // Delegate to canonical lifecycle service
        $this->lifecycleService->recordSubscriptionChange($provider);

        return $sub;
    }
}
