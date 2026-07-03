<?php

declare(strict_types=1);

namespace App\Modules\Providers\Presentation\Policies;

use App\Models\User;
use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Entities\ProviderStaff;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProviderPolicy
{
    use HandlesAuthorization;

    /**
     * Check if user is allowed to view listing.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'branch_manager']);
    }

    /**
     * View detailed provider graph.
     */
    public function view(User $user, Provider $provider): bool
    {
        if ($user->hasRole(['super_admin', 'branch_manager'])) {
            return true;
        }

        return ProviderStaff::where('provider_id', $provider->id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Create/register provider profiles.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Update details or configuration parameters.
     */
    public function update(User $user, Provider $provider): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->hasRole('provider_admin') &&
            ProviderStaff::where('provider_id', $provider->id)
                ->where('user_id', $user->id)
                ->where('is_primary', true)
                ->where('is_active', true)
                ->exists();
    }

    /**
     * Manage provider workspace users.
     */
    public function manageStaff(User $user, Provider $provider): bool
    {
        return $this->update($user, $provider);
    }

    /**
     * Audit compliance documents.
     */
    public function audit(User $user): bool
    {
        return $user->hasRole(['super_admin', 'branch_manager']);
    }
}
