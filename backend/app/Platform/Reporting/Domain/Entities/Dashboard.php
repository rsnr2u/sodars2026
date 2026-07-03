<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Domain\Entities;

use App\Core\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dashboard extends Model
{
    use HasUuid;

    protected $table = 'dashboards';

    protected $fillable = [
        'user_id',
        'name',
        'is_default',
        'layout_config',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'layout_config' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(DashboardWidget::class, 'dashboard_id');
    }
}
