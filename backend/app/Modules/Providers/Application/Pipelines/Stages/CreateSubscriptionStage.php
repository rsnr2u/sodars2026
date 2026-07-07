<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Pipelines\Stages;

use App\Modules\Providers\Domain\Entities\ProviderSubscription;
use App\Modules\Providers\Domain\Enums\BillingCycle;
use Closure;

class CreateSubscriptionStage
{
    /**
     * Start the free tier subscription allocation limits (max 2 active screens).
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $provider = $passable['provider'];

        ProviderSubscription::create([
            'organization_id' => $provider->organization_id,
            'provider_id' => $provider->id,
            'subscription_plan_id' => null,
            'max_active_screens' => 2,
            'billing_cycle' => BillingCycle::Monthly->value,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'is_active' => true,
        ]);

        return $next($passable);
    }
}
