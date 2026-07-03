<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Repositories;

use App\Modules\Finance\Domain\Entities\ProviderSettlement;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProviderSettlementRepositoryInterface
{
    public function findById(string $id): ?ProviderSettlement;

    public function findOrFail(string $id): ProviderSettlement;

    public function create(array $data): ProviderSettlement;

    public function update(string $id, array $data): ProviderSettlement;

    /**
     * @return LengthAwarePaginator<ProviderSettlement>
     */
    public function paginate(?string $providerId = null, int $perPage = 15): LengthAwarePaginator;
}
