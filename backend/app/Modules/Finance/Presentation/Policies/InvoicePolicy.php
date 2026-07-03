<?php

declare(strict_types=1);

namespace App\Modules\Finance\Presentation\Policies;

use App\Models\User;
use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Domain\Entities\ProviderSettlement;
use App\Modules\Providers\Domain\Entities\ProviderStaff;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'branch_manager', 'provider_admin', 'provider_staff', 'customer_admin', 'customer_staff']);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->hasRole(['super_admin', 'branch_manager'])) {
            return true;
        }

        if ($user->hasRole(['customer_admin', 'customer_staff']) && $invoice->customer_id === $user->id) {
            return true;
        }

        if ($user->hasRole(['provider_admin', 'provider_staff'])) {
            $providers = ProviderStaff::where('user_id', $user->id)
                ->where('is_active', true)
                ->pluck('provider_id');

            $snapshot = $invoice->booking_snapshot;
            $providerId = $snapshot['provider']['id'] ?? null;

            return $providerId && $providers->contains($providerId);
        }

        return false;
    }

    public function update(User $user): bool
    {
        return $user->hasRole(['super_admin', 'branch_manager']);
    }

    public function viewSettlements(User $user): bool
    {
        return $user->hasRole(['super_admin', 'branch_manager', 'provider_admin', 'provider_staff']);
    }

    public function manageSettlements(User $user): bool
    {
        return $user->hasRole(['super_admin', 'branch_manager']);
    }
}
