<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Platform\Search\Domain\Contracts\Searchable;

class Shift extends BaseBusinessModel implements Searchable
{
    protected $table = 'operations_shifts';

    protected $fillable = [
        'organization_id',
        'name',
        'shift_pattern',
        'status',
    ];

    protected $casts = [
        'shift_pattern' => 'array',
    ];

    public function toSearchDocument(): array
    {
        return [
            'searchable_text' => $this->name,
            'filterable_attributes' => [
                'status' => $this->status,
            ],
            'facet_values' => [
                'status' => $this->status,
            ],
            'sortable_attributes' => [
                'created_at' => $this->created_at?->toIso8601String(),
            ],
            'display_data' => [
                'name' => $this->name,
                'status' => $this->status,
            ],
        ];
    }

    public static function getSearchIndexName(): string
    {
        return 'operations_shifts';
    }

    public static function getSearchFieldMappings(): array
    {
        return [
            'name' => 'string',
        ];
    }

    public static function getSearchFacetFields(): array
    {
        return ['status'];
    }
}
