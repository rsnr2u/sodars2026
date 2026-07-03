<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\Actions;

use App\Modules\Branches\Application\DTOs\CreateBranchData;
use App\Modules\Branches\Application\Pipelines\CreateBranchPipeline;
use App\Modules\Branches\Domain\Entities\Branch;

class CreateBranchAction
{
    public function __construct(
        protected CreateBranchPipeline $pipeline
    ) {}

    /**
     * Execute the pipeline to create a new branch.
     */
    public function execute(CreateBranchData $data): Branch
    {
        return $this->pipeline->handle($data);
    }
}
