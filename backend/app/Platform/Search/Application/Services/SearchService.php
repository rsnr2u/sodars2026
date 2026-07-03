<?php

declare(strict_types=1);

namespace App\Platform\Search\Application\Services;

use App\Platform\Search\Domain\Entities\SearchIndex;
use App\Platform\Search\Domain\ValueObjects\SearchQuery;
use App\Platform\Search\Domain\ValueObjects\SearchResult;
use App\Platform\Search\Infrastructure\Registry\SearchProviderRegistry;
use App\Platform\Search\Domain\ValueObjects\SearchHit;

class SearchService
{
    public function __construct(
        protected SearchProviderRegistry $registry,
        protected SearchAnalyticsService $analytics
    ) {}

    /**
     * Search within a specific index.
     */
    public function search(string $indexName, SearchQuery $query, ?string $userId = null): SearchResult
    {
        $index = SearchIndex::where('name', $indexName)->firstOrFail();
        $provider = $this->registry->resolve($index->provider);

        $result = $provider->search($indexName, $query);

        $this->analytics->logQuery(
            userId: $userId,
            indexName: $indexName,
            queryTerm: $query->getTerm() ?? '',
            filters: $query->getFilters(),
            resultCount: $result->total,
            executionTimeMs: $result->queryTimeMs
        );

        return $result;
    }

    /**
     * Get suggestions/auto-complete for a prefix.
     */
    public function suggest(string $indexName, string $prefix, int $limit = 10): array
    {
        $index = SearchIndex::where('name', $indexName)->firstOrFail();
        $provider = $this->registry->resolve($index->provider);

        return $provider->suggest($indexName, $prefix, $limit);
    }

    /**
     * Global cross-index search.
     */
    public function globalSearch(string $term, int $limitPerIndex = 5, ?string $userId = null): array
    {
        $indexes = SearchIndex::all();
        $allResults = [];

        foreach ($indexes as $index) {
            $provider = $this->registry->resolve($index->provider);
            $query = SearchQuery::create($term)->page(1, $limitPerIndex);
            
            try {
                $result = $provider->search($index->name, $query);
                foreach ($result->hits as $hit) {
                    $allResults[] = [
                        'index' => $index->name,
                        'entity_type' => $hit->entityType,
                        'entity_id' => $hit->entityId,
                        'display_data' => $hit->displayData,
                        'score' => $hit->score,
                    ];
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Global search failed on index [{$index->name}]: " . $e->getMessage());
            }
        }

        usort($allResults, fn($a, $b) => $b['score'] <=> $a['score']);

        return $allResults;
    }
}
