<?php

declare(strict_types=1);

namespace App\Modules\Branches\Presentation\Policies;

use App\Models\User;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Modules\Branches\Domain\Entities\BranchUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class BranchPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can list branches.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine if user can view specific branch.
     */
    public function view(User $user, Branch $branch): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return BranchUser::where('branch_id', $branch->id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Determine if user can create branches.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine if user can update branch details.
     */
    public function update(User $user, Branch $branch): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return BranchUser::where('branch_id', $branch->id)
            ->where('user_id', $user->id)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Determine if user can change branch status.
     */
    public function changeStatus(User $user, Branch $branch): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine if user can delete/soft-delete branch.
     */
    public function delete(User $user, Branch $branch): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine if user can manage coverage areas.
     */
    public function manageCoverage(User $user, Branch $branch): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return BranchUser::where('branch_id', $branch->id)
            ->where('user_id', $user->id)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Determine if user can manage branch members.
     */
    public function manageMembers(User $user, Branch $branch): bool
    {
        return $user->hasRole('super_admin');
    }
}
