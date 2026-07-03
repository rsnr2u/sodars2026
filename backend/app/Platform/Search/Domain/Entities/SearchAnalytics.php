<?php

declare(strict_types=1);

namespace App\Platform\Search\Domain\Entities;

use App\Core\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchAnalytics extends Model
{
    use HasUuid;

    protected $table = 'search_analytics';

    protected $fillable = [
        'user_id',
        'index_name',
        'query_term',
        'filters_applied',
        'result_count',
        'execution_time_ms',
        'selected_entity_id',
        'selected_position',
        'searched_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'filters_applied' => 'array',
        'result_count' => 'integer',
        'execution_time_ms' => 'integer',
        'selected_position' => 'integer',
        'searched_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
