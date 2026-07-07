<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Modules\Operations\Domain\Enums\ResourceState;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceStateHistory extends BaseBusinessModel
{
    protected $table = 'operations_resource_state_history';

    protected $fillable = [
        'organization_id',
        'resource_id',
        'state',
        'started_at',
        'ended_at',
        'reason',
    ];

    protected $casts = [
        'state' => ResourceState::class,
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(OperationalResource::class, 'resource_id');
    }
}
