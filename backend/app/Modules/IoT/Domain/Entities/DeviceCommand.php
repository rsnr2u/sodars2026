<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Modules\IoT\Domain\Enums\CommandStatus;
use App\Platform\Search\Domain\Contracts\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceCommand extends BaseBusinessModel implements Searchable
{
    protected $table = 'device_commands';

    protected $fillable = [
        'organization_id',
        'device_id',
        'command_uuid',
        'idempotency_key',
        'correlation_id',
        'command_type',
        'status',
        'payload',
        'attempts',
        'last_attempted_at',
        'completed_at',
        'last_error',
    ];

    protected $casts = [
        'status' => CommandStatus::class,
        'payload' => 'array',
        'attempts' => 'integer',
        'last_attempted_at' => 'datetime',
        'completed_at' => 'datetime',
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
            'title' => "Command: {$this->command_type}",
            'subtitle' => $this->command_uuid,
            'content' => "Status: {$this->status->value} | Attempts: {$this->attempts}",
            'type' => 'iot_command',
            'metadata' => [
                'command_uuid' => $this->command_uuid,
                'status' => $this->status->value,
                'command_type' => $this->command_type,
            ],
        ];
    }

    public static function getSearchIndexName(): string
    {
        return 'iot_commands';
    }

    public static function getSearchFieldMappings(): array
    {
        return [
            'command_uuid' => 'string',
            'command_type' => 'string',
        ];
    }

    public static function getSearchFacetFields(): array
    {
        return ['status'];
    }
}
