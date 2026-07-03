<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Presentation\Policies;

use App\Models\User;
use App\Modules\Inventory\Domain\Entities\Inventory;
use App\Modules\Providers\Domain\Entities\ProviderStaff;
use Illuminate\Auth\Access\HandlesAuthorization;

class InventoryPolicy
{
    use HandlesAuthorization;

    /**
     * View paginated listing.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'branch_manager', 'provider_admin']);
    }

    /**
     * View aggregate root details.
     */
    public function view(User $user, Inventory $inventory): bool
    {
        if ($user->hasRole(['super_admin', 'branch_manager'])) {
            return true;
        }

        // Provider staff can view their own inventory
        return ProviderStaff::where('provider_id', $inventory->provider_id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Create new inventory listings.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'branch_manager', 'provider_admin']);
    }

    /**
     * Update inventory configurations.
     */
    public function update(User $user, Inventory $inventory): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->hasRole(['branch_manager', 'provider_admin']) &&
            ProviderStaff::where('provider_id', $inventory->provider_id)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->exists();
    }

    /**
     * Soft delete inventory.
     */
    public function delete(User $user, Inventory $inventory): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Approve or reject inventory submissions.
     */
    public function approve(User $user): bool
    {
        return $user->hasRole(['super_admin', 'branch_manager']);
    }

    /**
     * Manage pricing on faces.
     */
    public function managePricing(User $user, Inventory $inventory): bool
    {
        return $this->update($user, $inventory);
    }

    /**
     * Manage availability blocks.
     */
    public function manageAvailability(User $user, Inventory $inventory): bool
    {
        return $this->update($user, $inventory);
    }

    /**
     * Upload compliance documents.
     */
    public function uploadDocuments(User $user, Inventory $inventory): bool
    {
        return $this->update($user, $inventory);
    }
}
