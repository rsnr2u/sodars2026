<?php

declare(strict_types=1);

namespace App\Platform\Identity\Domain\Entities;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Platform\Identity\Infrastructure\Traits\BelongsToOrganization;

class Team extends Model
{
    use HasUuid;
    use BelongsToOrganization;

    protected $table = 'teams';

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(TeamMember::class, 'team_id');
    }
}
