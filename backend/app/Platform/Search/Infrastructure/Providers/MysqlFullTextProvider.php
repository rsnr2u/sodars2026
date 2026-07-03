<?php

declare(strict_types=1);

namespace App\Platform\Search\Infrastructure\Providers;

use App\Platform\Search\Domain\Contracts\SearchProvider;
use App\Platform\Search\Domain\Entities\SearchIndex;
use App\Platform\Search\Domain\Entities\SearchDocument;
use App\Platform\Search\Domain\ValueObjects\SearchQuery;
use App\Platform\Search\Domain\ValueObjects\SearchResult;
use App\Platform\Search\Domain\ValueObjects\SearchHit;
use App\Platform\Search\Domain\ValueObjects\FacetResult;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MysqlFullTextProvider implements SearchProvider
{
    public function search(string $indexName, SearchQuery $query): SearchResult
    {
        $startTime = microtime(true);

        $index = SearchIndex::where('name', $indexName)->firstOrFail();

        $dbQuery = SearchDocument::where('index_id', $index->id);

        $driver = Schema::connection(null)->getConnection()->getDriverName();
        $searchTerm = $query->getTerm();

        // 1. Full-Text Search or LIKE Match
        if (!empty($searchTerm)) {
            if ($driver === 'sqlite') {
                // SQLite fallback using LIKE
                $dbQuery->where(function ($q) use ($searchTerm) {
                    $q->where('searchable_text', 'like', '%' . $searchTerm . '%');
                });
                $dbQuery->select('*', DB::raw('1.0 as search_score'));
            } else {
                // MySQL FULLTEXT MATCH
                $dbQuery->whereRaw('MATCH(searchable_text) AGAINST(? IN BOOLEAN MODE)', [$searchTerm]);
                $dbQuery->select('*', DB::raw('MATCH(searchable_text) AGAINST(?) as search_score'), [$searchTerm]);
            }
        } else {
            $dbQuery->select('*', DB::raw('1.0 as search_score'));
        }

        // 2. Filters application
        foreach ($query->getFilters() as $filterKey => $filterVal) {
            if ($filterVal !== null && $filterVal !== '') {
                if (is_array($filterVal)) {
                    $dbQuery->whereIn("filterable_attributes->{$filterKey}", $filterVal);
                } else {
                    $dbQuery->where("filterable_attributes->{$filterKey}", $filterVal);
                }
            }
        }

        // 3. Sorting
        $sortField = $query->getSortField();
        $sortDir = $query->getSortDirection();

        if ($sortField) {
            if ($sortField === 'relevance' && !empty($searchTerm)) {
                $dbQuery->orderBy('search_score', 'desc');
            } else {
                $dbQuery->orderBy("sortable_attributes->{$sortField}", $sortDir);
            }
        } else {
            if (!empty($searchTerm)) {
                $dbQuery->orderBy('search_score', 'desc');
            } else {
                $dbQuery->orderBy('created_at', 'desc');
            }
        }

        // 4. Extract Facet options before pagination (calculated in PHP)
        $facets = [];
        $facetFields = $query->getFacets();
        if (!empty($facetFields)) {
            $facetDocs = (clone $dbQuery)->get(['facet_values']);
            foreach ($facetFields as $field) {
                $counts = [];
                foreach ($facetDocs as $doc) {
                    $val = data_get($doc->facet_values, $field);
                    if ($val !== null && $val !== '') {
                        $counts[$val] = ($counts[$val] ?? 0) + 1;
                    }
                }
                $values = [];
                foreach ($counts as $val => $count) {
                    $values[] = ['value' => $val, 'count' => $count];
                }
                usort($values, fn($a, $b) => $b['count'] <=> $a['count']);
                $facets[$field] = FacetResult::create($field, $values);
            }
        }

        // 5. Pagination
        $total = $dbQuery->count();
        $page = $query->getPage();
        $perPage = $query->getPerPage();

        $docs = $dbQuery->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $hits = [];
        foreach ($docs as $doc) {
            $hits[] = SearchHit::create(
                $doc->entity_id,
                $doc->entity_type,
                $doc->display_data ?? [],
                (float) ($doc->search_score ?? 1.0),
                []
            );
        }

        $queryTimeMs = (int) ((microtime(true) - $startTime) * 1000);

        return SearchResult::create($hits, $facets, $total, $page, $perPage, $queryTimeMs);
    }

    public function suggest(string $indexName, string $prefix, int $limit = 10): array
    {
        $index = SearchIndex::where('name', $indexName)->first();
        if (!$index) {
            return [];
        }

        $docs = SearchDocument::where('index_id', $index->id)
            ->where('searchable_text', 'like', '%' . $prefix . '%')
            ->take($limit * 2)
            ->get(['display_data', 'searchable_text']);

        $suggestions = [];
        foreach ($docs as $doc) {
            $name = $doc->display_data['name'] ?? null;
            if ($name && Str::startsWith(strtolower((string) $name), strtolower($prefix))) {
                $suggestions[] = $name;
            }
            $code = $doc->display_data['code'] ?? null;
            if ($code && Str::startsWith(strtolower((string) $code), strtolower($prefix))) {
                $suggestions[] = $code;
            }
        }

        return array_values(array_unique(array_slice($suggestions, 0, $limit)));
    }

    public function index(string $indexName, string $entityId, string $entityType, array $document): void
    {
        $index = SearchIndex::where('name', $indexName)->firstOrFail();

        SearchDocument::updateOrCreate(
            ['index_id' => $index->id, 'entity_id' => $entityId],
            [
                'entity_type' => $entityType,
                'searchable_text' => $document['searchable_text'] ?? '',
                'filterable_attributes' => $document['filterable_attributes'] ?? [],
                'facet_values' => $document['facet_values'] ?? [],
                'sortable_attributes' => $document['sortable_attributes'] ?? [],
                'display_data' => $document['display_data'] ?? [],
            ]
        );

        $index->update([
            'document_count' => SearchDocument::where('index_id', $index->id)->count(),
        ]);
    }

    public function remove(string $indexName, string $entityId): void
    {
        $index = SearchIndex::where('name', $indexName)->first();
        if (!$index) {
            return;
        }

        SearchDocument::where('index_id', $index->id)
            ->where('entity_id', $entityId)
            ->delete();

        $index->update([
            'document_count' => SearchDocument::where('index_id', $index->id)->count(),
        ]);
    }

    public function bulkIndex(string $indexName, string $entityType, array $documents): void
    {
        $index = SearchIndex::where('name', $indexName)->firstOrFail();

        DB::transaction(function () use ($index, $entityType, $documents) {
            foreach ($documents as $entityId => $document) {
                SearchDocument::updateOrCreate(
                    ['index_id' => $index->id, 'entity_id' => $entityId],
                    [
                        'entity_type' => $entityType,
                        'searchable_text' => $document['searchable_text'] ?? '',
                        'filterable_attributes' => $document['filterable_attributes'] ?? [],
                        'facet_values' => $document['facet_values'] ?? [],
                        'sortable_attributes' => $document['sortable_attributes'] ?? [],
                        'display_data' => $document['display_data'] ?? [],
                    ]
                );
            }
        });

        $index->update([
            'document_count' => SearchDocument::where('index_id', $index->id)->count(),
        ]);
    }

    public function createIndex(string $indexName, array $fieldMappings): void
    {
    }

    public function deleteIndex(string $indexName): void
    {
        $index = SearchIndex::where('name', $indexName)->first();
        if ($index) {
            $index->documents()->delete();
            $index->delete();
        }
    }
}
