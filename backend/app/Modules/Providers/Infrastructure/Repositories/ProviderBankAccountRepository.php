<?php

declare(strict_types=1);

namespace App\Modules\Providers\Infrastructure\Repositories;

use App\Core\Repositories\Eloquent\BaseRepository;
use App\Modules\Providers\Domain\Entities\ProviderBankAccount;
use App\Modules\Providers\Domain\Repositories\ProviderBankAccountRepositoryInterface;

class ProviderBankAccountRepository extends BaseRepository implements ProviderBankAccountRepositoryInterface
{
    public function __construct(ProviderBankAccount $model)
    {
        parent::__construct($model);
    }
}
