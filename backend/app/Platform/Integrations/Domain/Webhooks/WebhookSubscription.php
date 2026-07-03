<?php

declare(strict_types=1);

namespace App\Platform\Integrations\Domain\Webhooks;

use App\Core\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookSubscription extends Model
{
    use HasUuid;

    protected $table = 'webhook_subscriptions';

    protected $fillable = [
        'user_id',
        'target_url',
        'secret_token',
        'event_types',
        'is_active',
    ];

    protected $casts = [
        'event_types' => 'array',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WebhookDeliveryLog::class, 'webhook_subscription_id');
    }
}
