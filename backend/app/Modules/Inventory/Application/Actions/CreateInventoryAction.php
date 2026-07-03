<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Actions;

use App\Modules\Inventory\Application\DTOs\CreateInventoryData;
use App\Modules\Inventory\Application\Pipelines\CreateInventoryPipeline;
use App\Modules\Inventory\Domain\Entities\Inventory;
use Illuminate\Support\Facades\DB;

class CreateInventoryAction
{
    public function __construct(
        protected CreateInventoryPipeline $pipeline
    ) {}

    /**
     * Create aggregate root structure.
     */
    public function execute(CreateInventoryData $data): Inventory
    {
        return DB::transaction(fn() => $this->pipeline->handle($data));
    }
}
