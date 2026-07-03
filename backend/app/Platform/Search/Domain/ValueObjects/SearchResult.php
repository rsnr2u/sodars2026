<?php

declare(strict_types=1);

namespace App\Platform\Search\Domain\ValueObjects;

class SearchResult
{
    /**
     * @param array<int, SearchHit> $hits
     * @param array<string, FacetResult> $facets
     */
    public function __construct(
        public readonly array $hits,
        public readonly array $facets,
        public readonly int $total,
        public readonly int $page,
        public readonly int $perPage,
        public readonly int $queryTimeMs = 0
    ) {}

    public static function create(
        array $hits,
        array $facets,
        int $total,
        int $page,
        int $perPage,
        int $queryTimeMs = 0
    ): self {
        return new self($hits, $facets, $total, $page, $perPage, $queryTimeMs);
    }

    public function toArray(): array
    {
        return [
            'hits' => array_map(fn(SearchHit $hit) => $hit->toArray(), $this->hits),
            'facets' => array_map(fn(FacetResult $facet) => $facet->toArray(), $this->facets),
            'meta' => [
                'total' => $this->total,
                'page' => $this->page,
                'per_page' => $this->perPage,
                'query_time_ms' => $this->queryTimeMs,
            ],
        ];
    }
}
