<?php

declare(strict_types=1);

namespace App\Modules\Transport\Presentation\Policies;

use App\Models\User;
use App\Modules\Transport\Domain\Entities\Vehicle;
use Illuminate\Auth\Access\HandlesAuthorization;

class VehiclePolicy
{
    use HandlesAuthorization;

    public function view(User $user, Vehicle $vehicle): bool
    {
        if ($user->hasAnyRole(['admin', 'super_admin'])) {
            return true;
        }
        return $user->organization_id === $vehicle->organization_id;
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        if ($user->hasAnyRole(['admin', 'super_admin'])) {
            return true;
        }
        return $user->organization_id === $vehicle->organization_id;
    }
}
