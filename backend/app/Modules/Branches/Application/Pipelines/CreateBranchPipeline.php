<?php

declare(strict_types=1);

namespace App\Modules\Branches\Application\Pipelines;

use App\Modules\Branches\Application\DTOs\CreateBranchData;
use App\Modules\Branches\Domain\Entities\Branch;
use Illuminate\Pipeline\Pipeline;

class CreateBranchPipeline
{
    /**
     * The list of pipeline stages.
     *
     * @var array<int, string>
     */
    protected array $stages = [
        Stages\ValidateInputStage::class,
        Stages\ValidateBranchCodeStage::class,
        Stages\ValidateCoverageStage::class,
        Stages\PersistBranchStage::class,
        Stages\AssignManagerStage::class,
        Stages\PublishEventsStage::class,
    ];

    /**
     * Run the branch creation through the pipeline.
     */
    public function handle(CreateBranchData $data): Branch
    {
        return app(Pipeline::class)
            ->send([
                'dto' => $data,
                'branch' => null,
            ])
            ->through($this->stages)
            ->then(function (array $passable) {
                if ($passable['branch'] instanceof Branch) {
                    return $passable['branch'];
                }
                throw new \RuntimeException('CreateBranchPipeline failed to return a Branch entity.');
            });
    }
}
