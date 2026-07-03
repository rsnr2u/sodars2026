<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Pipelines;

use App\Modules\Campaigns\Application\DTOs\CreateCampaignData;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;

class CreateCampaignPipeline
{
    /** @var array<int, string> */
    protected array $stages = [
        Stages\ValidateInputStage::class,
        Stages\ResolveCustomerStage::class,
        Stages\ResolveBranchStage::class,
        Stages\CreateCampaignStage::class,
        Stages\AssignInventoryStage::class,
        Stages\PublishEventsStage::class,
    ];

    public function __construct(
        protected Pipeline $pipeline
    ) {}

    public function execute(CreateCampaignData $dto): Campaign
    {
        return DB::transaction(function () use ($dto) {
            $passable = [
                'dto' => $dto,
                'campaign' => null,
                'customer' => null,
                'branch' => null,
                'campaign_code' => null,
            ];

            $result = $this->pipeline->send($passable)
                ->through($this->stages)
                ->then(function ($passable) {
                    return $passable['campaign'];
                });

            return $result;
        });
    }
}
