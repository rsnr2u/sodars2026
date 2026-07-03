<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Domain\Entities;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardWidget extends Model
{
    use HasUuid;

    protected $table = 'dashboard_widgets';

    protected $fillable = [
        'dashboard_id',
        'report_key',
        'widget_type',
        'title',
        'dimensions',
        'query_parameters',
        'drilldown_route',
    ];

    protected $casts = [
        'dimensions' => 'array',
        'query_parameters' => 'array',
    ];

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class, 'dashboard_id');
    }
}
