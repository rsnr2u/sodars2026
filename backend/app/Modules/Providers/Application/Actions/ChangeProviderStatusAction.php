<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Actions;

use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Repositories\ProviderReadRepositoryInterface;
use App\Modules\Providers\Application\Services\ProviderLifecycleService;

class ChangeProviderStatusAction
{
    public function __construct(
        protected ProviderReadRepositoryInterface $providerReadRepo,
        protected ProviderLifecycleService $lifecycleService
    ) {}

    /**
     * Transition provider operational status using canonical lifecycle service.
     */
    public function execute(string $providerId, string $newStatus): Provider
    {
        /** @var Provider $provider */
        $provider = $this->providerReadRepo->findOrFail($providerId);

        $this->lifecycleService->transitionTo($provider, $newStatus);

        return $provider;
    }
}
