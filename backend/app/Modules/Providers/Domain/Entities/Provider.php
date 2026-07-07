<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Platform\Search\Domain\Contracts\Searchable;

class Provider extends BaseBusinessModel implements Searchable
{
    protected $table = 'providers';

    protected $fillable = [
        'organization_id',
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

    public function toSearchDocument(): array
    {
        return [
            'searchable_text' => implode(' ', array_filter([
                $this->company_name,
                $this->registration_number,
                $this->provider_code,
                $this->contacts->pluck('contact_name')->implode(' '),
                $this->contacts->pluck('email')->implode(' '),
                $this->contacts->pluck('phone')->implode(' '),
                $this->addresses->pluck('address_line1')->implode(' '),
            ])),
            'filterable_attributes' => [
                'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
                'organization_id' => $this->organization_id,
            ],
            'facet_values' => [
                'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            ],
            'sortable_attributes' => [
                'created_at' => $this->created_at?->toIso8601String(),
            ],
            'display_data' => [
                'name' => $this->company_name,
                'code' => $this->provider_code,
                'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            ],
        ];
    }

    public static function getSearchIndexName(): string
    {
        return 'provider_providers';
    }

    public static function getSearchFieldMappings(): array
    {
        return [
            'company_name' => 'string',
        ];
    }

    public static function getSearchFacetFields(): array
    {
        return ['status'];
    }
}
