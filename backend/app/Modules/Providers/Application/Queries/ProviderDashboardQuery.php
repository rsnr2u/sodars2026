<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Queries;

use App\Modules\Providers\Application\DTOs\ProviderDashboardDTO;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Entities\ProviderDocument;
use App\Modules\Providers\Domain\Entities\ProviderStaff;
use App\Modules\Providers\Domain\Enums\DocumentStatus;
use App\Modules\Providers\Domain\Repositories\ProviderReadRepositoryInterface;

class ProviderDashboardQuery
{
    public function __construct(
        protected ProviderReadRepositoryInterface $providerReadRepo
    ) {}

    /**
     * Compile metric summary for provider workspace.
     */
    public function execute(string $providerId): ProviderDashboardDTO
    {
        /** @var Provider $provider */
        $provider = $this->providerReadRepo->findOrFail($providerId);

        $activeSub = $provider->activeSubscription;
        $planName = $activeSub ? ($activeSub->subscription_plan_id ? 'Paid' : 'Free') : 'None';

        $documentsPending = ProviderDocument::where('provider_id', $providerId)
            ->where('status', DocumentStatus::Pending->value)
            ->count();

        $staffCount = ProviderStaff::where('provider_id', $providerId)
            ->where('is_active', true)
            ->count();

        return new ProviderDashboardDTO(
            providerId: $providerId,
            companyName: $provider->company_name,
            subscriptionPlan: $planName,
            documentsPending: $documentsPending,
            staffCount: $staffCount,
            inventoryCount: 0,
            revenue: 0.0,
            pendingBookings: 0
        );
    }
}
