<?php

declare(strict_types=1);

namespace App\Modules\Providers\Infrastructure\Repositories;

use App\Modules\Providers\Domain\Entities\Provider;
use App\Modules\Providers\Domain\Repositories\ProviderWriteRepositoryInterface;

class ProviderWriteRepository implements ProviderWriteRepositoryInterface
{
    public function __construct(
        protected Provider $model
    ) {}

    /**
     * Create new record.
     */
    public function create(array $attributes): Provider
    {
        return $this->model->create($attributes);
    }

    /**
     * Update existing record.
     */
    public function update(string $id, array $attributes): ?Provider
    {
        $provider = $this->model->find($id);
        if ($provider) {
            $provider->update($attributes);
            return $provider;
        }
        return null;
    }

    /**
     * Soft delete record.
     */
    public function delete(string $id): bool
    {
        $provider = $this->model->find($id);
        if ($provider) {
            return (bool) $provider->delete();
        }
        return false;
    }
}
