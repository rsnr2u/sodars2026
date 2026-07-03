<?php

declare(strict_types=1);

namespace App\Modules\Finance\Infrastructure\Repositories;

use App\Modules\Finance\Domain\Entities\ProviderSettlement;
use App\Modules\Finance\Domain\Repositories\ProviderSettlementRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ProviderSettlementRepository implements ProviderSettlementRepositoryInterface
{
    public function findById(string $id): ?ProviderSettlement
    {
        return ProviderSettlement::find($id);
    }

    public function findOrFail(string $id): ProviderSettlement
    {
        return ProviderSettlement::findOrFail($id);
    }

    public function create(array $data): ProviderSettlement
    {
        return ProviderSettlement::create($data);
    }

    public function update(string $id, array $data): ProviderSettlement
    {
        $settlement = ProviderSettlement::findOrFail($id);
        $settlement->update($data);
        return $settlement;
    }

    /**
     * @return LengthAwarePaginator<ProviderSettlement>
     */
    public function paginate(?string $providerId = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = ProviderSettlement::query()->with(['provider', 'booking', 'invoice']);

        if ($providerId) {
            $query->where('provider_id', $providerId);
        }

        return $query->latest()->paginate($perPage);
    }
}
