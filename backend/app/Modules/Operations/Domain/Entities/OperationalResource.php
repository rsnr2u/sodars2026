<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Platform\Search\Domain\Contracts\Searchable;

class OperationalResource extends BaseBusinessModel implements Searchable
{
    protected $table = 'operations_resources';

    protected $fillable = [
        'organization_id',
        'resource_type',
        'external_id',
        'display_name',
        'skills',
        'availability_metadata',
        'status',
    ];

    protected $casts = [
        'skills' => 'array',
        'availability_metadata' => 'array',
    ];

    public function toSearchDocument(): array
    {
        return [
            'searchable_text' => implode(' ', array_filter([
                $this->display_name,
                $this->resource_type,
            ])),
            'filterable_attributes' => [
                'resource_type' => $this->resource_type,
                'status' => $this->status,
            ],
            'facet_values' => [
                'resource_type' => $this->resource_type,
                'status' => $this->status,
            ],
            'sortable_attributes' => [
                'created_at' => $this->created_at?->toIso8601String(),
            ],
            'display_data' => [
                'name' => $this->display_name,
                'type' => $this->resource_type,
                'status' => $this->status,
            ],
        ];
    }

    public static function getSearchIndexName(): string
    {
        return 'operations_resources';
    }

    public static function getSearchFieldMappings(): array
    {
        return [
            'display_name' => 'string',
            'resource_type' => 'string',
        ];
    }

    public static function getSearchFacetFields(): array
    {
        return ['resource_type', 'status'];
    }
}
