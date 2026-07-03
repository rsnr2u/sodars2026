<?php

declare(strict_types=1);

namespace App\Modules\Providers\Infrastructure\Repositories;

use App\Core\Repositories\Eloquent\BaseRepository;
use App\Modules\Providers\Domain\Entities\ProviderStaff;
use App\Modules\Providers\Domain\Repositories\ProviderStaffRepositoryInterface;

class ProviderStaffRepository extends BaseRepository implements ProviderStaffRepositoryInterface
{
    public function __construct(ProviderStaff $model)
    {
        parent::__construct($model);
    }
}
