<?php

declare(strict_types=1);

namespace App\Platform\Search\Domain\Entities;

use App\Core\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedSearch extends Model
{
    use HasUuid;

    protected $table = 'saved_searches';

    protected $fillable = [
        'user_id',
        'name',
        'index_name',
        'query_payload',
        'is_pinned',
    ];

    protected $casts = [
        'query_payload' => 'array',
        'is_pinned' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
