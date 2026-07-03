<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Queries;

use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Repositories\ProviderReadRepositoryInterface;

class GetProviderDetailsQuery
{
    public function __construct(
        protected ProviderReadRepositoryInterface $providerReadRepo
    ) {}

    /**
     * Retrieve a detailed provider aggregate graph.
     */
    public function execute(string $id): Provider
    {
        /** @var Provider $provider */
        $provider = $this->providerReadRepo->findOrFail($id);

        $provider->load([
            'addresses.country',
            'addresses.state',
            'addresses.district',
            'addresses.city',
            'addresses.pincode',
            'contacts',
            'documents.verifier',
            'staff.user',
            'subscriptions',
            'bankAccounts.verifier',
            'settings',
            'activities.creator',
        ]);

        return $provider;
    }
}
