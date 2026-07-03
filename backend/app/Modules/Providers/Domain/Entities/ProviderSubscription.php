<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Modules\Providers\Domain\Enums\BillingCycle;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderSubscription extends BaseModel
{
    protected $table = 'provider_subscriptions';

    protected $fillable = [
        'provider_id',
        'subscription_plan_id',
        'max_active_screens',
        'billing_cycle',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'max_active_screens' => 'integer',
        'billing_cycle' => BillingCycle::class,
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }
}
