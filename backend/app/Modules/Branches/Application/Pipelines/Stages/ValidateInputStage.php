<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\Pipelines\Stages;

use App\Modules\Branches\Application\DTOs\CreateBranchData;
use Closure;
use Illuminate\Validation\ValidationException;

class ValidateInputStage
{
    /**
     * Handle the stage check.
     *
     * @param array{dto: CreateBranchData, branch: ?\App\Modules\Branches\Domain\Entities\Branch} $passable
     * @throws ValidationException
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];

        $errors = [];

        if (empty(trim($dto->name))) {
            $errors['name'] = ['Branch name cannot be empty.'];
        }

        if (empty(trim($dto->code))) {
            $errors['code'] = ['Branch code cannot be empty.'];
        }

        if ($dto->markupPercentage < 0 || $dto->markupPercentage > 20) {
            $errors['markup_percentage'] = ['Markup percentage must be between 0 and 20.'];
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return $next($passable);
    }
}
