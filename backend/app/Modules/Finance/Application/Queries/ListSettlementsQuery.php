<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Queries;

use App\Modules\Finance\Domain\Repositories\ProviderSettlementRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ListSettlementsQuery
{
    public function __construct(protected ProviderSettlementRepositoryInterface $repo) {}

    /**
     * @return LengthAwarePaginator<\App\Modules\Finance\Domain\Entities\ProviderSettlement>
     */
    public function execute(?string $providerId = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repo->paginate($providerId, $perPage);
    }
}
