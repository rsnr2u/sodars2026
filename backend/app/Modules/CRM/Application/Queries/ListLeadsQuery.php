<?php

declare(strict_types=1);

namespace App\Modules\CRM\Application\Queries;

use App\Modules\CRM\Domain\Entities\Lead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListLeadsQuery
{
    public function execute(int $perPage = 15): LengthAwarePaginator
    {
        return Lead::with(['account', 'contact', 'assignee'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
