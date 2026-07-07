<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Platform\Search\Domain\Contracts\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceAlert extends BaseBusinessModel implements Searchable
{
    protected $table = 'device_alerts';

    protected $fillable = [
        'organization_id',
        'device_id',
        'alert_type',
        'severity',
        'message',
        'raised_at',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'raised_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    // Searchable Contract Implementation
    public function toSearchDocument(): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'title' => "Alert: {$this->alert_type}",
            'subtitle' => $this->severity,
            'content' => $this->message,
            'type' => 'iot_alert',
            'metadata' => [
                'severity' => $this->severity,
                'alert_type' => $this->alert_type,
            ],
        ];
    }

    public static function getSearchIndexName(): string
    {
        return 'iot_alerts';
    }

    public static function getSearchFieldMappings(): array
    {
        return [
            'alert_type' => 'string',
        ];
    }

    public static function getSearchFacetFields(): array
    {
        return ['severity'];
    }
}
