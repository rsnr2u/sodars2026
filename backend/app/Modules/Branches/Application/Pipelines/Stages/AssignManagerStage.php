<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\Pipelines\Stages;

use App\Modules\Branches\Application\DTOs\CreateBranchData;
use App\Modules\Branches\Domain\Entities\BranchUser;
use Closure;

class AssignManagerStage
{
    /**
     * Assign the initial primary manager to the branch if provided.
     *
     * @param array{dto: CreateBranchData, branch: \App\Modules\Branches\Domain\Entities\Branch} $passable
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];
        $branch = $passable['branch'];

        if ($dto->managerUserId) {
            BranchUser::create([
                'branch_id' => $branch->id,
                'user_id' => $dto->managerUserId,
                'is_primary' => true,
                'is_active' => true,
                'joined_at' => now(),
            ]);
        }

        return $next($passable);
    }
}
