<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Presentation\Policies;

use App\Models\User;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Providers\Domain\Entities\ProviderStaff;
use Illuminate\Auth\Access\HandlesAuthorization;

class CampaignPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'branch_manager', 'provider_admin', 'provider_staff', 'customer_admin', 'customer_staff']);
    }

    public function view(User $user, Campaign $campaign): bool
    {
        if ($user->hasRole(['super_admin', 'branch_manager'])) {
            return true;
        }

        // Customer owner check
        if ($user->hasRole(['customer_admin', 'customer_staff']) && $campaign->customer_id === $user->id) {
            return true;
        }

        // Provider staff check: only if campaign is scheduled/running on faces owned by their provider
        if ($user->hasRole(['provider_admin', 'provider_staff'])) {
            $providers = ProviderStaff::where('user_id', $user->id)
                ->where('is_active', true)
                ->pluck('provider_id');

            return $campaign->inventoryFaces()
                ->whereHas('inventory', function ($q) use ($providers) {
                    $q->whereIn('provider_id', $providers);
                })->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'branch_manager', 'customer_admin']);
    }

    public function update(User $user, Campaign $campaign): bool
    {
        if ($user->hasRole(['super_admin', 'branch_manager'])) {
            return true;
        }

        return $user->hasRole('customer_admin') && $campaign->customer_id === $user->id;
    }

    public function auditCreative(User $user): bool
    {
        return $user->hasRole(['super_admin', 'branch_manager']);
    }

    public function uploadCreative(User $user, Campaign $campaign): bool
    {
        return $this->update($user, $campaign);
    }

    public function uploadProof(User $user, Campaign $campaign): bool
    {
        if ($user->hasRole(['super_admin', 'branch_manager'])) {
            return true;
        }

        if ($user->hasRole(['provider_admin', 'provider_staff'])) {
            $providers = ProviderStaff::where('user_id', $user->id)
                ->where('is_active', true)
                ->pluck('provider_id');

            return $campaign->inventoryFaces()
                ->whereHas('inventory', function ($q) use ($providers) {
                    $q->whereIn('provider_id', $providers);
                })->exists();
        }

        return false;
    }

    public function auditProof(User $user): bool
    {
        return $user->hasRole(['super_admin', 'branch_manager']);
    }
}
