<?php

declare(strict_types=1);

namespace App\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasBranch
{
    /**
     * Scope a query to only include branch scoped data.
     */
    public function scopeForUserBranch(Builder $query): Builder
    {
        if (Auth::check() && ! Auth::user()->hasRole('super_admin')) {
            $userBranchId = Auth::user()->branch_id ?? null;
            if ($userBranchId) {
                return $query->where('branch_id', $userBranchId);
            }
        }

        return $query;
    }
}
