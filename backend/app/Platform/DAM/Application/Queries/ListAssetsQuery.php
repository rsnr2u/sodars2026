<?php

declare(strict_types=1);

namespace App\Platform\DAM\Application\Queries;

use App\Platform\DAM\Domain\Entities\Asset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAssetsQuery
{
    /**
     * List and search assets based on directory placement and filters.
     */
    public function execute(array $filters = []): LengthAwarePaginator
    {
        $query = Asset::with(['currentVersion.file', 'versions.file', 'folder', 'tags']);

        if (isset($filters['folder_id'])) {
            $query->where('folder_id', $filters['folder_id']);
        } else {
            // Default to root folder if folder_id filter is not passed and rootOnly filter is set
            if (isset($filters['root_only']) && $filters['root_only']) {
                $query->whereNull('folder_id');
            }
        }

        if (isset($filters['asset_type'])) {
            $query->where('asset_type', $filters['asset_type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 20;

        return $query->paginate($perPage);
    }
}
