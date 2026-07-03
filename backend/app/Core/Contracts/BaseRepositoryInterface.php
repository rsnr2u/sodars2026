<?php

declare(strict_types=1);

namespace App\Core\Contracts;

use App\Core\Models\BaseModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    /**
     * Find a record by its primary ID.
     */
    public function find(string $id): ?BaseModel;

    /**
     * Find a record by its primary ID or throw exception.
     */
    public function findOrFail(string $id): BaseModel;

    /**
     * Find a record by its UUID.
     */
    public function findByUuid(string $uuid): ?BaseModel;

    /**
     * Create a new record.
     */
    public function create(array $attributes): BaseModel;

    /**
     * Update an existing record.
     */
    public function update(string $id, array $attributes): ?BaseModel;

    /**
     * Delete a record by ID.
     */
    public function delete(string $id): bool;

    /**
     * Paginate results.
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Check if a record exists by criteria.
     */
    public function exists(array $criteria): bool;

    /**
     * Perform database bulk inserts.
     */
    public function bulkInsert(array $records): bool;

    /**
     * Perform database bulk updates.
     */
    public function bulkUpdate(array $records, string $key = 'id'): int;
}
