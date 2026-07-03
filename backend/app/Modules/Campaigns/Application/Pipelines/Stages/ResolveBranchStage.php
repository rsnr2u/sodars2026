<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Pipelines\Stages;

use App\Modules\Branches\Domain\Entities\Branch;
use Closure;
use Illuminate\Validation\ValidationException;

class ResolveBranchStage
{
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];

        $branch = Branch::find($dto->branchId);
        if (!$branch) {
            throw ValidationException::withMessages([
                'branch_id' => ['Target branch not found.'],
            ]);
        }

        $passable['branch'] = $branch;

        return $next($passable);
    }
}
