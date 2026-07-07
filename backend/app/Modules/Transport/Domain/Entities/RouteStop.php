<?php

declare(strict_types=1);

namespace App\Modules\Transport\Domain\Entities;

use App\Core\Traits\HasUuid;
use App\Platform\Identity\Infrastructure\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteStop extends Model
{
    use HasUuid;
    use BelongsToOrganization;

    protected $table = 'route_stops';

    protected $fillable = [
        'organization_id',
        'route_id',
        'stop_name',
        'sequence_number',
        'latitude',
        'longitude',
        'status',
        'arrived_at',
        'departed_at',
    ];

    protected $casts = [
        'sequence_number' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'arrived_at' => 'datetime',
        'departed_at' => 'datetime',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class, 'route_id');
    }
}
