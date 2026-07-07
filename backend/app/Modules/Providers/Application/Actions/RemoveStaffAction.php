<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Actions;

use App\Modules\Providers\Domain\Entities\ProviderStaff;
use App\Modules\Providers\Domain\Repositories\ProviderReadRepositoryInterface;

class RemoveStaffAction
{
    public function __construct(
        protected ProviderReadRepositoryInterface $providerReadRepo
    ) {}

    /**
     * Terminate or suspend staff access to workspace.
     */
    public function execute(string $providerId, string $staffId): void
    {
        $this->providerReadRepo->findOrFail($providerId);

        /** @var ProviderStaff $staff */
        $staff = ProviderStaff::findOrFail($staffId);

        if ($staff->provider_id !== $providerId) {
            throw new \InvalidArgumentException('Staff member does not belong to this provider.');
        }

        $staff->update([
            'is_active' => false,
            'left_at' => now(),
        ]);
    }
}
