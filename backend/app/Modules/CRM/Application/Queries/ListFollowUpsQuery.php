<?php

declare(strict_types=1);

namespace App\Modules\CRM\Application\Queries;

use App\Modules\CRM\Domain\Entities\FollowUp;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListFollowUpsQuery
{
    public function execute(int $perPage = 15): LengthAwarePaginator
    {
        return FollowUp::with(['lead', 'opportunity', 'assignee'])
            ->orderBy('due_at', 'asc')
            ->paginate($perPage);
    }
}
