<?php

declare(strict_types=1);

namespace App\Platform\DAM\Presentation\Policies;

use App\Models\User;
use App\Platform\DAM\Domain\Entities\Asset;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssetPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('inventory.view') || $user->hasPermissionTo('campaign.view') || $user->hasPermissionTo('booking.view');
    }

    public function view(User $user, Asset $asset): bool
    {
        return $user->hasPermissionTo('inventory.view') || $user->hasPermissionTo('campaign.view') || $user->hasPermissionTo('booking.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('inventory.create') || $user->hasPermissionTo('campaign.create') || $user->hasPermissionTo('booking.create');
    }

    public function update(User $user, Asset $asset): bool
    {
        return $user->hasPermissionTo('inventory.edit') || $user->hasPermissionTo('campaign.edit') || $user->hasPermissionTo('booking.edit');
    }

    public function delete(User $user, Asset $asset): bool
    {
        return $user->hasPermissionTo('inventory.delete') || $user->hasPermissionTo('campaign.delete') || $user->hasPermissionTo('booking.delete');
    }
}
