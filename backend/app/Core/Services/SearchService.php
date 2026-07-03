<?php

declare(strict_types=1);

namespace App\Core\Services;

use Illuminate\Database\Eloquent\Builder;

class SearchService
{
    /**
     * Apply fulltext index or LIKE query constraints based on active driver.
     */
    public function applySearch(Builder $query, string $term, array $searchableColumns): Builder
    {
        $driver = config('search.driver', 'database');

        if ($driver === 'database' || empty($term)) {
            return $query->where(function (Builder $q) use ($term, $searchableColumns): void {
                foreach ($searchableColumns as $index => $column) {
                    if ($index === 0) {
                        $q->where($column, 'LIKE', "%{$term}%");
                    } else {
                        $q->orWhere($column, 'LIKE', "%{$term}%");
                    }
                }
            });
        }

        // Future placeholders for Scout / Meilisearch / Elasticsearch integrations
        return $query;
    }
}
