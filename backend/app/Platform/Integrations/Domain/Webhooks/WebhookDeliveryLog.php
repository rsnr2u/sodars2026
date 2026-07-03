<?php

declare(strict_types=1);

namespace App\Platform\Integrations\Domain\Webhooks;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDeliveryLog extends Model
{
    use HasUuid;

    protected $table = 'webhook_delivery_logs';

    protected $fillable = [
        'webhook_subscription_id',
        'event_type',
        'payload',
        'request_headers',
        'response_headers',
        'response_status',
        'response_body',
        'duration_ms',
        'attempt',
        'status',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'request_headers' => 'array',
        'response_headers' => 'array',
        'response_status' => 'integer',
        'duration_ms' => 'integer',
        'attempt' => 'integer',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(WebhookSubscription::class, 'webhook_subscription_id');
    }
}
