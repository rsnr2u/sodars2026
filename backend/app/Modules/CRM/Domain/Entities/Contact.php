<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends BaseBusinessModel
{
    protected $table = 'crm_contacts';

    protected $fillable = [
        'organization_id',
        'account_id',
        'first_name',
        'last_name',
        'email',
        'phone',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
