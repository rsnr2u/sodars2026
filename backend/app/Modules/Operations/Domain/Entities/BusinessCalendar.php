<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Entities;

use App\Core\Models\BaseBusinessModel;

class BusinessCalendar extends BaseBusinessModel
{
    protected $table = 'operations_calendars';

    protected $fillable = [
        'organization_id',
        'name',
        'type',
        'timezone',
        'working_hours',
        'holidays',
    ];

    protected $casts = [
        'working_hours' => 'array',
        'holidays' => 'array',
    ];
}
