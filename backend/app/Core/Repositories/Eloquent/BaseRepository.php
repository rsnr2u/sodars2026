<?php

declare(strict_types=1);

namespace App\Core\Repositories\Eloquent;

use App\Core\Contracts\BaseRepositoryInterface;
use App\Core\Models\BaseModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * The model instance.
     */
    protected BaseModel $model;

    /**
     * BaseRepository constructor.
     */
    public function __construct(BaseModel $model)
    {
        $this->model = $model;
    }

    /**
     * Find a record by its primary ID.
     */
    public function find(string $id): ?BaseModel
    {
        return $this->model->find($id);
    }

    /**
     * Find a record or throw exception.
     */
    public function findOrFail(string $id): BaseModel
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Find a record by its UUID.
     */
    public function findByUuid(string $uuid): ?BaseModel
    {
        return $this->model->where('id', $uuid)->first();
    }

    /**
     * Create a new record.
     */
    public function create(array $attributes): BaseModel
    {
        return DB::transaction(fn () => $this->model->create($attributes));
    }

    /**
     * Update an existing record.
     */
    public function update(string $id, array $attributes): ?BaseModel
    {
        return DB::transaction(function () use ($id, $attributes): ?BaseModel {
            $record = $this->find($id);
            if ($record) {
                $record->update($attributes);

                return $record;
            }

            return null;
        });
    }

    /**
     * Delete a record by ID.
     */
    public function delete(string $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $record = $this->find($id);
            if ($record) {
                return $record->delete();
            }

            return false;
        });
    }

    /**
     * Paginate results.
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * Check if a record exists by criteria.
     */
    public function exists(array $criteria): bool
    {
        return $this->model->where($criteria)->exists();
    }

    /**
     * Perform database bulk inserts.
     */
    public function bulkInsert(array $records): bool
    {
        return DB::transaction(fn () => $this->model->insert($records));
    }

    /**
     * Perform database bulk updates.
     */
    public function bulkUpdate(array $records, string $key = 'id'): int
    {
        return DB::transaction(function () use ($records, $key): int {
            $updated = 0;
            foreach ($records as $record) {
                if (isset($record[$key])) {
                    $keyValue = $record[$key];
                    unset($record[$key]);
                    $this->model->where($key, $keyValue)->update($record);
                    $updated++;
                }
            }

            return $updated;
        });
    }
}
