<?php

declare(strict_types=1);

namespace App\Platform\Search\Presentation\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Platform\Search\Application\Services\SavedSearchService;
use App\Platform\Search\Domain\ValueObjects\SearchQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavedSearchController extends BaseApiController
{
    public function __construct(
        protected SavedSearchService $service
    ) {}

    /**
     * List user's saved searches.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $saved = $this->service->getForUser($userId);

        return $this->successResponse($saved, 'Saved searches retrieved.');
    }

    /**
     * Save a search query.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'index_name' => 'required|string',
            'query_payload' => 'required|array',
            'is_pinned' => 'nullable|boolean',
        ]);

        $userId = (string) $request->user()->id;
        $name = $request->input('name');
        $indexName = $request->input('index_name');
        $queryPayload = $request->input('query_payload');
        $isPinned = (bool) $request->input('is_pinned', false);

        $query = SearchQuery::fromArray($queryPayload);

        $savedSearch = $this->service->save($userId, $name, $indexName, $query, $isPinned);

        return $this->successResponse($savedSearch, 'Search query saved successfully.', 201);
    }

    /**
     * Delete a saved search.
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $this->service->delete($id, $userId);

        return $this->successResponse(null, 'Saved search deleted successfully.');
    }
}
