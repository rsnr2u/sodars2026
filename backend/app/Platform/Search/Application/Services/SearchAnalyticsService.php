<?php

declare(strict_types=1);

namespace App\Platform\Search\Application\Services;

use App\Platform\Search\Domain\Entities\SearchAnalytics;
use Illuminate\Support\Str;

class SearchAnalyticsService
{
    /**
     * Log search query metadata.
     */
    public function logQuery(
        ?string $userId,
        string $indexName,
        string $queryTerm,
        array $filters,
        int $resultCount,
        int $executionTimeMs
    ): SearchAnalytics {
        return SearchAnalytics::create([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'index_name' => $indexName,
            'query_term' => $queryTerm,
            'filters_applied' => $filters,
            'result_count' => $resultCount,
            'execution_time_ms' => $executionTimeMs,
            'searched_at' => now(),
        ]);
    }

    /**
     * Record a click-through action on a search result.
     */
    public function logClick(string $analyticsId, string $entityId, int $position): void
    {
        $log = SearchAnalytics::find($analyticsId);
        if ($log) {
            $log->update([
                'selected_entity_id' => $entityId,
                'selected_position' => $position,
            ]);
        }
    }
}
