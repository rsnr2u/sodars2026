<?php

declare(strict_types=1);

namespace App\Modules\Providers\Infrastructure\Repositories;

use App\Core\Repositories\Eloquent\BaseRepository;
use App\Modules\Providers\Domain\Entities\ProviderSubscription;
use App\Modules\Providers\Domain\Repositories\ProviderSubscriptionRepositoryInterface;

class ProviderSubscriptionRepository extends BaseRepository implements ProviderSubscriptionRepositoryInterface
{
    public function __construct(ProviderSubscription $model)
    {
        parent::__construct($model);
    }
}
