<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Entities;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends BaseModel
{
    protected $table = 'crm_accounts';

    protected $fillable = [
        'name',
        'industry',
        'website',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'account_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'account_id');
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class, 'account_id');
    }
}
