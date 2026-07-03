<?php

declare(strict_types=1);

namespace App\Modules\Providers\Application\Queries;

use App\Modules\Providers\Application\DTOs\ProviderFilterData;
use App\Modules\Providers\Domain\Repositories\ProviderReadRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListProvidersQuery
{
    public function __construct(
        protected ProviderReadRepositoryInterface $providerReadRepo
    ) {}

    /**
     * Retrieve filtered list of providers.
     */
    public function execute(ProviderFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        $term = $filters->search ?? '';
        return $this->providerReadRepo->searchProviders($term, $filters->toArray(), $perPage);
    }
}
