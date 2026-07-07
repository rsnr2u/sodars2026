<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Modules\Operations\Domain\Enums\ScheduleStatus;
use App\Platform\Search\Domain\Contracts\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends BaseBusinessModel implements Searchable
{
    protected $table = 'operations_schedules';

    protected $fillable = [
        'organization_id',
        'calendar_id',
        'shift_id',
        'schedule_number',
        'name',
        'schedule_type',
        'status',
        'start_time',
        'end_time',
        'metadata',
    ];

    protected $casts = [
        'status' => ScheduleStatus::class,
        'metadata' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(BusinessCalendar::class, 'calendar_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function execution(): HasOne
    {
        return $this->hasOne(ScheduleExecution::class, 'schedule_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ScheduleAssignment::class, 'schedule_id');
    }

    public function conflicts(): HasMany
    {
        return $this->hasMany(ScheduleConflict::class, 'schedule_id');
    }

    public function checkpoints(): HasMany
    {
        return $this->hasMany(ScheduleCheckpoint::class, 'schedule_id');
    }

    public function toSearchDocument(): array
    {
        return [
            'searchable_text' => implode(' ', array_filter([
                $this->schedule_number,
                $this->name,
                $this->schedule_type,
            ])),
            'filterable_attributes' => [
                'status' => $this->status instanceof ScheduleStatus ? $this->status->value : $this->status,
                'schedule_type' => $this->schedule_type,
            ],
            'facet_values' => [
                'status' => $this->status instanceof ScheduleStatus ? $this->status->value : $this->status,
                'schedule_type' => $this->schedule_type,
            ],
            'sortable_attributes' => [
                'created_at' => $this->created_at?->toIso8601String(),
            ],
            'display_data' => [
                'name' => $this->name,
                'code' => $this->schedule_number,
                'status' => $this->status instanceof ScheduleStatus ? $this->status->value : $this->status,
            ],
        ];
    }

    public static function getSearchIndexName(): string
    {
        return 'operations_schedules';
    }

    public static function getSearchFieldMappings(): array
    {
        return [
            'schedule_number' => 'string',
            'name' => 'string',
            'schedule_type' => 'string',
        ];
    }

    public static function getSearchFacetFields(): array
    {
        return ['status', 'schedule_type'];
    }
}
