<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\Pipelines\Stages;

use App\Modules\Branches\Application\DTOs\CreateBranchData;
use App\Modules\Branches\Domain\Repositories\BranchRepositoryInterface;
use Closure;

class PersistBranchStage
{
    public function __construct(
        protected BranchRepositoryInterface $branchRepo
    ) {}

    /**
     * Persist the branch to the database.
     *
     * @param array{dto: CreateBranchData, branch: ?\App\Modules\Branches\Domain\Entities\Branch} $passable
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];

        $branch = $this->branchRepo->create([
            'name' => $dto->name,
            'code' => $dto->code,
            'timezone' => $dto->timezone,
            'currency_code' => $dto->currencyCode,
            'markup_percentage' => $dto->markupPercentage,
            'support_email' => $dto->supportEmail,
            'support_phone' => $dto->supportPhone,
            'status' => 'active',
        ]);

        $passable['branch'] = $branch;

        return $next($passable);
    }
}
