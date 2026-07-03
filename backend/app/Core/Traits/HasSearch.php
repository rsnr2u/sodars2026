<?php

declare(strict_types=1);

namespace App\Core\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasSearch
{
    /**
     * Scope a query to search model fields.
     */
    public function scopeSearch(Builder $query, string $term, array $fields = []): Builder
    {
        if (empty($term) || empty($fields)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term, $fields): void {
            foreach ($fields as $index => $field) {
                if ($index === 0) {
                    $q->where($field, 'LIKE', "%{$term}%");
                } else {
                    $q->orWhere($field, 'LIKE', "%{$term}%");
                }
            }
        });
    }
}
