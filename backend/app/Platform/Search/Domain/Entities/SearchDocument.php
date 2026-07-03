<?php

declare(strict_types=1);

namespace App\Platform\Search\Domain\Entities;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchDocument extends Model
{
    use HasUuid;

    protected $table = 'search_documents';

    protected $fillable = [
        'index_id',
        'entity_id',
        'entity_type',
        'searchable_text',
        'filterable_attributes',
        'facet_values',
        'sortable_attributes',
        'display_data',
    ];

    protected $casts = [
        'filterable_attributes' => 'array',
        'facet_values' => 'array',
        'sortable_attributes' => 'array',
        'display_data' => 'array',
    ];

    public function index(): BelongsTo
    {
        return $this->belongsTo(SearchIndex::class, 'index_id');
    }
}
