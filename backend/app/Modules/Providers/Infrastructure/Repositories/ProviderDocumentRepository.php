<?php

declare(strict_types=1);

namespace App\Modules\Providers\Infrastructure\Repositories;

use App\Core\Repositories\Eloquent\BaseRepository;
use App\Modules\Providers\Domain\Entities\ProviderDocument;
use App\Modules\Providers\Domain\Repositories\ProviderDocumentRepositoryInterface;

class ProviderDocumentRepository extends BaseRepository implements ProviderDocumentRepositoryInterface
{
    public function __construct(ProviderDocument $model)
    {
        parent::__construct($model);
    }
}
