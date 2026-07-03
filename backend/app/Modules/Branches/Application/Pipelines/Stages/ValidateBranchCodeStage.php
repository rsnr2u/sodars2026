<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\Pipelines\Stages;

use App\Modules\Branches\Application\DTOs\CreateBranchData;
use App\Modules\Branches\Domain\Repositories\BranchRepositoryInterface;
use Closure;
use Illuminate\Validation\ValidationException;

class ValidateBranchCodeStage
{
    public function __construct(
        protected BranchRepositoryInterface $branchRepo
    ) {}

    /**
     * Handle branch code/name uniqueness validation.
     *
     * @param array{dto: CreateBranchData, branch: ?\App\Modules\Branches\Domain\Entities\Branch} $passable
     * @throws ValidationException
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];
        $errors = [];

        if ($this->branchRepo->existsByCode($dto->code)) {
            $errors['code'] = ['Branch code already exists.'];
        }

        if ($this->branchRepo->existsByName($dto->name)) {
            $errors['name'] = ['Branch name already exists.'];
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return $next($passable);
    }
}
