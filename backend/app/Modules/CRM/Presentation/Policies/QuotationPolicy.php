<?php

declare(strict_types=1);

namespace App\Modules\CRM\Presentation\Policies;

use App\Models\User;
use App\Modules\CRM\Domain\Entities\Quotation;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuotationPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Quotation $quote): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin', 'sales_manager', 'sales_rep']);
    }

    public function update(User $user, Quotation $quote): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin', 'sales_manager', 'sales_rep']);
    }
}
