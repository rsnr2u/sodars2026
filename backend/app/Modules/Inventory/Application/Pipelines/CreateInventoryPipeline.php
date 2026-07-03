<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Pipelines;

use App\Modules\Inventory\Application\DTOs\CreateInventoryData;
use App\Modules\Inventory\Domain\Entities\Inventory;
use Illuminate\Pipeline\Pipeline;

class CreateInventoryPipeline
{
    /**
     * Pipeline stages.
     *
     * @var array<int, string>
     */
    protected array $stages = [
        Stages\ValidateInputStage::class,
        Stages\ResolveProviderStage::class,
        Stages\ResolveBranchStage::class,
        Stages\CreateInventoryStage::class,
        Stages\CreateFacesStage::class,
        Stages\CreatePricingStage::class,
        Stages\CreateAvailabilityStage::class,
        Stages\PublishEventsStage::class,
    ];

    /**
     * Run creation steps.
     */
    public function handle(CreateInventoryData $data): Inventory
    {
        return app(Pipeline::class)
            ->send([
                'dto' => $data,
                'inventory' => null,
                'provider' => null,
                'branch_id' => null,
                'city_id' => null,
                'state_id' => null,
                'district_id' => null,
                'country_id' => null,
                'pincode_id' => null,
            ])
            ->through($this->stages)
            ->then(function (array $passable) {
                if ($passable['inventory'] instanceof Inventory) {
                    return $passable['inventory'];
                }
                throw new \RuntimeException('CreateInventoryPipeline failed to return an Inventory entity.');
            });
    }
}
