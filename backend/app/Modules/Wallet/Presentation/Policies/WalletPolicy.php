<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Presentation\Policies;

use App\Models\User;
use App\Modules\Wallet\Domain\Entities\Wallet;
use Illuminate\Auth\Access\HandlesAuthorization;

class WalletPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Wallet $wallet): bool
    {
        // Admin can view any wallet, or user is the holder
        if ($user->hasAnyRole(['admin', 'super_admin'])) {
            return true;
        }

        return $wallet->holder_id === $user->id;
    }

    public function update(User $user, Wallet $wallet): bool
    {
        if ($user->hasAnyRole(['admin', 'super_admin'])) {
            return true;
        }

        return $wallet->holder_id === $user->id;
    }
}
