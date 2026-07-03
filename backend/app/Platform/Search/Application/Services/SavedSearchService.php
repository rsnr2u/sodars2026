<?php

declare(strict_types=1);

namespace App\Platform\Search\Application\Services;

use App\Platform\Search\Domain\Entities\SavedSearch;
use App\Platform\Search\Domain\ValueObjects\SearchQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SavedSearchService
{
    /**
     * Get all saved searches for a user.
     *
     * @return Collection<int, SavedSearch>
     */
    public function getForUser(string $userId): Collection
    {
        return SavedSearch::where('user_id', $userId)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create a saved search query.
     */
    public function save(string $userId, string $name, string $indexName, SearchQuery $query, bool $isPinned = false): SavedSearch
    {
        return SavedSearch::create([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'name' => $name,
            'index_name' => $indexName,
            'query_payload' => $query->toArray(),
            'is_pinned' => $isPinned,
        ]);
    }

    /**
     * Delete a saved search.
     */
    public function delete(string $id, string $userId): void
    {
        SavedSearch::where('id', $id)
            ->where('user_id', $userId)
            ->delete();
    }
}
