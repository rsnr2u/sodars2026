<?php

declare(strict_types=1);

namespace App\Platform\Search\Domain\Entities;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SearchIndex extends Model
{
    use HasUuid;

    protected $table = 'search_indexes';

    protected $fillable = [
        'name',
        'entity_type',
        'provider',
        'field_mappings',
        'facet_fields',
        'status',
        'document_count',
        'last_rebuilt_at',
    ];

    protected $casts = [
        'field_mappings' => 'array',
        'facet_fields' => 'array',
        'document_count' => 'integer',
        'last_rebuilt_at' => 'datetime',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(SearchDocument::class, 'index_id');
    }
}
