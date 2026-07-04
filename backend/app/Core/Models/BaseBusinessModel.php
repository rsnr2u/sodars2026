<?php

declare(strict_types=1);

namespace App\Core\Models;

use App\Platform\Identity\Infrastructure\Traits\BelongsToOrganization;
use App\Platform\Audit\Infrastructure\Traits\Auditable;

abstract class BaseBusinessModel extends BaseModel
{
    use BelongsToOrganization;
    use Auditable;
}
