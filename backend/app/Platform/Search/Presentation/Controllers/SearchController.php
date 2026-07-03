<?php

declare(strict_types=1);

namespace App\Platform\Search\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Platform\Search\Application\Services\SearchService;
use App\Platform\Search\Application\Services\SearchAnalyticsService;
use App\Platform\Search\Domain\ValueObjects\SearchQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends BaseApiController
{
    public function __construct(
        protected SearchService $searchService,
        protected SearchAnalyticsService $analytics
    ) {}

    /**
     * Search within an index.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'index' => 'required|string',
            'q' => 'nullable|string',
            'filters' => 'nullable|array',
            'facets' => 'nullable|string',
            'sort_field' => 'nullable|string',
            'sort_dir' => 'nullable|string|in:asc,desc',
        ]);

        $index = $request->input('index');
        $term = $request->input('q');
        $filters = $request->input('filters', []);
        $facets = $request->input('facets') ? explode(',', $request->input('facets')) : [];
        $sortField = $request->input('sort_field');
        $sortDir = $request->input('sort_dir', 'desc');

        $query = SearchQuery::create($term)
            ->inIndex($index)
            ->facetOn($facets);
            
        $page = (int) $request->query('page', 1);
        $perPage = $this->getPerPage();
        $query->page($page, $perPage);

        foreach ($filters as $key => $val) {
            $query->filterBy($key, $val);
        }

        if ($sortField) {
            $query->sortBy($sortField, $sortDir);
        }

        try {
            $result = $this->searchService->search($index, $query, (string) $request->user()?->id);
            return $this->successResponse($result->toArray(), 'Search completed successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    /**
     * Get suggestions/auto-complete.
     */
    public function suggest(Request $request): JsonResponse
    {
        $request->validate([
            'index' => 'required|string',
            'q' => 'required|string|min:1',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $index = $request->input('index');
        $prefix = $request->input('q');
        $limit = (int) $request->input('limit', 10);

        try {
            $suggestions = $this->searchService->suggest($index, $prefix, $limit);
            return $this->successResponse(['suggestions' => $suggestions], 'Suggestions retrieved.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    /**
     * Global cross-index search.
     */
    public function globalSearch(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:1',
            'limit_per_index' => 'nullable|integer|min:1|max:20',
        ]);

        $term = $request->input('q');
        $limit = (int) $request->input('limit_per_index', 5);

        try {
            $results = $this->searchService->globalSearch($term, $limit, (string) $request->user()?->id);
            return $this->successResponse(['results' => $results], 'Global search completed.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    /**
     * Record a click-through.
     */
    public function logClick(Request $request): JsonResponse
    {
        $request->validate([
            'analytics_id' => 'required|uuid',
            'entity_id' => 'required|string',
            'position' => 'required|integer|min:0',
        ]);

        $this->analytics->logClick(
            $request->input('analytics_id'),
            $request->input('entity_id'),
            (int) $request->input('position')
        );

        return $this->successResponse(null, 'Click logged successfully.');
    }
}
