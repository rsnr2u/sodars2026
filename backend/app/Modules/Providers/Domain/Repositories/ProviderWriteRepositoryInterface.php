<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Repositories;

use App\Modules\Providers\Domain\Entities\Provider;

interface ProviderWriteRepositoryInterface
{
    /**
     * Create a new provider record.
     */
    public function create(array $attributes): Provider;

    /**
     * Update an existing provider record.
     */
    public function update(string $id, array $attributes): ?Provider;

    /**
     * Delete a provider record by ID.
     */
    public function delete(string $id): bool;
}
