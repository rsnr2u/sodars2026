<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Presentation\Policies;

use App\Models\User;
use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\Providers\Domain\Entities\ProviderStaff;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'branch_manager', 'provider_admin', 'provider_staff', 'customer_admin', 'customer_staff']);
    }

    public function view(User $user, Booking $booking): bool
    {
        if ($user->hasRole(['super_admin', 'branch_manager'])) {
            return true;
        }

        if ($user->hasRole(['customer_admin', 'customer_staff']) && $booking->customer_id === $user->id) {
            return true;
        }

        if ($user->hasRole(['provider_admin', 'provider_staff'])) {
            $providers = ProviderStaff::where('user_id', $user->id)
                ->where('is_active', true)
                ->pluck('provider_id');

            return $booking->items()
                ->whereHas('face.inventory', function ($q) use ($providers) {
                    $q->whereIn('provider_id', $providers);
                })->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'branch_manager', 'customer_admin']);
    }

    public function update(User $user, Booking $booking): bool
    {
        if ($user->hasRole(['super_admin', 'branch_manager'])) {
            return true;
        }

        return $user->hasRole('customer_admin') && $booking->customer_id === $user->id;
    }

    public function recordPayment(User $user, Booking $booking): bool
    {
        return $this->update($user, $booking);
    }

    public function auditPayment(User $user): bool
    {
        return $user->hasRole(['super_admin', 'branch_manager']);
    }

    public function auditBooking(User $user): bool
    {
        return $user->hasRole(['super_admin', 'branch_manager', 'provider_admin']);
    }
}
