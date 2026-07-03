<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderBankAccount extends BaseModel
{
    protected $table = 'provider_bank_accounts';

    protected $fillable = [
        'provider_id',
        'bank_name',
        'account_holder',
        'account_number',
        'routing_code',
        'is_primary',
        'verification_status',
        'verified_by',
        'verified_at',
        'verification_reference',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
