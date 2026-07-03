<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Entities;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends BaseModel
{
    protected $table = 'crm_contacts';

    protected $fillable = [
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
