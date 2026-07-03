<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Provider extends BaseModel
{
    protected $table = 'providers';

    protected $fillable = [
        'company_name',
        'registration_number',
        'provider_code',
        'default_branch_id',
        'status',
        'preferred_payout_method',
        'external_reference',
        'legacy_reference',
    ];

    protected $casts = [
        'status' => ProviderStatus::class,
    ];

    public function addresses(): HasMany
    {
        return $this->hasMany(ProviderAddress::class, 'provider_id');
    }

    public function primaryAddress(): HasOne
    {
        return $this->hasOne(ProviderAddress::class, 'provider_id')->where('is_primary', true);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(ProviderContact::class, 'provider_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProviderDocument::class, 'provider_id');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(ProviderStaff::class, 'provider_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(ProviderSubscription::class, 'provider_id');
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(ProviderSubscription::class, 'provider_id')->where('is_active', true);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(ProviderBankAccount::class, 'provider_id');
    }

    public function primaryBankAccount(): HasOne
    {
        return $this->hasOne(ProviderBankAccount::class, 'provider_id')->where('is_primary', true);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(ProviderSetting::class, 'provider_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProviderActivity::class, 'provider_id');
    }
}
