<?php

declare(strict_types=1);

namespace App\Platform\Search\Domain\Contracts;

interface Searchable
{
    /**
     * Convert the entity into a searchable document array payload.
     *
     * @return array{
     *     searchable_text: string,
     *     filterable_attributes: array<string, mixed>,
     *     facet_values: array<string, mixed>,
     *     sortable_attributes: array<string, mixed>,
     *     display_data: array<string, mixed>
     * }
     */
    public function toSearchDocument(): array;

    /**
     * Get the index name associated with this searchable model.
     */
    public static function getSearchIndexName(): string;

    /**
     * Get field mappings definition for this model.
     */
    public static function getSearchFieldMappings(): array;

    /**
     * Get facet fields definition for this model.
     */
    public static function getSearchFacetFields(): array;
}
