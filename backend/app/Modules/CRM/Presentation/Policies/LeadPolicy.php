<?php

declare(strict_types=1);

namespace App\Modules\CRM\Presentation\Policies;

use App\Models\User;
use App\Modules\CRM\Domain\Entities\Lead;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeadPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Lead $lead): bool
    {
        if ($user->hasAnyRole(['admin', 'super_admin', 'sales_manager'])) {
            return true;
        }

        return $lead->assigned_to === $user->id;
    }

    public function update(User $user, Lead $lead): bool
    {
        if ($user->hasAnyRole(['admin', 'super_admin', 'sales_manager'])) {
            return true;
        }

        return $lead->assigned_to === $user->id;
    }
}
